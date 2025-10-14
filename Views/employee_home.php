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
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-color: #f4f6f8;
      color: #333;
    }

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

    main {
      padding: 2rem;
    }

    h1, h2 {
      color: #007BFF;
    }

    .training-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }

    .training-item {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .training-item h3 {
      margin: 0 0 10px;
      font-size: 18px;
    }

    .training-item a {
      align-self: flex-start;
      text-decoration: none;
      background-color: #007BFF;
      color: white;
      padding: 8px 12px;
      border-radius: 5px;
      font-size: 14px;
      transition: background 0.2s ease;
    }

    .training-item a:hover {
      background-color: #0056b3;
    }

    .no-content {
      background: #fff3cd;
      color: #856404;
      border: 1px solid #ffeeba;
      padding: 10px;
      border-radius: 5px;
      display: inline-block;
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <nav>
    <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span> |
    <a href="/Views/employee_home.php">Home</a>
    <a href="/Views/content_overview.php">Training Content</a>
    <a href="/Views/logout.php">Logout</a>
  </nav>

  <main>
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
          echo '<p class="no-content">No training content available yet.</p>';
      } else {
          echo '<div class="training-list">';
          foreach ($rows as $r) {
              echo '<div class="training-item">';
              echo '<h3>' . htmlspecialchars($r['title']) . '</h3>';
              echo '<a href="/Views/Content.php?id=' . (int)$r['id'] . '">Open</a>';
              echo '</div>';
          }
          echo '</div>';
      }
      ?>
    </section>
  </main>
</body>
</html>

