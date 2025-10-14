<?php

// Include the User model and Database connection class
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Config/databaseUtil.php';

class RegistrationController {

    private $db;

    public function __construct() {
        $this->db = (new DatabaseUtil())->connect();
    }

    private function checkEmailExists(string $email): bool {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return (bool)$stmt->fetch();
    }

    /**
     * Finds an organisation by name or creates a new one if it doesn't exist.
     * @param string $orgName The name of the organisation.
     * @return int The ID of the existing or newly created organisation.
     */
    private function getOrCreateOrganisation(string $orgName): array {
        // 1. Check if organisation exists
        $stmt = $this->db->prepare("SELECT id, approved FROM organisations WHERE name = ?");
        $stmt->execute([$orgName]);
        $organisation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($organisation) {
            // Organisation exists: inherit its 'approved' status for the new user.
            return [
                (int)$organisation['id'], 
                (int)$organisation['approved']
            ];
        }

        // 2. Organisation does not exist, create new one
        $stmt = $this->db->prepare("INSERT INTO organisations (name, approved) VALUES (?, 0)");
        $stmt->execute([$orgName]);
        $newOrgId = (int)$this->db->lastInsertId();

        // New organisation means the new user is also pending (status 0).
        return [
            $newOrgId, 
            0 
        ];
    }

    /**
     * Registers a new user with their organisation.
     * New users are set as role_id=3 (Employee) and approved=0 (Pending approval).
     *
     * @param string $email
     * @param string $password
     * @param string $orgName
     * @return bool True on successful registration, false otherwise.
     */
    public function register(string $name, string $email, string $password, string $orgName): bool {
        // Basic validation
        if (empty($email) || empty($password) || empty($orgName) || empty($name)){
            throw new Exception("All fields are required.");
        }

        if ($this->checkEmailExists($email)) {
            throw new Exception("The email address is already registered.");
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        list($organisationId, $orgApprovedStatus) = $this->getOrCreateOrganisation($orgName);

        // Prepare SQL statement to insert a new user
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role_id, organisation_id, approved) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        // Get or create the organisation ID
        // Name is set to email for initial registration
        
        return $stmt->execute([
            $name, 
            $email,
            $hashedPassword,
            3, // Default role for new sign-ups is Employee
            $organisationId,
            0 // default is not approved
        ]);
        
    }
}

// Handle POST request when file is executed directly
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // We assume the front-end sends 'action=register' on POST
    require_once __DIR__ . '/../Models/UserModel.php';

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $orgName = trim($_POST['organisation'] ?? '');
    
    $message = '';
    
    try {
        $controller = new RegistrationController();
        if ($controller->register($name, $email, $password, $orgName)) {
            // Redirect to a success page or login with a success message
            header('Location: /Views/login.php?registration=success');
            exit();
        } else {
             $message = "Registration failed due to an unknown error.";
        }
    } catch (Exception $e) {
        $message = $e->getMessage();
    }
    
    // If we reach here, registration failed, pass message to the register view
    // Store message in session or redirect back to register.php with a query parameter
    header('Location: /Views/register.php?error=' . urlencode($message) . '&email=' . urlencode($email) . '&org=' . urlencode($orgName));
    exit();
}