<?php

// Using a procedural approach in creating routes, rather then making it a class implementing OOP style
// Set the response content type to JSON for all API responses
header('Content-Type: application/json');

// Import the controller classes that handle the business logic for each module
require_once __DIR__ . "/../controllers/LoginController.php";
require_once __DIR__ . "/../controllers/UserController.php";
require_once __DIR__ . "/../controllers/OrganisationController.php";
require_once __DIR__ . "/../controllers/ContentController.php";

/**
 * --- Helper Functions ---
 */

/**
 * getInput()
 * ----------------------
 * Reads the raw JSON input from the HTTP request body
 * and converts it into a PHP associative array.
 * Returns an empty array if no input or invalid JSON.
 */
function getInput() {
    $input = file_get_contents("php://input"); // read raw input
    return json_decode($input, true) ?? [];     // decode JSON, fallback to empty array
}

/**
 * unauthorized()
 * ----------------------
 * Sends a 401 HTTP status and returns an error message in JSON format.
 * Terminates the script using exit().
 */
function unauthorized($msg = "Unauthorized") {
    http_response_code(401);                  // set status code 401
    echo json_encode(["error" => $msg]);      // return JSON error message
    exit;                                     // stop execution
}

/**
 * notFound()
 * ----------------------
 * Sends a 404 HTTP status and returns an error message in JSON format.
 * Used when the requested endpoint does not exist.
 */
function notFound($msg = "Endpoint not found") {
    http_response_code(404);                  // set status code 404
    echo json_encode(["error" => $msg]);      // return JSON error message
    exit;                                     // stop execution
}

/**
 * getCurrentUser()
 * ----------------------
 * Simple authentication check based on the Authorization HTTP header.
 * Currently expects: "Authorization: email,password"
 * Calls LoginController->login() to verify credentials.
 * Returns a User object on success, or terminates with 401 if failed.
 */

function getCurrentUser() {
    
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {  // check if header exists
        unauthorized("Missing Authorization header"); // terminate if missing
    }

    // Split the header by comma to extract email and password
    list($email, $password) = explode(",", $_SERVER['HTTP_AUTHORIZATION']);

    $loginCtrl = new LoginController();
    return $loginCtrl->login($email, $password);  // return User object
}


// --- Endpoint declarations ---
$loginEndpoint          = '/api/login';
$usersEndpoint          = '/api/users';
$organisationsEndpoint  = '/api/organisations';
$approveOrgEndpoint     = '/api/organisations/{id}/approve';
$contentEndpoint        = '/api/content';


/**
 * Parse the request URI and HTTP method
 */
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); // extract path from URL
$method = $_SERVER['REQUEST_METHOD'];                         // GET, POST, PUT, DELETE
$input = getInput();                                          // get JSON body as array

// Instantiate controllers (these handle the actual business logic)
$loginCtrl = new LoginController();
$userCtrl = new UserController();
$orgCtrl = new OrganisationController();
$contentCtrl = new ContentController();


/**
 * --- Routing ---
 * Simple switch-based router that handles all endpoints.
 * Each case matches a URL path and HTTP method.
 */
switch (true) {

    // --- Login ---
    case $request === $loginEndpoint && $method === 'POST':
        $currentUser = $loginCtrl->login($input['email'], $input['password']);
        echo json_encode([
            "id" => $currentUser->getId(),
            "name" => $currentUser->getName(),
            "role_id" => $currentUser->getRoleId()
        ]);
        break;

    // --- Users ---
    //getting a list of users
    case $request === $usersEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        $userCtrl->listUsers($currentUser);
        break;

        //create a user 
    case $request === $usersEndpoint && $method === 'POST':
        $currentUser = getCurrentUser();
        $userCtrl->createUser($currentUser, $input);
        break;

    // --- Organisations ---
    case $request === $organisationsEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        $orgCtrl->listOrganisations($currentUser);
        break;

    case preg_match('/\/api\/organisations\/(\d+)\/approve/', $request, $matches) && $method === 'PUT':
        $currentUser = getCurrentUser();
        $orgId = $matches[1];
        $orgCtrl->approveOrganisation($currentUser, $orgId);
        break;

    // --- Content ---
    case $request === $contentEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        $contentCtrl->listContent($currentUser);
        break;

    case $request === $contentEndpoint && $method === 'POST':
        $currentUser = getCurrentUser();
        $contentCtrl->addContent($currentUser, $input['title'], $input['link']);
        break;

    // --- Default ---
    default:
        notFound();
        break;
}



