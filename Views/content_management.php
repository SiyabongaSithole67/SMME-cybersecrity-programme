<?php
session_start();
require_once __DIR__ . '/../Controllers/ContentController.php';
require_once __DIR__ . '/../Models/UserModel.php';

// Make sure a user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: /Views/login.php");
    exit();
}

// Recreate a UserModel object from session data using setters
$currentUser = new UserModel();
$currentUser->setId($_SESSION['user']['id'] ?? null);
$currentUser->setName($_SESSION['user']['name'] ?? null);
$currentUser->setEmail($_SESSION['user']['email'] ?? null);
$currentUser->setRoleId((int)($_SESSION['user']['role_id'] ?? 0));
$currentUser->setOrganisationId($_SESSION['user']['organisation_id'] ?? null);

// Create controller and fetch content
$contentController = new ContentController();
$contentList = $contentController->listContent($currentUser);
?>
<?php include __DIR__ . '/_user_badge.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Content Management</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 0; }
    nav { background: #2c3e50; color: white; padding: 1rem; }
    nav a { color: white; margin-right: 1rem; text-decoration: none; }
    table { width: 90%; margin: 2rem auto; border-collapse: collapse; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background: #f0f0f0; }
    form { width: 90%; margin: 2rem auto; display: flex; gap: 1rem; }
    input[type="text"] { flex: 1; padding: 8px; }
    button { padding: 8px 12px; border: none; background: #007BFF; color: white; border-radius: 4px; }
  </style>
</head>
<body>
  <nav>
    <a href="/Views/admin_home.php">Dashboard</a>
    <a href="/Views/content_management.php">Content Management</a>
    <a href="/logout.php">Logout</a>
  </nav>

  <h2 style="text-align:center;">Manage Learning Content</h2>

  <?php if ($_SESSION['user']['role'] == 'systemAdmin'): ?>
  <!-- Add Content (only SystemAdmin) -->
  <form method="POST" action="/Controllers/ContentController.php?action=add">
    <input type="text" name="title" placeholder="Content Title" required />
    <input type="text" name="link" placeholder="Link" required />
    <button type="submit">Add Content</button>
  </form>
  <?php endif; ?>

  <table>
    <tr>
      <th>ID</th>
      <th>Title</th>
      <th>Link</th>
      <?php if ($_SESSION['user']['role'] == 'systemAdmin'): ?>
        <th>Actions</th>
      <?php endif; ?>
    </tr>
    <?php foreach ($contentList as $content): ?>
      <tr>
        <td><?= htmlspecialchars($content['id']) ?></td>
        <td><?= htmlspecialchars($content['title']) ?></td>
  <td><a href="/Views/Content.php?id=<?= htmlspecialchars($content['id']) ?>" target="_blank">Open</a></td>
        <?php if ($_SESSION['user']['role'] == 'systemAdmin'): ?>
          <td>
            <form method="POST" action="/Controllers/ContentController.php?action=delete" onsubmit="return confirm('Delete this content?');">
              <input type="hidden" name="id" value="<?= htmlspecialchars($content['id']) ?>" />
              <button type="submit" style="background:#dc3545;">Delete</button>
            </form>
          </td>
        <?php endif; ?>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>