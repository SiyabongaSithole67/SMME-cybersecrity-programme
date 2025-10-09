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

$stats = $controller->getOrganizationStats($orgId);
$employeePerformance = $controller->getEmployeePerformance($orgId);
$assessmentBreakdown = $controller->getAssessmentTypeBreakdown($orgId);
$orgName = $controller->getOrganizationName($orgId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Analytics - Org Admin</title>
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
    h1, h2 {
      color: #333;
      margin-bottom: 1rem;
    }
    h2 {
      margin-top: 2rem;
      font-size: 1.3rem;
    }
    .metrics-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1.5rem;
      margin: 2rem 0;
    }
    .metric-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .metric-card h3 {
      color: #666;
      font-size: 0.9rem;
      margin-bottom: 0.5rem;
      text-transform: uppercase;
    }
    .metric-card .value {
      font-size: 2.5rem;
      color: #17a2b8;
      font-weight: bold;
    }
    .metric-card .subtitle {
      color: #999;
      font-size: 0.85rem;
      margin-top: 0.5rem;
    }
    .chart-container {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
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
    .progress-bar {
      width: 100%;
      height: 20px;
      background: #e9ecef;
      border-radius: 10px;
      overflow: hidden;
    }
    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #17a2b8, #138496);
      transition: width 0.3s;
    }
    .score-badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.9rem;
      font-weight: 600;
    }
    .score-badge.excellent {
      background: #28a745;
      color: white;
    }
    .score-badge.good {
      background: #17a2b8;
      color: white;
    }
    .score-badge.fair {
      background: #ffc107;
      color: #333;
    }
    .score-badge.poor {
      background: #dc3545;
      color: white;
    }
    .insight-box {
      background: #e7f6f8;
      border-left: 4px solid #17a2b8;
      padding: 1.5rem;
      margin: 2rem 0;
      border-radius: 4px;
    }
    .insight-box h3 {
      color: #17a2b8;
      margin-bottom: 0.5rem;
    }
    .insight-box ul {
      margin-left: 1.5rem;
      color: #333;
    }
    .insight-box li {
      margin: 0.5rem 0;
    }
  </style>
</head>
<body>
  <nav>
    <span>Org Admin - Analytics</span>
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
    <h1>📈 Analytics & Insights</h1>
    <p>Measure the effectiveness of the cybersecurity awareness programme at <?php echo htmlspecialchars($orgName); ?>.</p>

    <div class="metrics-grid">
      <div class="metric-card">
        <h3>Participation Rate</h3>
        <div class="value">
          <?php 
          $participationRate = $stats['total_employees'] > 0 
            ? round(($stats['active_employees'] / $stats['total_employees']) * 100) 
            : 0;
          echo $participationRate;
          ?>%
        </div>
        <div class="subtitle"><?php echo $stats['active_employees']; ?> of <?php echo $stats['total_employees']; ?> employees active</div>
      </div>
      <div class="metric-card">
        <h3>Average Score</h3>
        <div class="value"><?php echo $stats['average_score']; ?>%</div>
        <div class="subtitle">Across all assessments</div>
      </div>
      <div class="metric-card">
        <h3>Total Assessments</h3>
        <div class="value"><?php echo $stats['assessments_completed']; ?></div>
        <div class="subtitle">Completed by employees</div>
      </div>
      <div class="metric-card">
        <h3>Avg per Employee</h3>
        <div class="value">
          <?php 
          $avgPerEmployee = $stats['active_employees'] > 0 
            ? round($stats['assessments_completed'] / $stats['active_employees'], 1) 
            : 0;
          echo $avgPerEmployee;
          ?>
        </div>
        <div class="subtitle">Assessments per active employee</div>
      </div>
    </div>

    <?php if (!empty($assessmentBreakdown)): ?>
    <div class="chart-container">
      <h2>Assessment Type Breakdown</h2>
      <p>Performance comparison between formative and summative assessments.</p>
      <table style="margin-top: 1.5rem;">
        <thead>
          <tr>
            <th>Assessment Type</th>
            <th>Count</th>
            <th>Average Score</th>
            <th>Performance</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($assessmentBreakdown as $breakdown): ?>
          <tr>
            <td><strong><?php echo ucfirst($breakdown['type']); ?></strong></td>
            <td><?php echo $breakdown['count']; ?> completed</td>
            <td><?php echo round($breakdown['avg_score'], 1); ?>%</td>
            <td>
              <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo round($breakdown['avg_score']); ?>%;"></div>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

    <h2>Employee Performance Rankings</h2>
    <p>Individual employee performance and engagement metrics.</p>
    <table>
      <thead>
        <tr>
          <th>Rank</th>
          <th>Employee</th>
          <th>Assessments Taken</th>
          <th>Average Score</th>
          <th>Performance</th>
          <th>Last Activity</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $rank = 1;
        foreach ($employeePerformance as $emp): 
          $score = round($emp['avg_score'] ?? 0, 1);
          $scoreClass = 'poor';
          if ($score >= 90) $scoreClass = 'excellent';
          elseif ($score >= 80) $scoreClass = 'good';
          elseif ($score >= 70) $scoreClass = 'fair';
        ?>
        <tr>
          <td><strong>#<?php echo $rank++; ?></strong></td>
          <td>
            <strong><?php echo htmlspecialchars($emp['name']); ?></strong><br>
            <small style="color: #666;"><?php echo htmlspecialchars($emp['email']); ?></small>
          </td>
          <td><?php echo $emp['assessments_taken']; ?></td>
          <td><span class="score-badge <?php echo $scoreClass; ?>"><?php echo $score; ?>%</span></td>
          <td>
            <div class="progress-bar">
              <div class="progress-fill" style="width: <?php echo $score; ?>%;"></div>
            </div>
          </td>
          <td><?php echo $emp['last_activity'] ? date('Y-m-d', strtotime($emp['last_activity'])) : 'No activity'; ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="insight-box">
      <h3>💡 Key Insights</h3>
      <ul>
        <?php
        // Generate insights
        if ($participationRate < 50) {
            echo "<li>⚠️ Low participation rate ({$participationRate}%). Consider encouraging more employees to engage with the training materials.</li>";
        } elseif ($participationRate >= 80) {
            echo "<li>✅ Excellent participation rate ({$participationRate}%)! Your employees are highly engaged.</li>";
        }

        if ($stats['average_score'] >= 85) {
            echo "<li>✅ Strong performance with an average score of {$stats['average_score']}%.</li>";
        } elseif ($stats['average_score'] < 70) {
            echo "<li>⚠️ Average score ({$stats['average_score']}%) indicates room for improvement. Consider reviewing training materials.</li>";
        }

        if ($stats['active_employees'] > 0 && $avgPerEmployee < 2) {
            echo "<li>📊 Employees are completing an average of {$avgPerEmployee} assessments. Encourage more regular engagement.</li>";
        }

        // Check for inactive employees
        $inactiveCount = $stats['total_employees'] - $stats['active_employees'];
        if ($inactiveCount > 0) {
            echo "<li>👥 {$inactiveCount} employee(s) have not completed any assessments yet. Follow up to boost engagement.</li>";
        }
        ?>
      </ul>
    </div>
  </main>
</body>
</html>