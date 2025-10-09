<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'systemAdmin') {
    header("Location: /Views/login.php");
    exit();
}

require_once __DIR__ . '/../Controllers/SystemAdminController.php';

$controller = new SystemAdminController();
$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['org_id'])) {
        switch ($_POST['action']) {
            case 'approve':
                if ($controller->approveOrganisation($_POST['org_id'])) {
                    $message = '<div class="alert success">✓ Organization approved successfully! All associated users can now log in.</div>';
                } else {
                    $message = '<div class="alert error">✗ Failed to approve organization.</div>';
                }
                break;
            case 'reject':
                if ($controller->rejectOrganisation($_POST['org_id'])) {
                    $message = '<div class="alert success">✓ Organization rejected and removed from system.</div>';
                } else {
                    $message = '<div class="alert error">✗ Failed to reject organization.</div>';
                }
                break;
        }
    }
}

$pendingOrgs = $controller->getPendingOrganisations();
$approvedOrgs = $controller->getApprovedOrganisations();
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Approve Organizations - System Admin</title>
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
    .alert.warning {
      background: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
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
    .badge {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 12px;
      font-size: 0.85rem;
      font-weight: 600;
    }
    .badge.pending {
      background: #ffc107;
      color: #333;
    }
    .badge.approved {
      background: #28a745;
      color: white;
    }
    button {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      font-size: 0.9rem;
    }
    button.approve {
      background: #28a745;
      color: white;
      margin-right: 0.5rem;
    }
    button.approve:hover {
      background: #218838;
    }
    button.reject {
      background: #dc3545;
      color: white;
    }
    button.reject:hover {
      background: #c82333;
    }
    .no-data {
      text-align: center;
      padding: 2rem;
      color: #666;
      font-style: italic;
    }
  </style>
</head>
<body>
  <nav>
    <span>System Admin - Approve Organizations</span>
    <div>
      <a href="/Views/systemAdmin_home.php">Dashboard</a>
      <a href="/Views/systemAdmin_manageUsers.php">Manage Users</a>
      <a href="/Views/systemAdmin_approveOrgs.php">Approve Organizations</a>
      <a href="/Views/systemAdmin_manageContent.php">Manage Content</a>
      <a href="/Views/logout.php">Logout</a>
    </div>
  </nav>

  <main>
    <h1>Approve Organizations</h1>
    <p>Review and approve pending organization registrations. Approving an organization will activate all associated user accounts.</p>

    <?php echo $message; ?>

    <h2>⏳ Pending Approval (<?php echo count($pendingOrgs); ?>)</h2>
    <?php if (empty($pendingOrgs)): ?>
      <div class="no-data">No pending organizations at this time.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Organization Name</th>
            <th>Admin Users</th>
            <th>Registration Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pendingOrgs as $org): ?>
          <tr>
            <td><?php echo htmlspecialchars($org['id']); ?></td>
            <td><strong><?php echo htmlspecialchars($org['name']); ?></strong></td>
            <td><?php echo $org['admin_count']; ?> admin(s)</td>
            <td><?php echo date('Y-m-d', strtotime($org['created_at'])); ?></td>
            <td>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Approve this organization? All associated users will be able to log in.');">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="org_id" value="<?php echo $org['id']; ?>">
                <button type="submit" class="approve">✓ Approve</button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this organization? This will permanently delete the organization and all its users.');">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="org_id" value="<?php echo $org['id']; ?>">
                <button type="submit" class="reject">✗ Reject</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <h2>✓ Approved Organizations (<?php echo count($approvedOrgs); ?>)</h2>
    <?php if (empty($approvedOrgs)): ?>
      <div class="no-data">No approved organizations yet.</div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Organization Name</th>
            <th>Total Users</th>
            <th>Status</th>
            <th>Registration Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($approvedOrgs as $org): ?>
          <tr>
            <td><?php echo htmlspecialchars($org['id']); ?></td>
            <td><strong><?php echo htmlspecialchars($org['name']); ?></strong></td>
            <td><?php echo $org['user_count']; ?> user(s)</td>
            <td><span class="badge approved">Approved</span></td>
            <td><?php echo date('Y-m-d', strtotime($org['created_at'])); ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </main>
</body>
</html>