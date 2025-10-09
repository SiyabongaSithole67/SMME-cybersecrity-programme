<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee') {
    header("Location: /Views/index.php");
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
    <a href="/Views/Assessment.php">Assessments</a>
    <a href="/Views/content_overview.php">Training Content</a>
    <a href="/Views/logout.php">Logout</a>
  </nav>

  <main style="padding: 2rem;">
    <h1>Employee Dashboard</h1>
    <p>Here you can view your assessments, progress, and training content.</p>

    <section style="margin-top:20px;">
      <h2>Available Training</h2>
      <?php
      require_once __DIR__ . '/../Config/databaseUtil.php';
      $db = (new DatabaseUtil())->connect();
      $stmt = $db->query('SELECT id, title FROM contents ORDER BY created_at DESC');
      $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
      if (empty($rows)) {
          echo '<p>No content available yet.</p>';
      } else {
          echo '<ul>';
          foreach ($rows as $r) {
              echo '<li>' . htmlspecialchars($r['title']) . ' - <a href="/Views/Content.php?id=' . (int)$r['id'] . '">Open</a></li>';
          }
          echo '</ul>';
      }
      ?>
    </section>
  </main>
</body>
</html>
