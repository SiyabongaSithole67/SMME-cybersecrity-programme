<?php
/**
 * Class Assessment
 * 
 * Represents a cybersecurity assessment/test in the system.
 * Can be summative or formative and linked to specific content.
 */
class Assessment {
    
    // --- Properties ---
    private $id;           // Unique ID for the assessment
    private $title;        // Title of the assessment
    private $description;  // Description or instructions
    private $contentId;    // Optional: ID of content associated with this assessment
    private $type;         // Type: 'formative' or 'summative'

    // --- Getters ---
    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getContentId() {
        return $this->contentId;
    }

    public function getType() {
        return $this->type;
    }

    // --- Setters ---
    public function setId($id) {
        $this->id = $id;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function setContentId($contentId) {
        $this->contentId = $contentId;
    }

    public function setType($type) {
        // Ensure type is either 'formative' or 'summative'
        if (!in_array($type, ['formative', 'summative'])) {
            throw new InvalidArgumentException("Type must be 'formative' or 'summative'");
        }
        $this->type = $type;
    }
}

