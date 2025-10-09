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
$message = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                if (!empty($_POST['name']) && !empty($_POST['email']) && !empty($_POST['password'])) {
                    if ($controller->addEmployee($_POST['name'], $_POST['email'], $_POST['password'], $orgId)) {
                        $message = '<div class="alert success">✓ Employee added successfully.</div>';
                    } else {
                        $message = '<div class="alert error">✗ Failed to add employee. Email may already exist.</div>';
                    }
                } else {
                    $message = '<div class="alert error">✗ All fields are required.</div>';
                }
                break;
            
            case 'update':
                if (!empty($_POST['user_id']) && !empty($_POST['name']) && !empty($_POST['email'])) {
                    if ($controller->updateEmployee($_POST['user_id'], $_POST['name'], $_POST['email'], $orgId)) {
                        $message = '<div class="alert success">✓ Employee updated successfully.</div>';
                    } else {
                        $message = '<div class="alert error">✗ Failed to update employee.</div>';
                    }
                } else {
                    $message = '<div class="alert error">✗ All fields are required.</div>';
                }
                break;
            
            case 'reset_password':
                if (!empty($_POST['user_id']) && !empty($_POST['new_password'])) {
                    if ($controller->resetPassword($_POST['user_id'], $_POST['new_password'], $orgId)) {
                        $message = '<div class="alert success">✓ Password reset successfully.</div>';
                    } else {
                        $message = '<div class="alert error">✗ Failed to reset password.</div>';
                    }
                } else {
                    $message = '<div class="alert error">✗ Password is required.</div>';
                }
                break;
            
            case 'delete':
                if ($controller->deleteEmployee($_POST['user_id'], $orgId)) {
                    $message = '<div class="alert success">✓ Employee deleted successfully.</div>';
                } else {
                    $message = '<div class="alert error">✗ Failed to delete employee.</div>';
                }
                break;
        }
    }
}

$employees = $controller->getOrganizationEmployees($orgId);
$orgName = $controller->getOrganizationName($orgId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Employees - Org Admin</title>
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
    .form-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #333;
    }
    input[type="text"], input[type="email"], input[type="password"] {
      width: 100%;
      padding: 0.75rem;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 1rem;
    }
    input:focus {
      outline: none;
      border-color: #17a2b8;
    }
    button {
      padding: 0.75rem 1.5rem;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-weight: 500;
      font-size: 1rem;
    }
    button.add {
      background: #28a745;
      color: white;
    }
    button.add:hover {
      background: #218838;
    }
    button.edit {
      background: #17a2b8;
      color: white;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      margin-right: 0.5rem;
    }
    button.edit:hover {
      background: #138496;
    }
    button.reset {
      background: #ffc107;
      color: #333;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
      margin-right: 0.5rem;
    }
    button.reset:hover {
      background: #e0a800;
    }
    button.delete {
      background: #dc3545;
      color: white;
      padding: 0.5rem 1rem;
      font-size: 0.9rem;
    }
    button.delete:hover {
      background: #c82333;
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
    .badge.orgadmin {
      background: #ffc107;
      color: #333;
    }
    .badge.employee {
      background: #28a745;
      color: white;
    }
    .edit-form, .password-form {
      display: none;
      background: #f8f9fa;
      padding: 1rem;
      margin-top: 0.5rem;
      border-radius: 4px;
    }
    .edit-form input, .password-form input {
      margin-bottom: 0.5rem;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1rem;
    }
  </style>
  <script>
    function toggleEdit(id) {
      const form = document.getElementById('edit-form-' + id);
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
    function togglePassword(id) {
      const form = document.getElementById('password-form-' + id);
      form.style.display = form.style.display === 'none' ? 'block' : 'none';
    }
  </script>
</head>
<body>
  <nav>
    <span>Org Admin - Manage Employees</span>
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
    <h1>Manage Employees</h1>
    <p>Add, edit, and manage employee accounts for <?php echo htmlspecialchars($orgName); ?>.</p>

    <?php echo $message; ?>

    <div class="form-card">
      <h2>➕ Enrol New Employee</h2>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-grid">
          <div class="form-group">
            <label for="name">Full Name</label>
            <input type="text" id="name" name="name" placeholder="John Doe" required>
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" required>
          </div>
        </div>
        <div class="form-group">
          <label for="password">Initial Password</label>
          <input type="password" id="password" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="add">Add Employee</button>
      </form>
    </div>

    <h2>👥 Organization Employees (<?php echo count($employees); ?>)</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($employees as $emp): ?>
        <tr>
          <td><?php echo htmlspecialchars($emp['id']); ?></td>
          <td><?php echo htmlspecialchars($emp['name']); ?></td>
          <td><?php echo htmlspecialchars($emp['email']); ?></td>
          <td>
            <span class="badge <?php echo $emp['role_id'] == 2 ? 'orgadmin' : 'employee'; ?>">
              <?php echo htmlspecialchars($emp['role_name']); ?>
            </span>
          </td>
          <td><?php echo date('Y-m-d', strtotime($emp['created_at'])); ?></td>
          <td>
            <button class="edit" onclick="toggleEdit(<?php echo $emp['id']; ?>)">Edit</button>
            <button class="reset" onclick="togglePassword(<?php echo $emp['id']; ?>)">Reset Password</button>
            <?php if ($emp['role_id'] == 3 && $emp['id'] != $user['id']): ?>
            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this employee?');">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="user_id" value="<?php echo $emp['id']; ?>">
              <button type="submit" class="delete">Delete</button>
            </form>
            <?php endif; ?>
          </td>
        </tr>
        <tr>
          <td colspan="6">
            <div id="edit-form-<?php echo $emp['id']; ?>" class="edit-form">
              <form method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="user_id" value="<?php echo $emp['id']; ?>">
                <input type="text" name="name" value="<?php echo htmlspecialchars($emp['name']); ?>" placeholder="Name" required>
                <input type="email" name="email" value="<?php echo htmlspecialchars($emp['email']); ?>" placeholder="Email" required>
                <button type="submit" class="add">Update</button>
                <button type="button" onclick="toggleEdit(<?php echo $emp['id']; ?>)">Cancel</button>
              </form>
            </div>
            <div id="password-form-<?php echo $emp['id']; ?>" class="password-form">
              <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="user_id" value="<?php echo $emp['id']; ?>">
                <input type="password" name="new_password" placeholder="New Password" required>
                <button type="submit" class="add">Reset Password</button>
                <button type="button" onclick="togglePassword(<?php echo $emp['id']; ?>)">Cancel</button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </main>
</body>
</html>