<?php

require_once __DIR__ . "/../Config/databaseUtil.php";

/**
 * Class OrganisationController
 * 
 * Handles organisation-related operations, such as approving organisations
 * and listing organisations based on the role of the current user.
 * 
 * Role access:
 * 1 = SystemAdmin → full access
 * 2 = OrgAdmin → restricted access to their own organisation
 * 3 = Employee → no access
 */
class OrganisationController {
    
    /**
     * @var PDO $db The PDO database connection
     */
    private $db;

    /**
     * OrganisationController constructor.
     * Initializes a database connection.
     */
    public function __construct() {
     $this->db = (new DatabaseUtil())->connect();
    }

    /**
     * Approve an organisation (System Admin only)
     *
     * @param UserModel $currentUser The user attempting to approve
     * @param int $orgId The ID of the organisation to approve
     * @return bool True if the operation succeeded, false otherwise
     */
    public function approveOrganisation($currentUser, $orgId) {
        // Only SystemAdmin can approve organisations
        if ($currentUser->getRoleId() != 1) {
            die("Access denied: Only SystemAdmin can approve organisations.");
        }

        // Prepare SQL to update 'approved' column to 1
        $stmt = $this->db->prepare("UPDATE organisations SET approved=1 WHERE id=?");

        // Execute the SQL statement with the organisation ID
        return $stmt->execute([$orgId]);
    }

    /**
     * List organisations accessible to the current user
     *
     * @param UserModel $currentUser The user requesting the list
     * @return array List of organisations as associative arrays
     */
    public function listOrganisations($currentUser) {
        // SystemAdmin → can see all organisations
        if ($currentUser->getRoleId() == 1) {
            $stmt = $this->db->query("SELECT * FROM organisations");

        // OrgAdmin → can see only their own organisation
        } elseif ($currentUser->getRoleId() == 2) {
            $stmt = $this->db->prepare("SELECT * FROM organisations WHERE id=?");
            $stmt->execute([$currentUser->getOrganisationId()]);

        // Employees or other roles → no access
        } else {
            die("Access denied!");
        }
        
        // Fetch all rows as associative arrays and return
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


