<?php

require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/DatabaseUtil.php";

/**
 * Class LoginController
 * 
 * Handles user authentication.
 * Provides a login method that verifies email and password and returns a User object.
 */
class LoginController {
    
    /**
     * @var PDO $db The PDO database connection
     */
    private $db;

    /**
     * Constructor
     * Initializes the database connection
     */
    public function __construct() {
        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Login a user by email and password
     *
     * @param string $email The email address entered by the user
     * @param string $password The password entered by the user
     * @return User Returns a populated User object if login succeeds
     * @throws Exception if login fails
     */
    public function login($email, $password) {

        // Prepare SQL to find user by email
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);

        // Fetch user row as associative array
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no user found, deny access
        if (!$row) {
            die("Login failed: user not found");
        }

        
        // Simple password check (plaintext for now)
        // NOTE: In production, use password_hash() and password_verify()
        if ($row['password'] !== $password) {
            die("Login failed: incorrect password");
        }

        // Populate User object with database data
        $user = new User();
        $user->setId($row['id']);
        $user->setName($row['name']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setRoleId($row['role_id']);
        $user->setOrganisationId($row['organisation_id']);

        // Return the authenticated User object
        return $user;
    }

    public function verifyUser($email, $password) {
        $db = new DatabaseUtil();  
        $conn = $db->connect();

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
}


