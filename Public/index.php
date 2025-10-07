<?php
session_start();

// Include necessary files
require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../Models/ContentModel.php';
require_once __DIR__ . '/../Controllers/ContentController.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// Get current user from session
$currentUser = $_SESSION['user']; // Assuming you store UserModel object in session

// Initialize controller and get content
$contentController = new ContentController();
$contentList = $contentController->listContent($currentUser);

// Include the view
include __DIR__ . '/Views/Content.php';
?>