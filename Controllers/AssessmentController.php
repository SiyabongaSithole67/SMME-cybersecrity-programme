<?php
require_once __DIR__ . "/../Models/Assessment.php";
require_once __DIR__ . "/../Config/databaseUtil.php";

/**
 * Class AssessmentController
 * 
 * Handles creation, listing, submission, and viewing of assessments.
 * Supports role-based access:
 * - SystemAdmin (role_id = 1) → full access
 * - OrgAdmin (role_id = 2) → access to assessments/results for their organisation
 * - Employee (role_id = 3) → access to own assessments/results only
 */
class AssessmentController{
    private $db; // Database connection

    /**
     * Constructor: Establishes SQLite database connection
     */
    public function __construct() {
      $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Create a new assessment
     * Only SystemAdmin can create assessments
     *
     * @param UserModel $currentUser Authenticated user attempting to create assessment
     * @param array $data Associative array containing:
     *                    - 'title' => string
     *                    - 'description' => string
     *                    - 'content_id' => int|null
     *                    - 'type' => 'formative'|'summative'
     * @return bool True if the assessment is successfully created
     */
    public function createAssessment($currentUser, $data) {
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can create assessments.");
        }

        // Prepare SQL INSERT statement
        $stmt = $this->db->prepare(
            "INSERT INTO assessments (title, description, content_id, type) VALUES (?, ?, ?, ?)"
        );

        // Execute SQL with provided data
        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['content_id'] ?? null,
            $data['type']
        ]);
    }


       public function updateAssessment($currentUser, $id, $data) {
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can update assessments.");
        }

        $stmt = $this->db->prepare("
            UPDATE assessments
            SET title = ?, description = ?, content_id = ?, type = ?
            WHERE id = ?
        ");

        return $stmt->execute([
            $data['title'],
            $data['description'],
            $data['content_id'] ?? null,
            $data['type'],
            $id
        ]);
    }

  /** ✅ Delete an assessment (SystemAdmin only) */
     public function deleteAssessment($currentUser, $id) {
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can delete assessments.");
        }

        $stmt = $this->db->prepare("DELETE FROM assessments WHERE id = ?");
        return $stmt->execute([$id]);
    }
      
    

    /**
     * List all assessments accessible to the current user
     * 
     * @param UserModel $currentUser Authenticated user
     * @return array List of assessments (associative arrays)
     */
    public function listAssessments($currentUser) {
        if ($currentUser->getRoleId() == 1) {


            // SystemAdmin: can see all assessments
            $stmt = $this->db->query("SELECT * FROM assessments");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);


        } else {
            // OrgAdmin or Employee: see assessments linked to their organisation or general assessments
            $stmt = $this->db->prepare(

                "SELECT a.* FROM assessments a
                 JOIN contents c ON a.content_id = c.id
                 WHERE c.organisation_id = ? OR c.organisation_id IS NULL"

            );

            $stmt->execute([$currentUser->getOrganisationId()]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    /**
     * Record an assessment result for an employee
     * 
     * @param int $assessmentId ID of the assessment
     * @param int $userId ID of the employee taking the assessment
     * @param float $score Score achieved by the employee
     * @return bool True if result saved successfully
     */
    public function submitResult($assessmentId, $userId, $score) {
        $stmt = $this->db->prepare(
            "INSERT INTO results (assessment_id, user_id, score, completed_at) VALUES (?, ?, ?, datetime('now'))"
        );
        return $stmt->execute([$assessmentId, $userId, $score]);
    }

    /**
     * View assessment results
     * Role-based access:
     * - SystemAdmin: all results
     * - OrgAdmin: results of employees in their organisation
     * - Employee: only own results
     *
     * @param UserModel $currentUser Authenticated user
     * @param int|null $employeeId Optional specific employee ID (OrgAdmin can filter)
     * @return array List of results (associative arrays)
     */
    public function viewResults($currentUser, $employeeId = null) {
        
        if ($currentUser->getRoleId() == 1) {
            // SystemAdmin sees all results
            $stmt = $this->db->query("SELECT * FROM results");
        } elseif ($currentUser->getRoleId() == 2) {
            // OrgAdmin sees results of their organisation's employees
            if ($employeeId) {
                $stmt = $this->db->prepare(
                    "SELECT r.* FROM results r
                     JOIN users u ON r.user_id = u.id
                     WHERE u.organisation_id = ? AND u.id = ?"
                );
                $stmt->execute([$currentUser->getOrganisationId(), $employeeId]);
            } else {
                $stmt = $this->db->prepare(
                    "SELECT r.* FROM results r
                     JOIN users u ON r.user_id = u.id
                     WHERE u.organisation_id = ?"
                );
                $stmt->execute([$currentUser->getOrganisationId()]);
            }
        } else {
            // Employee sees only their own results
            $stmt = $this->db->prepare(
                "SELECT * FROM results WHERE user_id = ?"
            );
            $stmt->execute([$currentUser->getId()]);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

