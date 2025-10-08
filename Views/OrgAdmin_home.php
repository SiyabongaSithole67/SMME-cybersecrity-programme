<?php
session_start();

// Only allow OrgAdmin users
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /Views/login.php");
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Organisation Admin Dashboard</title>
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
  </style>
</head>
<body>
  <nav>
    <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span> |
    <a href="/Views/OrgAdmin_home.php">Home</a>
  <a href="/Views/manage_users.php">Manage Employees</a>
    <a href="/Views/view_reports.php">Reports</a>
    <a href="/Views/logout.php">Logout</a>
  </nav>

  <main style="padding: 2rem;">
    <h1>Organisation Admin Dashboard</h1>
    <p>Here you can manage employees, view reports, and configure organisation settings.</p>
  </main>
</body>
</html>


