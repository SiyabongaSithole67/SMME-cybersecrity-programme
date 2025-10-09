<?php

// Include the User model and Database connection class
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Config/databaseUtil.php';

class UserController {

    // Private property to hold the PDO database connection
    private $db;

    // Constructor runs automatically when a new UserController object is created
    public function __construct() {
        // Create a new Database object and connect to the database
        // Store the PDO connection in $this->db for use in all methods

        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Create a new user in the system
     * 
     * @param UserModel $currentUser The currently logged-in user performing the action
     * @param array $userData Array containing new user data: name, email, password, role_id, organisation_id
     * 
     * @return bool Returns true if insertion was successful, false otherwise
     * 
     * Access control:
     * - SystemAdmin (role_id = 1) → can create any user
     * - OrgAdmin (role_id = 2) → can only create employees (role_id = 3) in their own organisation
     * - Employees (role_id = 3) → cannot create users
     */

    public function createUser($currentUser, array $userData) { //create a user

        // Check the role of the current user
        // Only SystemAdmin or OrgAdmin may create users. OrgAdmin can only create employees in their organisation.
        if ($currentUser->getRoleId() == 1) {
            // SystemAdmin → full power, allowed to create any user
        } elseif ($currentUser->getRoleId() == 2) {
            // OrgAdmin → can only create employees in their own organisation
            if (!isset($userData['role_id']) || (int)$userData['role_id'] !== 3) {
                die("Access denied: OrgAdmin can only create employees in their organisation");
            }
            // ensure organisation_id matches creator's organisation
            $userData['organisation_id'] = $currentUser->getOrganisationId();
        } else {
            // Any other role (employees) cannot create users
            die("Access denied!");
        }

        // Prepare SQL statement to insert a new user into the 'users' table
        // Using prepared statements prevents SQL injection attacks
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role_id, organisation_id, approved) VALUES (?, ?, ?, ?, ?, ?)"
        );

        // Execute the SQL statement with the actual user data
        // The array values correspond to the placeholders (?) in the prepared statement
        return $stmt->execute([
            $userData['name'],
            $userData['email'],
            $userData['password'],
            $userData['role_id'],
            $userData['organisation_id'],
            $userData['approved'] ?? 1
        ]);
    }

    /**
     * List users visible to the current user
     * 
     * @param UserModel $currentUser The currently logged-in user
     * 
     * @return array Returns an array of users as associative arrays
     * 
     * Access control:
     * - SystemAdmin → sees all users
     * - OrgAdmin → sees users only in their organisation
     * - Employee → sees only their own account
     */
    public function listUsers($currentUser) { //get or list all users

        // SystemAdmin → retrieve all users
        if ($currentUser->getRoleId() == 1) {
            $stmt = $this->db->query("SELECT * FROM users");

        // OrgAdmin → retrieve users in the same organisation
        } elseif ($currentUser->getRoleId() == 2) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE organisation_id=?");
            $stmt->execute([$currentUser->getOrganisationId()]);

        // Employee → retrieve only their own record
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$currentUser->getId()]);
        }

        // Fetch all results as an array of associative arrays and return
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single user by id with basic access control
     */
    public function getUserById($currentUser, $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([(int)$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) return null;

        // Access control: SystemAdmin can view any, OrgAdmin only same org, employee only self
        if ($currentUser->getRoleId() == 1) return $user;
        if ($currentUser->getRoleId() == 2) {
            return ($user['organisation_id'] == $currentUser->getOrganisationId()) ? $user : null;
        }
        return ($user['id'] == $currentUser->getId()) ? $user : null;
    }

    /**
     * Approve a user account (set approved = 1)
     */
    public function approveUser($currentUser, $id)
    {
        $user = $this->getUserById($currentUser, $id);
        if (!$user) throw new Exception('Access denied or user not found');

        // OrgAdmin cannot approve users outside their org; getUserById already checks that
        $stmt = $this->db->prepare("UPDATE users SET approved = 1 WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Reset a user's password. Caller provides raw new password.
     */
    public function resetPassword($currentUser, $id, $newPassword)
    {
        $user = $this->getUserById($currentUser, $id);
        if (!$user) throw new Exception('Access denied or user not found');

        // Employees can reset only their own password
        if ($currentUser->getRoleId() == 3 && $currentUser->getId() != (int)$id) {
            throw new Exception('Access denied');
        }
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed, (int)$id]);
    }

    /**
     * Update a user's basic fields (name, email, role_id, organisation_id, approved)
     */
    public function updateUser($currentUser, $id, array $data)
    {
        $target = $this->getUserById($currentUser, $id);
        if (!$target) throw new Exception('Access denied or user not found');

        // OrgAdmin can only update users in their org
        if ($currentUser->getRoleId() == 2 && $target['organisation_id'] != $currentUser->getOrganisationId()) {
            throw new Exception('Access denied');
        }

        // Build allowed fields
        $fields = [];
        $params = [];
        if (isset($data['name'])) { $fields[] = 'name = ?'; $params[] = $data['name']; }
        if (isset($data['email'])) { $fields[] = 'email = ?'; $params[] = $data['email']; }
        // role change allowed only for SystemAdmin
        if (isset($data['role_id']) && $currentUser->getRoleId() == 1) { $fields[] = 'role_id = ?'; $params[] = (int)$data['role_id']; }
        // organisation change allowed only for SystemAdmin
        if (isset($data['organisation_id']) && $currentUser->getRoleId() == 1) { $fields[] = 'organisation_id = ?'; $params[] = (int)$data['organisation_id']; }
        if (isset($data['approved']) && $currentUser->getRoleId() == 1) { $fields[] = 'approved = ?'; $params[] = (int)$data['approved']; }

        if (empty($fields)) throw new Exception('No updatable fields provided');

        $params[] = (int)$id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete a user by id
     */
    public function deleteUser($currentUser, $id)
    {
        $target = $this->getUserById($currentUser, $id);
        if (!$target) throw new Exception('Access denied or user not found');

        // Prevent deleting self accidentally
        if ($currentUser->getId() == (int)$id) throw new Exception('Cannot delete your own account');

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Handle HTTP add/approve/reset actions when this file is executed directly.
     */
    public static function handleRequest()
    {
        if (php_sapi_name() === 'cli' || empty($_GET['action'])) return;

        session_start();
        require_once __DIR__ . '/../Models/UserModel.php';

        if (empty($_SESSION['user'])) {
            header('Location: /Views/login.php');
            exit();
        }

        $u = $_SESSION['user'];
        $currentUser = new UserModel();
        $currentUser->setId($u['id'] ?? null);
        $currentUser->setName($u['name'] ?? null);
        $currentUser->setEmail($u['email'] ?? null);
        $currentUser->setRoleId((int)($u['role_id'] ?? 0));
        $currentUser->setOrganisationId($u['organisation_id'] ?? null);

        $controller = new self();
        $action = $_GET['action'];
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $redirect = function ($query = '') {
            $loc = '/Views/manage_users.php' . ($query ? "?{$query}" : '');
            header("Location: {$loc}");
            exit();
        };

        try {
            if ($action === 'add') {
                if ($method !== 'POST') $redirect('msg=invalid_method');
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                // Determine role and organisation based on creator's role
                if ($currentUser->getRoleId() == 1) {
                    // SystemAdmin may set role and organisation
                    $role_id = isset($_POST['role_id']) ? (int)$_POST['role_id'] : 3;
                    $organisation_id = isset($_POST['organisation_id']) ? (int)$_POST['organisation_id'] : null;
                } else {
                    // OrgAdmin can only create employees in their own organisation
                    $role_id = 3;
                    $organisation_id = $currentUser->getOrganisationId();
                }

                if ($name === '' || $email === '' || $password === '') {
                    $redirect('msg=missing_fields');
                }
                
                $hashed = password_hash($password, PASSWORD_DEFAULT);

                $userData = [
                    'name' => $name,
                    'email' => $email,
                    // store raw password for now per request
                    'password' => $hashed,
                    'role_id' => $role_id,
                    'organisation_id' => $organisation_id,
                    'approved' => 1
                ];

                $ok = $controller->createUser($currentUser, $userData);
                if ($ok) $redirect('msg=added');
                $redirect('msg=duplicate_email');
            }

            if ($action === 'approve') {
                if ($method !== 'POST') $redirect('msg=invalid_method');
                $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                if (!$id) $redirect('msg=missing_id');
                $controller->approveUser($currentUser, $id);
                $redirect('msg=approved');
            }

            if ($action === 'reset') {
                if ($method !== 'POST') $redirect('msg=invalid_method');
                $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                $newpw = $_POST['new_password'] ?? '';
                if (!$id) $redirect('msg=missing_id');
                if ($newpw === '' || strlen($newpw) < 6) $redirect('msg=bad_password');
                $controller->resetPassword($currentUser, $id, $newpw);
                $redirect('msg=password_reset');
            }
            if ($action === 'update') {
                if ($method !== 'POST') $redirect('msg=invalid_method');
                $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                if (!$id) $redirect('msg=missing_id');
                $data = [];
                if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
                if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
                if (isset($_POST['role_id'])) $data['role_id'] = (int)$_POST['role_id'];
                if (isset($_POST['organisation_id'])) $data['organisation_id'] = (int)$_POST['organisation_id'];
                if (isset($_POST['approved'])) $data['approved'] = (int)$_POST['approved'];
                $controller->updateUser($currentUser, $id, $data);
                $redirect('msg=updated');
            }

            if ($action === 'delete') {
                if ($method !== 'POST') $redirect('msg=invalid_method');
                $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                if (!$id) $redirect('msg=missing_id');
                $controller->deleteUser($currentUser, $id);
                $redirect('msg=deleted');
            }
        } catch (Exception $e) {
            $msg = urlencode($e->getMessage());
            $redirect("error={$msg}");
        }
    }


    // method to select one user

/**
 * 
 * C-Create
 * R-Read (viewing details)
 * U-Update (Updating data)
 * D-Delete 
 */





}

// If the file is requested directly via the web and an action is provided,
// delegate handling to the class method so routing stays near the controller.
if (php_sapi_name() !== 'cli' && !empty($_GET['action'])) {
    UserController::handleRequest();
}




