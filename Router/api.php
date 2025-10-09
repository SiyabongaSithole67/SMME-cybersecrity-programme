<?php

header('Content-Type: application/json');

// --- Controllers ---
require_once __DIR__ . "/../controllers/LoginController.php";
require_once __DIR__ . "/../controllers/UserController.php";
require_once __DIR__ . "/../controllers/OrganizationController.php";
require_once __DIR__ . "/../controllers/ContentController.php";
require_once __DIR__ . "/../controllers/AssessmentController.php";

// --- Helpers ---
function getInput() {
    $input = file_get_contents("php://input");
    return json_decode($input, true) ?? [];
}

function unauthorized($msg = "Unauthorized") {
    http_response_code(401);
    echo json_encode(["error" => $msg]);
    exit;
}

function notFound($msg = "Endpoint not found") {
    http_response_code(404);
    echo json_encode(["error" => $msg]);
    exit;
}

function getCurrentUser() {
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        unauthorized("Missing Authorization header");
    }
    list($email, $password) = explode(",", $_SERVER['HTTP_AUTHORIZATION']);
    $loginCtrl = new LoginController();
    return $loginCtrl->login($email, $password);
}

// --- Endpoints ---
$loginEndpoint = '/api/login';
$usersEndpoint = '/api/users';
$organisationsEndpoint = '/api/organisations';
$contentEndpoint = '/api/content';
$assessmentsEndpoint = '/api/assessments';
$submitAssessmentEndpoint = '/api/assessments/submit';
$resultsEndpoint = '/api/assessments/results';

// --- Parse request ---
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$input = getInput();

// --- Controllers ---
$loginCtrl = new LoginController();
$userCtrl = new UserController();
$orgCtrl = new OrganizationController();
$contentCtrl = new ContentController();
$assessmentCtrl = new AssessmentController();

// --- Routing ---
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
    case $request === $usersEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        echo json_encode($userCtrl->listUsers($currentUser));
        break;

    case $request === $usersEndpoint && $method === 'POST':
        $currentUser = getCurrentUser();
        echo json_encode($userCtrl->createUser($currentUser, $input));
        break;

    // --- Organisations ---
    case $request === $organisationsEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        echo json_encode($orgCtrl->listOrganisations($currentUser));
        break;

    case preg_match('/\/api\/organisations\/(\d+)\/approve/', $request, $matches) && $method === 'PUT':
        $currentUser = getCurrentUser();
        $orgId = $matches[1];
        echo json_encode($orgCtrl->approveOrganisation($currentUser, $orgId));
        break;

    // --- Content ---
    case $request === $contentEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        echo json_encode($contentCtrl->listContent($currentUser));
        break;

    case $request === $contentEndpoint && $method === 'POST':
        $currentUser = getCurrentUser();
        echo json_encode($contentCtrl->addContent($currentUser, $input['title'], $input['link']));
        break;

    // --- Assessments ---
    case $request === $assessmentsEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        echo json_encode($assessmentCtrl->listAssessments($currentUser));
        break;

    case $request === $assessmentsEndpoint && $method === 'POST':
        $currentUser = getCurrentUser();
        echo json_encode(["success" => $assessmentCtrl->createAssessment($currentUser, $input)]);
        break;

    case preg_match('/\/api\/assessments\/(\d+)$/', $request, $matches) && $method === 'PUT':
        $currentUser = getCurrentUser();
        $assessmentId = $matches[1];
        echo json_encode(["success" => $assessmentCtrl->updateAssessment($currentUser, $assessmentId, $input)]);
        break;

    case preg_match('/\/api\/assessments\/(\d+)$/', $request, $matches) && $method === 'DELETE':
        $currentUser = getCurrentUser();
        $assessmentId = $matches[1];
        echo json_encode(["success" => $assessmentCtrl->deleteAssessment($currentUser, $assessmentId)]);
        break;

    case $request === $submitAssessmentEndpoint && $method === 'POST':
        $currentUser = getCurrentUser();
        echo json_encode(["success" => $assessmentCtrl->submitResult(
            $input['assessment_id'],
            $currentUser->getId(),
            $input['score']
        )]);
        break;

    case $request === $resultsEndpoint && $method === 'GET':
        $currentUser = getCurrentUser();
        $employeeId = $_GET['employee_id'] ?? null;
        echo json_encode($assessmentCtrl->viewResults($currentUser, $employeeId));
        break;

    // --- Default ---
    default:
        notFound();
        break;
}
