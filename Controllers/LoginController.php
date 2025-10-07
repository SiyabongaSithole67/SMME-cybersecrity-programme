<?php
require_once __DIR__ . "/../models/UserModel.php";
require_once __DIR__ . "/../Config/DatabaseUtil.php";

class LoginController {
    private $db;

    public function __construct() {
        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Authenticate user and redirect to role-specific home page
     */
public function login($email, $password) {
    // Trim user input to remove accidental spaces
    $email = trim($email);
    $password = trim($password);

    // Fetch user from database
    $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die("Login failed: user not found");
    }

    // Compare passwords (plain text for now)
    if (trim($row['password']) !== $password) {
        die("Login failed: incorrect username or password");
    }

    // Populate User object
    $user = new UserModel(); //instance of the
    $user->setId($row['id']);
    $user->setName($row['name']);
    $user->setEmail($row['email']);
    $user->setRoleId($row['role_id']);
    $user->setOrganisationId($row['organisation_id']);

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Store user info in session
    $_SESSION['user'] = [
        'id' => $user->getId(),
        'name' => $user->getName(),
        'role' => $this->roleName($user->getRoleId()) // 'employee', 'systemAdmin', 'admin'
    ];

    // Redirect based on role
    switch ($_SESSION['user']['role']) {
        case 'systemAdmin':
            header("Location: /Views/systemAdmin_home.php");
            break;
        case 'admin':
            header("Location: /Views/OrgAdmin_home.php");
            break;
        case 'employee':
            header("Location: /Views/employee_home.php");
            break;
        default:
            die("Unknown role. Cannot redirect.");
    }

    exit();
}


    /**
     * Convert role_id to readable role name
     */
    private function roleName($roleId) {
        switch ($roleId) {
            case 1: return 'systemAdmin';
            case 2: return 'admin';
            case 3: return 'employee';
            default: return 'unknown';
        }
    }
}



