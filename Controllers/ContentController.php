<?php
require_once __DIR__ . "/../config/Database.php";

class ContentController {
    private $db;

    public function __construct() {
        $this->db = (new Database())->connect();
    }

    // Add new content (SystemAdmin only)
    public function addContent($currentUser, $title, $link) {
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can add content");
        }
        $stmt = $this->db->prepare("INSERT INTO contents (title, link) VALUES (?, ?)");
        return $stmt->execute([$title, $link]);
    }

    // View all content (any user can view)
    public function listContent($currentUser) {
        $stmt = $this->db->query("SELECT * FROM contents");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

