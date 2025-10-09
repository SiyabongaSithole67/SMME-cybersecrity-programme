<?php

require_once __DIR__ . "/../Config/databaseUtil.php";

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

    /**
     * Delete a content item by id
     * Only SystemAdmin can delete content
     *
     * @param UserModel $currentUser The user attempting the delete
     * @param int $id The id of the content to delete
     * @return bool True on success, false on failure
     */
    public function deleteContent($currentUser, $id) {
        if ($currentUser->getRoleId() != 1) {
            throw new Exception("Access denied: Only SystemAdmin can delete content");
        }

        $stmt = $this->db->prepare("DELETE FROM contents WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Handle HTTP add/delete actions when this file is executed directly.
     * Keeps routing logic close to the controller but preserves
     * addContent/deleteContent as pure methods.
     */
    public static function handleRequest()
    {
        if (php_sapi_name() === 'cli' || empty($_GET['action'])) {
            return;
        }

        session_start();
        require_once __DIR__ . '/../Models/UserModel.php';

        if (empty($_SESSION['user'])) {
            header('Location: /Views/login.php');
            exit();
        }

    $u = $_SESSION['user'];
    // Rebuild a UserModel using setters (UserModel has no constructor)
    $currentUser = new UserModel();
    $currentUser->setId($u['id'] ?? null);
    $currentUser->setName($u['name'] ?? null);
    $currentUser->setEmail($u['email'] ?? null);
    $currentUser->setRoleId((int)($u['role_id'] ?? 0));
    $currentUser->setOrganisationId($u['organisation_id'] ?? null);

        $controller = new self();
        $action = $_GET['action'];
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

        $redirect = function ($query = '') {
            $loc = '/Views/content_management.php' . ($query ? "?{$query}" : '');
            header("Location: {$loc}");
            exit();
        };

        try {
            if ($action === 'add') {
                if ($method !== 'POST') {
                    $redirect('msg=invalid_method');
                }
                $title = trim($_POST['title'] ?? '');
                $link = trim($_POST['link'] ?? '');
                if ($title === '' || $link === '') {
                    $redirect('msg=missing_fields');
                }
                $controller->addContent($currentUser, $title, $link);
                $redirect('msg=added');
            }

            if ($action === 'delete') {
                if ($method !== 'POST') {
                    $redirect('msg=invalid_method');
                }
                $id = $_POST['id'] ?? null;
                if ($id === null) {
                    $redirect('msg=missing_id');
                }
                $controller->deleteContent($currentUser, (int)$id);
                $redirect('msg=deleted');
            }
        } catch (Exception $e) {
            $msg = urlencode($e->getMessage());
            $redirect("error={$msg}");
        }
    }
}

// If the file is requested directly via the web and an action is provided,
// delegate handling to the class method so routing stays near the controller.
if (php_sapi_name() !== 'cli' && !empty($_GET['action'])) {
    ContentController::handleRequest();
}



