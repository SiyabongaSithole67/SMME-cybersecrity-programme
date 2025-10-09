<?php
session_start();

// Only allow SystemAdmin users
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'systemAdmin') {
    header("Location: /Views/index.php");
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>System Admin Dashboard</title>
  <style>
    nav {
      background: #007BFF;
      color: white;
      padding: 1rem;
    }
    nav a {
      color: white;
      margin-right: 1rem;
      text-decoration: none;
    }
    nav a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <nav>
    <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span> |
    <a href="/Views/SystemAdmin_home.php">Home</a>
    
    <a href="/Views/content_management.php">Manage Content</a>
  <a href="/Views/manage_users.php">Manage Users</a>
    <a href="/Views/manage_orgs.php">Manage Organisations</a>
    <a href="/Views/logout.php">Logout</a>
  </nav>

  <main style="padding: 2rem;">
    <h1>System Admin Dashboard</h1>
    <p>Here you can manage organisations, users and content.</p>
  </main>
</body>
</html>

