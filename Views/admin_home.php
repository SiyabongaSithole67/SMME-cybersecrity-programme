<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super-admin') {
    header("Location: /Views/login.php");
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; }
    nav {
      background: #2c3e50;
      color: white;
      padding: 1rem;
    }
    nav a {
      color: white;
      margin-right: 1rem;
      text-decoration: none;
    }
    .content {
      padding: 2rem;
    }
    .card {
      background: #ecf0f1;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <nav>
    <span>Welcome, <?php echo htmlspecialchars($user['name']); ?> (Admin)</span> |
    <a href="/Views/admin_home.php">Dashboard</a>
    <a href="/Views/content_management.php">Content Management</a>
    <a href="/logout.php">Logout</a>
  </nav>

  <div class="content">
    <h1>Admin Dashboard</h1>
    <div class="card">
      <h3>Quick Actions</h3>
      <ul>
        <li><a href="/Views/content_management.php">Manage Learning Content</a></li>
        <li><a href="/Views/user_management.php">Manage Users</a></li>
        <li><a href="/Views/organization_management.php">Manage Organizations</a></li>
      </ul>
    </div>
  </div>
</body>
</html>