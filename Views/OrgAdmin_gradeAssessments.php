<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: /Views/login.php");
    exit();
}

require_once __DIR__ . '/../Config/databaseUtil.php';

$db = (new DatabaseUtil())->connect();
$user = $_SESSION['user'];
$orgId = $user['organisation_id'];
$message = '';

// Handle grading
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['result_id'], $_POST['score'])) {
    $resultId = $_POST['result_id'];
    $score = $_POST['score'];
    
    try {
        $stmt = $db->prepare("
            UPDATE results r
            SET score = :score, status = 'graded'
            WHERE r.id = :id 
            AND EXISTS (
                SELECT 1 FROM users u 
                WHERE u.id = r.user_id AND u.organisation_id = :org_id
            )
        ");
        $stmt->bindParam(':score', $score);
        $stmt->bindParam(':id', $resultId, PDO::PARAM_INT);
        $stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $message = '<div class="alert success">✓ Assessment graded successfully!</div>';
        } else {
            $message = '<div class="alert error">✗ Failed to grade assessment.</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert error">✗ Error: ' . $e->getMessage() . '</div>';
    }
}

// Get pending submissions for this organization
$stmt = $db->prepare("
    SELECT 
        r.id, r.answer_file, r.completed_at,
        u.name as employee_name, u.email as employee_email,
        a.title as assessment_title, a.type as assessment_type
    FROM results r
    INNER JOIN users u ON r.user_id = u.id
    INNER JOIN assessments a ON r.assessment_id = a.id
    WHERE u.organisation_id = :org_id AND r.status = 'pending'
    ORDER BY r.completed_at DESC
");
$stmt->bindParam(':org_id', $orgId, PDO::PARAM_INT);
$stmt->execute();
$pendingResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Grade Assessments - Org Admin</title>
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
    .alert {
      padding: 1rem;
      margin: 1rem 0;
      border-radius: 4px;
    }
    .alert.success {
      background: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }
    .alert.error {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #f5c6cb;
    }
    .submission-grid {
      display: grid;
      gap: 1.5rem;
      margin-top: 2rem;
    }
    .submission-card {
      background: white;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 1.5rem;
    }
    .submission-header {
      display: flex;
      justify-content: space-between;
      align-items: start;
      margin-bottom: 1rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid #eee;
    }
    .submission-header h3 {
      color: #333;
      margin: 0;
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
    .submission-info {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .info-item {
      color: #666;
    }
    .info-item strong {
      display: block;
      color: #333;
      margin-bottom: 0.25rem;
    }
    .grade-form {
      display: flex;
      gap: 1rem;
      align-items: center;
      background: #f8f9fa;
      padding: 1rem;
      border-radius: 4px;
    }
    .grade-form label {
      font-weight: 600;
      color: #333;
    }
    .grade-form input[type="number"] {
      width: 100px;
      padding: 0.5rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }
    .grade-form button {
      padding: 0.5rem 1.5rem;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
    }
    .grade-form button:hover {
      background: #218838;
    }
    .download-link {
      display: inline-block;
      padding: 0.5rem 1rem;
      background: #007BFF;
      color: white;
      text-decoration: none;
      border-radius: 4px;
      font-weight: 500;
      margin-top: 0.5rem;
    }
    .download-link:hover {
      background: #0056b3;
    }
    .no-data {
      text-align: center;
      padding: 3rem;
      color: #666;
      background: white;
      border-radius: 8px;
      margin-top: 2rem;
    }
  </style>
</head>
<body>
  <nav>
    <span>Org Admin - Grade Assessments</span>
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
    <h1>📝 Grade Pending Assessments</h1>
    <p>Review and grade employee assessment submissions.</p>

    <?php echo $message; ?>

    <?php if (empty($pendingResults)): ?>
      <div class="no-data">
        <h2>✓ All Caught Up!</h2>
        <p>There are no pending assessments to grade at this time.</p>
      </div>
    <?php else: ?>
      <div class="submission-grid">
        <?php foreach ($pendingResults as $submission): ?>
        <div class="submission-card">
          <div class="submission-header">
            <div>
              <h3><?php echo htmlspecialchars($submission['assessment_title']); ?></h3>
              <span class="badge <?php echo $submission['assessment_type']; ?>">
                <?php echo ucfirst($submission['assessment_type']); ?>
              </span>
            </div>
          </div>

          <div class="submission-info">
            <div class="info-item">
              <strong>Employee</strong>
              <?php echo htmlspecialchars($submission['employee_name']); ?><br>
              <small><?php echo htmlspecialchars($submission['employee_email']); ?></small>
            </div>
            <div class="info-item">
              <strong>Submitted</strong>
              <?php echo date('Y-m-d H:i', strtotime($submission['completed_at'])); ?>
            </div>
            <div class="info-item">
              <strong>Answer File</strong>
              <?php if ($submission['answer_file']): ?>
                <a href="/uploads/assessments/<?php echo htmlspecialchars($submission['answer_file']); ?>" 
                   class="download-link" target="_blank" download>
                  📥 Download Answer
                </a>
              <?php else: ?>
                No file uploaded
              <?php endif; ?>
            </div>
          </div>

          <form method="POST" class="grade-form">
            <input type="hidden" name="result_id" value="<?php echo $submission['id']; ?>">
            <label for="score_<?php echo $submission['id']; ?>">Score:</label>
            <input type="number" 
                   id="score_<?php echo $submission['id']; ?>" 
                   name="score" 
                   min="0" 
                   max="100" 
                   step="0.1" 
                   placeholder="0-100"
                   required>
            <span>%</span>
            <button type="submit">Submit Grade</button>
          </form>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>
</body>
</html>