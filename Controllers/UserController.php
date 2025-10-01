<?php

require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../config/Database.php";

class UserController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Create user
    public function createUser($currentUser, $userData) {
        if ($currentUser->getRoleId() == 1) {
            // SystemAdmin → full power
        } elseif ($currentUser->getRoleId() == 2) {
            // OrgAdmin → can only create employees in their own org
            if ($userData['role_id'] != 3 || $userData['organisation_id'] != $currentUser->getOrganisationId()) {
                die("Access denied: OrgAdmin can only create employees in their organisation");
            }
        } else {
            die("Access denied!");
        }

        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role_id, organisation_id) VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $userData['name'], $userData['email'], $userData['password'],
            $userData['role_id'], $userData['organisation_id']
        ]);
    }

    // List users
    public function listUsers($currentUser) {
        if ($currentUser->getRoleId() == 1) {
            $stmt = $this->db->query("SELECT * FROM users");
        } elseif ($currentUser->getRoleId() == 2) {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE organisation_id=?");
            $stmt->execute([$currentUser->getOrganisationId()]);
        } else {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE id=?");
            $stmt->execute([$currentUser->getId()]);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}



