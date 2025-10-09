<?php

require_once __DIR__ . "/../Config/databaseUtil.php";
require_once __DIR__ . "/../Models/UserModel.php";
/**
 * Class OrganisationController
 * 
 * Handles organisation-related operations, such as approving organisations
 * and listing organisations based on the role of the current user.
 * 
 * Role access:
 * 1 = SystemAdmin → full access
 * 2 = OrgAdmin → restricted access to their own organisation
 * 3 = Employee → no access
 */
class OrganisationController {
    
    /**
     * @var PDO $db The PDO database connection
     */
    private $db;

    /**
     * OrganisationController constructor.
     * Initializes a database connection.
     */
    public function __construct() {
     $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Approve an organisation (System Admin only)
     *
     * @param UserModel $currentUser The user attempting to approve
     * @param int $orgId The ID of the organisation to approve
     * @return bool True if the operation succeeded, false otherwise
     */
    public function approveOrganisation($currentUser, $orgId) {
        // Only SystemAdmin can approve organisations
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can approve organisations.");
        }

        // Prepare SQL to update 'approved' column to 1
        $stmt = $this->db->prepare("UPDATE organisations SET approved=1 WHERE id=?");

        // Execute the SQL statement with the organisation ID
        return $stmt->execute([$orgId]);
    }

    /**
     * List organisations accessible to the current user
     *
     * @param UserModel $currentUser The user requesting the list
     * @return array List of organisations as associative arrays
     */
    public function listOrganisations($currentUser) {
        // SystemAdmin → can see all organisations
        if ($currentUser->getRoleId() == 1) {
            $stmt = $this->db->query("SELECT * FROM organisations");

        // OrgAdmin → can see only their own organisation
        } elseif ($currentUser->getRoleId() == 2) {
            $stmt = $this->db->prepare("SELECT * FROM organisations WHERE id=?");
            $stmt->execute([$currentUser->getOrganisationId()]);

        // Employees or other roles → no access
        } else {
            die("Access denied!");
        }
        
        // Fetch all rows as associative arrays and return
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addOrganisation($currentUser, $name) {
        // Only SystemAdmin (Role ID 1) is authorized to add via this central management tool
        if ($currentUser->getRoleId() != 1) {
            return false; // Unauthorized
        }

        // 1. Check for duplicate name
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM organisations WHERE name = ?");
        $stmt->execute([$name]);
        if ($stmt->fetchColumn() > 0) {
            return 'duplicate'; // Custom string for duplicate error
        }

        // 2. Insert new organisation (default approved=0 for review)
        $stmt = $this->db->prepare("INSERT INTO organisations (name, approved) VALUES (?, 0)");
        
        return $stmt->execute([$name]);
    }

    public function deleteOrganisation($currentUser, $orgId) {
        // Only SystemAdmin can delete organisations
        if ($currentUser->getRoleId() != 1) {
            return false; // Unauthorized
        }

        // Prepare SQL to delete the organisation
        $stmt = $this->db->prepare("DELETE FROM organisations WHERE id=?");
        
        return $stmt->execute([$orgId]);
    }
}
    if (isset($_GET['action'])) {
    session_start();
    
    // Check for user session and load the necessary context (e.g., UserModel)
    if (!isset($_SESSION['user'])) {
        header('Location: /Views/manage_orgs.php?error=' . urlencode('Session expired or unauthorized.'));
        exit();
    }
    
    // Create a mock current user object for controller logic access checks
    $u = $_SESSION['user'];
    $currentUser = new UserModel();
    $currentUser->setId($u['id'] ?? null);
    $currentUser->setRoleId($u['role_id'] ?? null);
    
    // Only SystemAdmin (Role ID 1) should be able to process these actions
    if ($currentUser->getRoleId() != 1) {
        header('Location: /Views/manage_orgs.php?error=' . urlencode('Unauthorized access to controller action.'));
        exit();
    }
    
    $orgController = new OrganisationController();
    $action = $_GET['action'];
    $redirectUrl = '/Views/manage_orgs.php';
    $success = false;
    $msg = '';

    try {
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            if (empty($name)) {
                $msg = 'missing_fields';
            } else {
                $result = $orgController->addOrganisation($currentUser, $name);
                if ($result === 'duplicate') {
                    $msg = 'duplicate_name';
                } elseif ($result) {
                    $msg = 'added';
                    $success = true;
                } else {
                    $msg = 'Error adding organisation.';
                }
            }

        } elseif ($action === 'approve' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $orgId = (int)($_POST['id'] ?? 0);
            if ($orgId > 0) {
                if ($orgController->approveOrganisation($currentUser, $orgId)) {
                    $msg = 'approved';
                    $success = true;
                } else {
                    $msg = 'Error approving organisation.';
                }
            } else {
                $msg = 'missing_id';
            }

        } elseif ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $orgId = (int)($_POST['id'] ?? 0);
            if ($orgId > 0) {
                // Before deleting the organisation, you should ideally check for and 
                // handle related users (e.g., set their organisation_id to null or delete them)
                if ($orgController->deleteOrganisation($currentUser, $orgId)) {
                    $msg = 'deleted';
                    $success = true;
                } else {
                    $msg = 'Error deleting organisation.';
                }
            } else {
                $msg = 'missing_id';
            }
        } else {
            $msg = 'invalid_method';
        }

    } catch (Exception $e) {
        // Log error and set generic message
        error_log("OrganizationController Error: " . $e->getMessage());
        $msg = 'A fatal error occurred: ' . $e->getMessage();
    }

    // Redirect with status message
    if ($success) {
        header('Location: ' . $redirectUrl . '?msg=' . urlencode($msg));
    } else {
        // If $msg is a predefined short code, pass it as 'msg' so it can be translated in the view
        if (in_array($msg, ['missing_fields', 'duplicate_name', 'missing_id', 'invalid_method'])) {
            header('Location: ' . $redirectUrl . '?msg=' . urlencode($msg));
        } else {
            // Otherwise, pass it as an 'error' for general failure messages
            header('Location: ' . $redirectUrl . '?error=' . urlencode($msg));
        }
    }
    exit();
}




