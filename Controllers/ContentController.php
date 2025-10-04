<?php

require_once __DIR__ . "/../config/DatabaseUtil.php";

/**
 * Class ContentController
 * 
 * Handles operations related to content management, such as adding new content
 * and listing content. Includes access control based on user roles.
 * 
 * Role access:
 * 1 = SystemAdmin → full control (can add content)
 * 2 = OrgAdmin → can view content
 * 3 = Employee → can view content
 */
class ContentController {
    
    /**
     * @var PDO $db The PDO database connection
     */
    private $db;

    /**
     * Constructor
     * Initializes the database connection
     */
    public function __construct() {
       $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Add new content to the database
     * Only SystemAdmin can add content
     *
     * @param UserModel $currentUser The user attempting to add content
     * @param string $title The title of the content
     * @param string $link The link or body of the content
     * @return bool True if insertion succeeded, false otherwise
     */
    public function addContent($currentUser, $title, $link) {
        // Access control: Only SystemAdmin
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can add content");
        }

        // Prepare SQL statement to insert content
        $stmt = $this->db->prepare("INSERT INTO contents (title, link) VALUES (?, ?)");

        // Execute the SQL with the given title and link
        return $stmt->execute([$title, $link]);
    }

    /**
     * List all content from the database
     * All users can view content
     *
     * @param UserModel $currentUser The user requesting the content list
     * @return array Array of content rows as associative arrays
     */
    public function listContent($currentUser) {
        // Query all content
        $stmt = $this->db->query("SELECT * FROM contents");

        // Fetch results as associative arrays
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


