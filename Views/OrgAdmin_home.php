<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /Views/index.php");
    exit();
}

require_once __DIR__ . '/../Controllers/OrgAdminController.php';

$controller = new OrgAdminController();
$user = $_SESSION['user'];
$orgId = $user['organisation_id'];

$stats = $controller->getOrganizationStats($orgId);
$orgName = $controller->getOrganizationName($orgId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Organization Admin Dashboard</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
    }
    nav {
      background: #17a2b8;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    nav a {
      color: white;
      margin-left: 1.5rem;
      text-decoration: none;
      font-weight: 500;
    }
    nav a:hover {
      text-decoration: underline;
    }
    main {
      padding: 2rem;
      max-width: 1200px;
      margin: 0 auto;
    }
    h1 {
      color: #333;
      margin-bottom: 0.5rem;
    }
    .org-name {
      color: #17a2b8;
      font-size: 1.2rem;
      font-weight: bold;
      margin-bottom: 1rem;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin: 2rem 0;
    }
    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      text-align: center;
    }
    .stat-card h3 {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      text-transform: uppercase;
    }
    .stat-card .number {
      font-size: 2.5rem;
      color: #17a2b8;
      font-weight: bold;
    }
    .stat-card .subtitle {
      color: #999;
      font-size: 0.85rem;
      margin-top: 0.5rem;
    }
    .quick-actions {
      margin-top: 2rem;
    }
    .action-buttons {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    .action-btn {
      background: #17a2b8;
      color: white;
      padding: 1rem;
      text-align: center;
      text-decoration: none;
      border-radius: 6px;
      font-weight: 500;
      transition: background 0.3s;
    }
    .action-btn:hover {
      background: #138496;
    }
  </style>
</head>
<body>
  <nav>
    <span>Welcome, <?php echo htmlspecialchars($user['name']); ?> (Organization Admin)</span>
    <div>
      <a href="/Views/OrgAdmin_home.php">Dashboard</a>
      <a href="/Views/OrgAdmin_manageEmployees.php">Manage Employees</a>
      <a href="/Views/OrgAdmin_viewContent.php">Training Content</a>
      <a href="/Views/OrgAdmin_assessmentResults.php">Assessment Results</a>
      <a href="/Views/OrgAdmin_gradeAssessments.php">Grade Assessments</a>
      <a href="/Views/OrgAdmin_analytics.php">Analytics</a>
      <a href="/Views/logout.php">Logout</a>
    </div>
  </nav>

  <main>
    <h1>Organization Admin Dashboard</h1>
    <div class="org-name">📊 <?php echo htmlspecialchars($orgName); ?></div>
    <p>Manage your organization's employees, view training progress, and analyze results.</p>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Total Employees</h3>
        <div class="number"><?php echo $stats['total_employees']; ?></div>
        <div class="subtitle"><?php echo $stats['active_employees']; ?> active</div>
      </div>
      <div class="stat-card">
        <h3>Assessments Completed</h3>
        <div class="number"><?php echo $stats['assessments_completed']; ?></div>
      </div>
      <div class="stat-card">
        <h3>Average Score</h3>
        <div class="number"><?php echo $stats['average_score']; ?>%</div>
      </div>
      <div class="stat-card">
        <h3>Engagement Rate</h3>
        <div class="number">
          <?php 
          $engagement = $stats['total_employees'] > 0 
            ? round(($stats['active_employees'] / $stats['total_employees']) * 100) 
            : 0;
          echo $engagement;
          ?>%
        </div>
        <div class="subtitle">employees participating</div>
      </div>
    </div>

    <div class="quick-actions">
      <h2>Quick Actions</h2>
      <div class="action-buttons">
        <a href="/Views/OrgAdmin_manageEmployees.php" class="action-btn">👥 Manage Employees</a>
        <a href="/Views/OrgAdmin_viewContent.php" class="action-btn">📚 View Training Content</a>
        <a href="/Views/OrgAdmin_gradeAssessments.php" class="action-btn">✅ Grade Assessments</a>
        <a href="/Views/OrgAdmin_assessmentResults.php" class="action-btn">📊 View Results</a>
        <a href="/Views/OrgAdmin_analytics.php" class="action-btn">📈 View Analytics</a>
      </div>
    </div>
  </main>
</body>
</html>