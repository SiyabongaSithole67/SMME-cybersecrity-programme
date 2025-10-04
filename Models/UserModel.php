<?php
require_once __DIR__ . '/../DatabaseConnection/database.php';
// User.php

class UserModel {
   
    private $id;
    private $name;
    private $email;
    private $password;
    private $role_id;
    private $organisation_id;

    // --- Getters ---
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRoleId() { return $this->role_id; }
    public function getOrganisationId() { return $this->organisation_id; }

    // --- Setters ---
    public function setId($id) { $this->id = $id; }
    public function setName($name) { $this->name = $name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setRoleId($role_id) { $this->role_id = $role_id; }
    public function setOrganisationId($organisation_id) { $this->organisation_id = $organisation_id; }

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