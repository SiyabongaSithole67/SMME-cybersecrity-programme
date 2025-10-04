<?php
class Result {
    private $id;
    private $assessmentId;
    private $userId;
    private $score;
    private $completedAt;

    // --- Getters ---
    public function getId() { return $this->id; }
    public function getAssessmentId() { return $this->assessmentId; }
    public function getUserId() { return $this->userId; }
    public function getScore() { return $this->score; }
    public function getCompletedAt() { return $this->completedAt; }

    // --- Setters ---
    public function setId($id) { $this->id = $id; }
    public function setAssessmentId($assessmentId) { $this->assessmentId = $assessmentId; }
    public function setUserId($userId) { $this->userId = $userId; }
    public function setScore($score) { $this->score = $score; }
    public function setCompletedAt($completedAt) { $this->completedAt = $completedAt; }
}

