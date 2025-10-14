<?php
require_once __DIR__ . "/../Models/UserModel.php";
require_once __DIR__ . "/../Config/databaseUtil.php";

class OrgAdminController {
    private $db;

    public function __construct() {
        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Get all employees in the org admin's organization
     */
    public function getOrganizationEmployees($orgId) {
        $stmt = $this->db->prepare("
            SELECT u.*, r.name as role_name
            FROM users u
            LEFT JOIN roles r ON u.role_id = r.id
            WHERE u.organisation_id = :org_id
            ORDER BY u.created_at DESC
        ");
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add new employee to organization
     */
    public function addEmployee($name, $email, $password, $orgId, $roleId = 3) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO users (name, email, password, role_id, organisation_id, approved)
                VALUES (:name, :email, :password, :role_id, :org_id, 1)
            ");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Update employee details
     */
    public function updateEmployee($userId, $name, $email, $orgId) {
        try {
            // Verify user belongs to this org
            $stmt = $this->db->prepare("
                UPDATE users 
                SET name = :name, email = :email
                WHERE id = :id AND organisation_id = :org_id
            ");
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Reset employee password
     */
    public function resetPassword($userId, $newPassword, $orgId) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                UPDATE users 
                SET password = :password
                WHERE id = :id AND organisation_id = :org_id
            ");
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Delete employee
     */
    public function deleteEmployee($userId, $orgId) {
        try {
            // Verify user belongs to this org before deleting
            $stmt = $this->db->prepare("
                DELETE FROM users 
                WHERE id = :id AND organisation_id = :org_id AND role_id = 3
            ");
            $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get all training content
     */
    public function getAllContent() {
        $stmt = $this->db->query("SELECT * FROM contents ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get assessment results for organization employees
     */
    public function getOrganizationResults($orgId, $assessmentType = null) {
        $sql = "
            SELECT 
                r.id, r.score, r.completed_at,
                u.name as employee_name, u.email as employee_email,
                a.title as assessment_title, a.type as assessment_type,
                c.title as content_title
            FROM results r
            INNER JOIN users u ON r.user_id = u.id
            INNER JOIN assessments a ON r.assessment_id = a.id
            LEFT JOIN contents c ON a.content_id = c.id
            WHERE u.organisation_id = :org_id
        ";
        
        if ($assessmentType) {
            $sql .= " AND a.type = :type";
        }
        
        $sql .= " ORDER BY r.completed_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        if ($assessmentType) {
            $stmt->bindParam(':type', $assessmentType);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get organization dashboard statistics
     */
    public function getOrganizationStats($orgId) {
        $stats = [];

        // Total employees
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM users WHERE organisation_id = :org_id AND role_id = 3");
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['total_employees'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total assessments completed
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as count 
            FROM results r
            INNER JOIN users u ON r.user_id = u.id
            WHERE u.organisation_id = :org_id
        ");
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['assessments_completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Average score
        $stmt = $this->db->prepare("
            SELECT AVG(r.score) as avg_score 
            FROM results r
            INNER JOIN users u ON r.user_id = u.id
            WHERE u.organisation_id = :org_id
        ");
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['average_score'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg_score'] ?? 0, 1);

        // Active employees (completed at least one assessment)
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT u.id) as count
            FROM users u
            INNER JOIN results r ON r.user_id = u.id
            WHERE u.organisation_id = :org_id AND u.role_id = 3
        ");
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['active_employees'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }

    /**
     * Get employee performance analytics
     */
    public function getEmployeePerformance($orgId) {
        $stmt = $this->db->prepare("
            SELECT 
                u.id, u.name, u.email,
                COUNT(r.id) as assessments_taken,
                AVG(r.score) as avg_score,
                MAX(r.completed_at) as last_activity
            FROM users u
            LEFT JOIN results r ON r.user_id = u.id
            WHERE u.organisation_id = :org_id AND u.role_id = 3
            GROUP BY u.id, u.name, u.email
            ORDER BY avg_score DESC
        ");
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get assessment type breakdown
     */
    public function getAssessmentTypeBreakdown($orgId) {
        $stmt = $this->db->prepare("
            SELECT 
                a.type,
                COUNT(r.id) as count,
                AVG(r.score) as avg_score
            FROM results r
            INNER JOIN assessments a ON r.assessment_id = a.id
            INNER JOIN users u ON r.user_id = u.id
            WHERE u.organisation_id = :org_id
            GROUP BY a.type
        ");
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get organization name
     */
    public function getOrganizationName($orgId) {
        $stmt = $this->db->prepare("SELECT name FROM organisations WHERE id = :id");
        $stmt->bindParam(':id', $orgId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['name'] : 'Unknown Organization';
    }
}