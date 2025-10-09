<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee') {
    header("Location: /Views/index.php");
    exit();
}

require_once __DIR__ . '/../Controllers/EmployeeController.php';

$controller = new EmployeeController();
$user = $_SESSION['user'];

// Get employee statistics and results
$stats = $controller->getEmployeeStats($user['id']);
$results = $controller->getEmployeeResults($user['id']);
$assessments = $controller->getAllAssessments();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Dashboard</title>
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
      background: #007BFF;
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
    h1, h2 {
      color: #333;
      margin-bottom: 1rem;
    }
    h2 {
      margin-top: 2rem;
      font-size: 1.3rem;
    }
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
      color: #007BFF;
      font-weight: bold;
    }
    .assessment-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-top: 1.5rem;
    }
    .assessment-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.2s;
    }
    .assessment-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .assessment-header {
      background: linear-gradient(135deg, #007BFF 0%, #0056b3 100%);
      color: white;
      padding: 1.5rem;
    }
    .assessment-header h3 {
      margin: 0 0 0.5rem 0;
    }
    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .badge.formative {
      background: rgba(255,255,255,0.2);
    }
    .badge.summative {
      background: rgba(255,255,255,0.3);
    }
    .badge.completed {
      background: #28a745;
      color: white;
    }
    .badge.pending {
      background: #ffc107;
      color: #333;
    }
    .assessment-body {
      padding: 1.5rem;
    }
    .assessment-body p {
      color: #666;
      margin-bottom: 1rem;
      line-height: 1.5;
    }
    .btn {
      display: inline-block;
      padding: 0.75rem 1.5rem;
      background: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 500;
      transition: background 0.3s;
    }
    .btn:hover {
      background: #0056b3;
    }
    .btn.completed {
      background: #6c757d;
      cursor: not-allowed;
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
      background: #007BFF;
      color: white;
      font-weight: 600;
    }
    tr:hover {
      background: #f8f9fa;
    }
    .score {
      font-size: 1.2rem;
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
      padding: 2rem;
      color: #666;
      background: white;
      border-radius: 8px;
      font-style: italic;
    }
  </style>
</head>
<body>
  <nav>
    <span>Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
    <div>
      <a href="/Views/employee_home.php">Home</a>
      <a href="/Views/content_overview.php">Training Content</a>
      <a href="/Views/logout.php">Logout</a>
    </div>
  </nav>

  <main>
    <h1>Employee Dashboard</h1>
    <p>Track your cybersecurity training progress and assessment results.</p>

    <div class="stats-grid">
      <div class="stat-card">
        <h3>Assessments Completed</h3>
        <div class="number"><?php echo $stats['completed']; ?></div>
      </div>
      <div class="stat-card">
        <h3>Average Score</h3>
        <div class="number"><?php echo $stats['average_score']; ?>%</div>
      </div>
      <div class="stat-card">
        <h3>Pending Results</h3>
        <div class="number"><?php echo $stats['pending']; ?></div>
      </div>
      <div class="stat-card">
        <h3>Available Assessments</h3>
        <div class="number"><?php echo $stats['total_available']; ?></div>
      </div>
    </div>

    <h2>📊 Your Assessment Results</h2>
    <?php if (empty($results)): ?>
      <div class="no-data">
        You haven't completed any assessments yet. Start with the assessments below!
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Assessment</th>
            <th>Type</th>
            <th>Score</th>
            <th>Status</th>
            <th>Completed</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($results as $result): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($result['assessment_title']); ?></strong>
              <?php if ($result['content_title']): ?>
                <br><small style="color: #666;">Content: <?php echo htmlspecialchars($result['content_title']); ?></small>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?php echo $result['assessment_type']; ?>">
                <?php echo ucfirst($result['assessment_type']); ?>
              </span>
            </td>
            <td>
              <?php if ($result['status'] === 'pending'): ?>
                <span style="color: #666;">-</span>
              <?php else: 
                $score = $result['score'];
                $scoreClass = 'high';
                if ($score < 70) $scoreClass = 'low';
                elseif ($score < 85) $scoreClass = 'medium';
              ?>
                <span class="score <?php echo $scoreClass; ?>"><?php echo number_format($score, 1); ?>%</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge <?php echo $result['status']; ?>">
                <?php echo ucfirst($result['status']); ?>
              </span>
            </td>
            <td><?php echo date('Y-m-d H:i', strtotime($result['completed_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h2>📝 Available Assessments</h2>
    <p>Take assessments to test your cybersecurity knowledge.</p>
    
    <div class="assessment-grid">
      <?php foreach ($assessments as $assessment): 
        $hasSubmitted = $controller->hasSubmitted($assessment['id'], $user['id']);
      ?>
      <div class="assessment-card">
        <div class="assessment-header">
          <h3><?php echo htmlspecialchars($assessment['title']); ?></h3>
          <span class="badge <?php echo $assessment['type']; ?>">
            <?php echo ucfirst($assessment['type']); ?>
          </span>
        </div>
        <div class="assessment-body">
          <p><?php echo htmlspecialchars(substr($assessment['description'], 0, 150)); ?>...</p>
          
          <?php if ($hasSubmitted): ?>
            <a href="#" class="btn completed" onclick="return false;">✓ Submitted</a>
          <?php else: ?>
            <a href="/Views/employee_takeAssessment.php?id=<?php echo $assessment['id']; ?>" class="btn">
              Take Assessment →
            </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </main>
</body>
</html>