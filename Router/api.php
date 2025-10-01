<?php
header('Content-Type: application/json');

require_once __DIR__ . "/../controllers/LoginController.php";
require_once __DIR__ . "/../controllers/UserController.php";
require_once __DIR__ . "/../controllers/OrganisationController.php";
require_once __DIR__ . "/../controllers/ContentController.php";

/**
 * --- Helper Functions ---
 */

/**
 * Parse JSON input safely
 */
function getInput() {
    $input = file_get_contents("php://input");
    return json_decode($input, true) ?? [];
}

/**
 * Return 401 Unauthorized
 */
function unauthorized($msg = "Unauthorized") {
    http_response_code(401);
    echo json_encode(["error" => $msg]);
    exit;
}

/**
 * Return 404 Not Found
 */
function notFound($msg = "Endpoint not found") {
    http_response_code(404);
    echo json_encode(["error" => $msg]);
    exit;
}

/**
 * Simple auth check (currently email,password in header)
 * Replace later with token-based auth
 */
function getCurrentUser() {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        unauthorized("Missing Authorization header");
    }

    // Expect: Authorization: email,password
    list($email, $password) = explode(",", $_SERVER['HTTP_AUTHORIZATION']);
    $loginCtrl = new LoginController();
    return $loginCtrl->login($email, $password);
}

/**
 * Parse request path
 */
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$input = getInput();

// Instantiate controllers
$loginCtrl = new LoginController();
$userCtrl = new UserController();
$orgCtrl = new OrganisationController();
$contentCtrl = new ContentController();

/**
 * --- Routing ---
 */
switch (true) {

    // --- Login ---
    case $request === '/api/login' && $method === 'POST':
        $currentUser = $loginCtrl->login($input['email'], $input['password']);
        echo json_encode([
            "id" => $currentUser->getId(),
            "name" => $currentUser->getName(),
            "role_id" => $currentUser->getRoleId()
        ]);
        break;

    // --- Users ---
    case $request === '/api/users' && $method === 'GET':
        $currentUser = getCurrentUser();
        $userCtrl->listUsers($currentUser);
        break;

    case $request === '/api/users' && $method === 'POST':
        $currentUser = getCurrentUser();
        $userCtrl->createUser($currentUser, $input);
        break;

    // --- Organisations ---
    case $request === '/api/organisations' && $method === 'GET':
        $currentUser = getCurrentUser();
        $orgCtrl->listOrganisations($currentUser);
        break;

    case preg_match('/\/api\/organisations\/(\d+)\/approve/', $request, $matches) && $method === 'PUT':
        $currentUser = getCurrentUser();
        $orgId = $matches[1];
        $orgCtrl->approveOrganisation($currentUser, $orgId);
        break;

    // --- Content ---
    case $request === '/api/content' && $method === 'GET':
        $currentUser = getCurrentUser();
        $contentCtrl->listContent($currentUser);
        break;

    case $request === '/api/content' && $method === 'POST':
        $currentUser = getCurrentUser();
        $contentCtrl->addContent($currentUser, $input['title'], $input['link']);
        break;

    // --- Default 404 ---
    default:
        notFound();
        break;
}

