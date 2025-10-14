<?php
require_once __DIR__ . "/../Config/databaseUtil.php";

class EmployeeController {
    private $db;

    public function __construct() {
        $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Get all available assessments
     */
    public function getAllAssessments() {
        $stmt = $this->db->query("
            SELECT a.*, c.title as content_title 
            FROM assessments a
            LEFT JOIN contents c ON a.content_id = c.id
            ORDER BY a.created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get assessment by ID
     */
    public function getAssessmentById($assessmentId) {
        $stmt = $this->db->prepare("
            SELECT a.*, c.title as content_title, c.link as content_link
            FROM assessments a
            LEFT JOIN contents c ON a.content_id = c.id
            WHERE a.id = :id
        ");
        $stmt->bindParam(':id', $assessmentId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Submit assessment answer
     */
    public function submitAssessment($assessmentId, $userId, $answerFile, $score = 0) {
        try {
            // Check if already submitted
            $stmt = $this->db->prepare("
                SELECT id FROM results 
                WHERE assessment_id = :assessment_id AND user_id = :user_id
            ");
            $stmt->bindParam(':assessment_id', $assessmentId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'You have already submitted this assessment.'];
            }

            // Insert result
            $stmt = $this->db->prepare("
                INSERT INTO results (assessment_id, user_id, score, answer_file, status)
                VALUES (:assessment_id, :user_id, :score, :answer_file, 'pending')
            ");
            $stmt->bindParam(':assessment_id', $assessmentId, PDO::PARAM_INT);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindParam(':score', $score);
            $stmt->bindParam(':answer_file', $answerFile);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Assessment submitted successfully! Awaiting grading.'];
            } else {
                return ['success' => false, 'message' => 'Failed to submit assessment.'];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get employee's results
     */
    public function getEmployeeResults($userId) {
        $stmt = $this->db->prepare("
            SELECT 
                r.id, r.score, r.completed_at, r.status,
                a.title as assessment_title, a.type as assessment_type,
                c.title as content_title
            FROM results r
            INNER JOIN assessments a ON r.assessment_id = a.id
            LEFT JOIN contents c ON a.content_id = c.id
            WHERE r.user_id = :user_id
            ORDER BY r.completed_at DESC
        ");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get employee's statistics
     */
    public function getEmployeeStats($userId) {
        $stats = [];

        // Total assessments completed
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM results WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['completed'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Average score
        $stmt = $this->db->prepare("SELECT AVG(score) as avg FROM results WHERE user_id = :user_id");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['average_score'] = round($stmt->fetch(PDO::FETCH_ASSOC)['avg'] ?? 0, 1);

        // Pending results
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM results WHERE user_id = :user_id AND status = 'pending'");
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $stats['pending'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        // Total available assessments
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM assessments");
        $stats['total_available'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];

        return $stats;
    }

    /**
     * Check if employee has submitted an assessment
     */
    public function hasSubmitted($assessmentId, $userId) {
        $stmt = $this->db->prepare("
            SELECT id FROM results 
            WHERE assessment_id = :assessment_id AND user_id = :user_id
        ");
        $stmt->bindParam(':assessment_id', $assessmentId, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    /**
     * Handle file upload
     */
    public function handleFileUpload($file) {
        $uploadDir = __DIR__ . '/../uploads/assessments/';
        
        // Create directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Check file size (max 10MB)
        if ($file['size'] > 10 * 1024 * 1024) {
            return ['success' => false, 'message' => 'File too large. Maximum size is 10MB.'];
        }

        // Allowed file types
        $allowedTypes = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($extension), $allowedTypes)) {
            return ['success' => false, 'message' => 'Invalid file type. Allowed: PDF, DOC, DOCX, TXT, JPG, PNG.'];
        }

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['success' => false, 'message' => 'Failed to upload file.'];
        }
    }
}