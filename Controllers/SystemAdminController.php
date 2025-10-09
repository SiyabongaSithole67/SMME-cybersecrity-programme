<?php
require_once __DIR__ . "/../Models/UserModel.php";
require_once __DIR__ . "/../Config/databaseUtil.php";

class SystemAdminController {
    private $db;

    public function __construct() {
        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Get all users with their role and organization info
     */
    public function getAllUsers() {
        $stmt = $this->db->query("
            SELECT u.*, r.name as role_name, o.name as org_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            LEFT JOIN organisations o ON u.organisation_id = o.id
            ORDER BY u.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all pending organizations (approved = 0)
     */
    public function getPendingOrganisations() {
        $stmt = $this->db->query("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM users WHERE organisation_id = o.id AND role_id = 2) as admin_count
            FROM organisations o
            WHERE o.approved = 0
            ORDER BY o.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all approved organizations
     */
    public function getApprovedOrganisations() {
        $stmt = $this->db->query("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM users WHERE organisation_id = o.id) as user_count
            FROM organisations o
            WHERE o.approved = 1
            ORDER BY o.name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Approve an organization and its admin users
     */
    public function approveOrganisation($orgId) {
        try {
            $this->db->beginTransaction();

            // Approve the organisation
            $stmt = $this->db->prepare("UPDATE organisations SET approved = 1 WHERE id = :id");
            $stmt->bindParam(':id', $orgId, PDO::PARAM_INT);
            $stmt->execute();

            // Approve all users in this organisation
            $stmt = $this->db->prepare("UPDATE users SET approved = 1 WHERE organisation_id = :org_id");
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Reject (delete) an organization and its users
     */
    public function rejectOrganisation($orgId) {
        try {
            $this->db->beginTransaction();

            // Delete users in this organisation
            $stmt = $this->db->prepare("DELETE FROM users WHERE organisation_id = :org_id");
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            $stmt->execute();

            // Delete the organisation
            $stmt = $this->db->prepare("DELETE FROM organisations WHERE id = :id");
            $stmt->bindParam(':id', $orgId, PDO::PARAM_INT);
            $stmt->execute();

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Delete a user
     */
    public function deleteUser($userId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update user role
     */
    public function updateUserRole($userId, $newRoleId) {
        try {
            $stmt = $this->db->prepare("UPDATE users SET role_id = :role_id WHERE id = :id");
            $stmt->bindParam(':role_id', $newRoleId, PDO::PARAM_INT);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all content
     */
    public function getAllContent() {
        $stmt = $this->db->query("SELECT * FROM contents ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add new content
     */
    public function addContent($title, $link) {
        try {
            $stmt = $this->db->prepare("INSERT INTO contents (title, link) VALUES (:title, :link)");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':link', $link);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update content
     */
    public function updateContent($id, $title, $link) {
        try {
            $stmt = $this->db->prepare("UPDATE contents SET title = :title, link = :link WHERE id = :id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':link', $link);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete content
     */
    public function deleteContent($contentId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM contents WHERE id = :id");
            $stmt->bindParam(':id', $contentId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $stats = [];

        // Total users
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Pending organizations
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM organisations WHERE approved = 0");
        $stats['pending_orgs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total content
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM contents");
        $stats['total_content'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total organizations
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM organisations WHERE approved = 1");
        $stats['approved_orgs'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }
}