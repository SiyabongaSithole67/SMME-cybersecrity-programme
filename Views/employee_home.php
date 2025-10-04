<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee') {
    header("Location: /Views/login.php");
    exit();
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Dashboard</title>
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
    <a href="/Views/employee_home.php">Home</a>
    <a href="/Views/profile.php">Profile</a>
    <a href="/logout.php">Logout</a>
  </nav>

  <main style="padding: 2rem;">
    <h1>Employee Dashboard</h1>
    <p>Here you can view your assessments, progress, and content.</p>
  </main>
</body>
</html>