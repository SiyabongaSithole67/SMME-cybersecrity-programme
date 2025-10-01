<?php
require_once __DIR__ . "/../config/Database.php";

class OrganisationController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Approve organisation (System Admin only)
    public function approveOrganisation($currentUser, $orgId) {
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can approve organisations.");
        }
        $stmt = $this->db->prepare("UPDATE organisations SET approved=1 WHERE id=?");
        return $stmt->execute([$orgId]);
    }

    // List organisations
    public function listOrganisations($currentUser) {
        if ($currentUser->getRoleId() == 1) {
            $stmt = $this->db->query("SELECT * FROM organisations");
        } elseif ($currentUser->getRoleId() == 2) {
            $stmt = $this->db->prepare("SELECT * FROM organisations WHERE id=?");
            $stmt->execute([$currentUser->getOrganisationId()]);
        } else {
            die("Access denied!");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

