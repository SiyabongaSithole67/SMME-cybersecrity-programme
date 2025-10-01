<?php
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/Database.php";

class LoginController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Login function: returns User object if successful
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email=?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            die("Login failed: user not found");
        }

        // Simple password check (plaintext for now; you can hash later)
        if ($row['password'] !== $password) {
            die("Login failed: incorrect password");
        }

        // Populate User object
        $user = new User();
        $user->setId($row['id']);
        $user->setName($row['name']);
        $user->setEmail($row['email']);
        $user->setPassword($row['password']);
        $user->setRoleId($row['role_id']);
        $user->setOrganisationId($row['organisation_id']);

        return $user;
    }
}

