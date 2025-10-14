<?php

/**
 * UserController
 * 
 * Handles user management with role-based access control
 * 
 * Roles:
 * - SystemAdmin (role_id = 1): Full access to all operations
 * - OrgAdmin (role_id = 2): Can manage users within their organization
 * - Employee (role_id = 3): Limited access to own account
 */

// Include dependencies
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Config/databaseUtil.php';

class UserController {

    private $db;

    /**
     * Initialize database connection
     */
    public function __construct() {
        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Create a new user in the system
     * 
     * @param UserModel $currentUser The currently logged-in user performing the action
     * @param array $userData Array containing: name, email, password, role_id, organisation_id, approved (optional)
     * @return bool Returns true if insertion was successful, false otherwise
     * 
     * Access control:
     * - SystemAdmin: Can create any user
     * - OrgAdmin: Can only create employees in their own organisation
     * - Employee: Cannot create users
     */
    public function createUser($currentUser, array $userData) {
        // Validate current user permissions
        if ($currentUser->getRoleId() == 1) {
            // SystemAdmin has full access
        } elseif ($currentUser->getRoleId() == 2) {
            // OrgAdmin can only create employees in their organisation
            if (!isset($userData['role_id']) || (int)$userData['role_id'] !== 3) {
                throw new Exception("Access denied: OrgAdmin can only create employees");
            }
            // Force organisation_id to match creator's organisation
            $userData['organisation_id'] = $currentUser->getOrganisationId();
        } else {
            throw new Exception("Access denied: Insufficient permissions to create users");
        }

        // Prepare and execute insertion
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role_id, organisation_id, approved) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );

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
     * @return array Array of users as associative arrays
     * 
     * Access control:
     * - SystemAdmin: Sees all users
     * - OrgAdmin: Sees users only in their organisation
     * - Employee: Sees only their own account
     */
    public function listUsers($currentUser) {
        if ($currentUser->getRoleId() == 1) {
            // SystemAdmin: All users
            $stmt = $this->db->query("SELECT * FROM users");
        } elseif ($currentUser->getRoleId() == 2) {
            // OrgAdmin: Users in same organisation
            $stmt = $this->db->prepare("SELECT * FROM users WHERE organisation_id = ?");
            $stmt->execute([$currentUser->getOrganisationId()]);
        } else {
            // Employee: Only own record
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$currentUser->getId()]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single user by ID with access control
     * 
     * @param UserModel $currentUser The currently logged-in user
     * @param int $id User ID to retrieve
     * @return array|null User data or null if not found/accessible
     */
    public function getUserById($currentUser, $id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([(int)$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return null;
        }

        // Access control checks
        if ($currentUser->getRoleId() == 1) {
            return $user; // SystemAdmin can view any user
        }
        
        if ($currentUser->getRoleId() == 2) {
            // OrgAdmin can only view users in their organisation
            return ($user['organisation_id'] == $currentUser->getOrganisationId()) ? $user : null;
        }
        
        // Employee can only view their own account
        return ($user['id'] == $currentUser->getId()) ? $user : null;
    }

    /**
     * Approve a user account
     * 
     * @param UserModel $currentUser The currently logged-in user
     * @param int $id User ID to approve
     * @return bool Success status
     */
    public function approveUser($currentUser, $id) {
        $user = $this->getUserById($currentUser, $id);
        if (!$user) {
            throw new Exception('Access denied or user not found');
        }

        $stmt = $this->db->prepare("UPDATE users SET approved = 1 WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Reset a user's password
     * 
     * @param UserModel $currentUser The currently logged-in user
     * @param int $id User ID
     * @param string $newPassword New password (will be hashed)
     * @return bool Success status
     */
    public function resetPassword($currentUser, $id, $newPassword) {
        $user = $this->getUserById($currentUser, $id);
        if (!$user) {
            throw new Exception('Access denied or user not found');
        }

        // Employees can only reset their own password
        if ($currentUser->getRoleId() == 3 && $currentUser->getId() != (int)$id) {
            throw new Exception('Access denied: Can only reset own password');
        }

        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE id = ?");
        return $stmt->execute([$hashed, (int)$id]);
    }

    /**
     * Update user information
     * 
     * @param UserModel $currentUser The currently logged-in user
     * @param int $id User ID to update
     * @param array $data Fields to update: name, email, role_id, organisation_id, approved
     * @return bool Success status
     */
    public function updateUser($currentUser, $id, array $data) {
        $target = $this->getUserById($currentUser, $id);
        if (!$target) {
            throw new Exception('Access denied or user not found');
        }

        // OrgAdmin can only update users in their organisation
        if ($currentUser->getRoleId() == 2 && 
            $target['organisation_id'] != $currentUser->getOrganisationId()) {
            throw new Exception('Access denied: Can only update users in your organisation');
        }

        // Build dynamic UPDATE query with allowed fields
        $fields = [];
        $params = [];

        if (isset($data['name'])) {
            $fields[] = 'name = ?';
            $params[] = $data['name'];
        }

        if (isset($data['email'])) {
            $fields[] = 'email = ?';
            $params[] = $data['email'];
        }

        // Role changes only allowed for SystemAdmin
        if (isset($data['role_id']) && $currentUser->getRoleId() == 1) {
            $fields[] = 'role_id = ?';
            $params[] = (int)$data['role_id'];
        }

        // Organisation changes only allowed for SystemAdmin
        if (isset($data['organisation_id']) && $currentUser->getRoleId() == 1) {
            $fields[] = 'organisation_id = ?';
            $params[] = (int)$data['organisation_id'];
        }

        // Approval changes only allowed for SystemAdmin
        if (isset($data['approved']) && $currentUser->getRoleId() == 1) {
            $fields[] = 'approved = ?';
            $params[] = (int)$data['approved'];
        }

        if (empty($fields)) {
            throw new Exception('No updatable fields provided');
        }

        $params[] = (int)$id;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute($params);
    }

    /**
     * Delete a user
     * 
     * @param UserModel $currentUser The currently logged-in user
     * @param int $id User ID to delete
     * @return bool Success status
     */
    public function deleteUser($currentUser, $id) {
        $target = $this->getUserById($currentUser, $id);
        if (!$target) {
            throw new Exception('Access denied or user not found');
        }

        // Prevent self-deletion
        if ($currentUser->getId() == (int)$id) {
            throw new Exception('Cannot delete your own account');
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    /**
     * Handle HTTP requests when file is accessed directly
     * Processes add, approve, reset, update, and delete actions
     */
    public static function handleRequest() {
        // Only handle web requests with an action parameter
        if (php_sapi_name() === 'cli' || empty($_GET['action'])) {
            return;
        }

        session_start();
        require_once __DIR__ . '/../Models/UserModel.php';

        // Check authentication
        if (empty($_SESSION['user'])) {
            header('Location: /Views/login.php');
            exit();
        }

        // Build current user object from session
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

        // Helper function for redirects
        $redirect = function($query = '') {
            $loc = '/Views/manage_users.php' . ($query ? "?{$query}" : '');
            header("Location: {$loc}");
            exit();
        };

        try {
            // Handle different actions
            switch ($action) {
                case 'add':
                    if ($method !== 'POST') {
                        $redirect('msg=invalid_method');
                    }

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

                    $userData = [
                        'name' => $name,
                        'email' => $email,
                        'password' => password_hash($password, PASSWORD_DEFAULT),
                        'role_id' => $role_id,
                        'organisation_id' => $organisation_id,
                        'approved' => 1
                    ];

                    $ok = $controller->createUser($currentUser, $userData);
                    $redirect($ok ? 'msg=added' : 'msg=duplicate_email');
                    break;

                case 'approve':
                    if ($method !== 'POST') {
                        $redirect('msg=invalid_method');
                    }

                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    if (!$id) {
                        $redirect('msg=missing_id');
                    }

                    $controller->approveUser($currentUser, $id);
                    $redirect('msg=approved');
                    break;

                case 'reset':
                    if ($method !== 'POST') {
                        $redirect('msg=invalid_method');
                    }

                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    $newpw = $_POST['new_password'] ?? '';

                    if (!$id) {
                        $redirect('msg=missing_id');
                    }
                    if ($newpw === '' || strlen($newpw) < 6) {
                        $redirect('msg=bad_password');
                    }

                    $controller->resetPassword($currentUser, $id, $newpw);
                    $redirect('msg=password_reset');
                    break;

                case 'update':
                    if ($method !== 'POST') {
                        $redirect('msg=invalid_method');
                    }

                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    if (!$id) {
                        $redirect('msg=missing_id');
                    }

                    $data = [];
                    if (isset($_POST['name'])) $data['name'] = trim($_POST['name']);
                    if (isset($_POST['email'])) $data['email'] = trim($_POST['email']);
                    if (isset($_POST['role_id'])) $data['role_id'] = (int)$_POST['role_id'];
                    if (isset($_POST['organisation_id'])) $data['organisation_id'] = (int)$_POST['organisation_id'];
                    if (isset($_POST['approved'])) $data['approved'] = (int)$_POST['approved'];

                    $controller->updateUser($currentUser, $id, $data);
                    $redirect('msg=updated');
                    break;

                case 'delete':
                    if ($method !== 'POST') {
                        $redirect('msg=invalid_method');
                    }

                    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
                    if (!$id) {
                        $redirect('msg=missing_id');
                    }

                    $controller->deleteUser($currentUser, $id);
                    $redirect('msg=deleted');
                    break;

                default:
                    $redirect('msg=invalid_action');
            }
        } catch (Exception $e) {
            $msg = urlencode($e->getMessage());
            $redirect("error={$msg}");
        }
    }
}

// Handle direct web requests with action parameter
if (php_sapi_name() !== 'cli' && !empty($_GET['action'])) {
    UserController::handleRequest();
}