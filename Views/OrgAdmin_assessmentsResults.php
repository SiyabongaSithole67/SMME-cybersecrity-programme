<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /Views/login.php");
    exit();
}

require_once __DIR__ . '/../Controllers/OrgAdminController.php';

$controller = new OrgAdminController();
$user = $_SESSION['user'];
$orgId = $user['organisation_id'];

// Get filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$assessmentType = null;
if ($filter === 'formative') {
    $assessmentType = 'formative';
} elseif ($filter === 'summative') {
    $assessmentType = 'summative';
}

$results = $controller->getOrganizationResults($orgId, $assessmentType);
$orgName = $controller->getOrganizationName($orgId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Assessment Results - Org Admin</title>
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
      max-width: 1400px;
      margin: 0 auto;
    }
    h1 {
      color: #333;
      margin-bottom: 1rem;
    }
    .filter-bar {
      background: white;
      padding: 1rem;
      border-radius: 8px;
      margin: 1.5rem 0;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      display: flex;
      gap: 1rem;
      align-items: center;
    }
    .filter-bar label {
      font-weight: 600;
      color: #333;
    }
    .filter-btn {
      padding: 0.5rem 1rem;
      border: 2px solid #17a2b8;
      background: white;
      color: #17a2b8;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      text-decoration: none;
      transition: all 0.3s;
    }
    .filter-btn:hover {
      background: #17a2b8;
      color: white;
    }
    .filter-btn.active {
      background: #17a2b8;
      color: white;
    }
    table {
      width: 100%;
      background: white;
      border-collapse: collapse;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-top: 1rem;
    }
    th, td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }
    th {
      background: #17a2b8;
      color: white;
      font-weight: 600;
    }
    tr:hover {
      background: #f8f9fa;
    }
    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .badge.formative {
      background: #17a2b8;
      color: white;
    }
    .badge.summative {
      background: #6f42c1;
      color: white;
    }
    .score {
      font-size: 1.1rem;
      font-weight: bold;
    }
    .score.high {
      color: #28a745;
    }
    .score.medium {
      color: #ffc107;
    }
    .score.low {
      color: #dc3545;
    }
    .no-data {
      text-align: center;
      padding: 3rem;
      color: #666;
      background: white;
      border-radius: 8px;
      margin-top: 2rem;
    }
    .summary-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }
    .summary-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      text-align: center;
    }
    .summary-card h3 {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
    }
    .summary-card .number {
      font-size: 2rem;
      color: #17a2b8;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <nav>
    <span>Org Admin - Assessment Results</span>
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
    <h1>📊 Assessment Results</h1>
    <p>View formative and summative assessment results for employees at <?php echo htmlspecialchars($orgName); ?>.</p>

    <?php
    // Calculate summary stats
    $totalResults = count($results);
    $avgScore = $totalResults > 0 ? array_sum(array_column($results, 'score')) / $totalResults : 0;
    $formativeCount = count(array_filter($results, function($r) { return $r['assessment_type'] === 'formative'; }));
    $summativeCount = count(array_filter($results, function($r) { return $r['assessment_type'] === 'summative'; }));
    ?>

    <div class="summary-cards">
      <div class="summary-card">
        <h3>Total Results</h3>
        <div class="number"><?php echo $totalResults; ?></div>
      </div>
      <div class="summary-card">
        <h3>Average Score</h3>
        <div class="number"><?php echo round($avgScore, 1); ?>%</div>
      </div>
      <div class="summary-card">
        <h3>Formative</h3>
        <div class="number"><?php echo $formativeCount; ?></div>
      </div>
      <div class="summary-card">
        <h3>Summative</h3>
        <div class="number"><?php echo $summativeCount; ?></div>
      </div>
    </div>

    <div class="filter-bar">
      <label>Filter by Type:</label>
      <a href="?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All Results</a>
      <a href="?filter=formative" class="filter-btn <?php echo $filter === 'formative' ? 'active' : ''; ?>">Formative</a>
      <a href="?filter=summative" class="filter-btn <?php echo $filter === 'summative' ? 'active' : ''; ?>">Summative</a>
    </div>

    <?php if (empty($results)): ?>
      <div class="no-data">
        <h2>No Assessment Results</h2>
        <p>No employees have completed assessments yet.</p>
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Employee</th>
            <th>Assessment</th>
            <th>Type</th>
            <th>Content</th>
            <th>Score</th>
            <th>Completed</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $result): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($result['employee_name']); ?></strong><br>
              <small style="color: #666;"><?php echo htmlspecialchars($result['employee_email']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($result['assessment_title']); ?></td>
            <td>
              <span class="badge <?php echo $result['assessment_type']; ?>">
                <?php echo ucfirst($result['assessment_type']); ?>
              </span>
            </td>
            <td><?php echo $result['content_title'] ? htmlspecialchars($result['content_title']) : '-'; ?></td>
            <td>
              <?php
              $score = $result['score'];
              $scoreClass = 'high';
              if ($score < 70) $scoreClass = 'low';
              elseif ($score < 85) $scoreClass = 'medium';
              ?>
              <span class="score <?php echo $scoreClass; ?>"><?php echo number_format($score, 1); ?>%</span>
            </td>
            <td><?php echo date('Y-m-d H:i', strtotime($result['completed_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</body>
</html>