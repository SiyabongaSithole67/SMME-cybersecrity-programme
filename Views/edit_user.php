<?php
session_start();
require_once __DIR__ . '/../Controllers/UserController.php';
require_once __DIR__ . '/../Models/UserModel.php';

if (!isset($_SESSION['user'])) {
    header('Location: /Views/index.php');
    exit();
}
$u = $_SESSION['user'];
$currentUser = new UserModel();
$currentUser->setId($u['id'] ?? null);
$currentUser->setName($u['name'] ?? null);
$currentUser->setEmail($u['email'] ?? null);
$currentUser->setRoleId((int)($u['role_id'] ?? 0));
$currentUser->setOrganisationId($u['organisation_id'] ?? null);

$controller = new UserController();
$id = $_GET['id'] ?? null;
if (!$id) { header('Location: /Views/manage_users.php?msg=missing_id'); exit(); }
$target = $controller->getUserById($currentUser, (int)$id);
if (!$target) { header('Location: /Views/manage_users.php?error=' . urlencode('Access denied or not found')); exit(); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit User</title>
  <style>
    form { width: 400px; margin: 40px auto; padding: 16px; background: #fafafa; border-radius: 8px; }
    label { display:block; margin-top:8px; }
    input, select { width:100%; padding:8px; }
  </style>
</head>
<body>
  <h2 style="text-align:center;">Edit User #<?= htmlspecialchars($target['id']) ?></h2>
  <form method="post" action="/Controllers/UserController.php?action=update">
    <input type="hidden" name="id" value="<?= htmlspecialchars($target['id']) ?>" />
    <label>Name</label>
    <input name="name" value="<?= htmlspecialchars($target['name']) ?>" />
    <label>Email</label>
    <input name="email" value="<?= htmlspecialchars($target['email']) ?>" />
    <?php if ($currentUser->getRoleId() == 1): // only system admin can change role/org/approved ?>
      <label>Role</label>
      <select name="role_id">
        <option value="1" <?= $target['role_id']==1? 'selected':'' ?>>SystemAdmin</option>
        <option value="2" <?= $target['role_id']==2? 'selected':'' ?>>OrgAdmin</option>
        <option value="3" <?= $target['role_id']==3? 'selected':'' ?>>Employee</option>
      </select>
      <label>Organisation ID</label>
      <input name="organisation_id" value="<?= htmlspecialchars($target['organisation_id']) ?>" />
      <label>Approved</label>
      <select name="approved">
        <option value="1" <?= $target['approved']==1? 'selected':'' ?>>Yes</option>
        <option value="0" <?= $target['approved']==0? 'selected':'' ?>>No</option>
      </select>
    <?php endif; ?>
    <div style="margin-top:12px;">
      <button type="submit">Save</button>
      <a href="/Views/manage_users.php" style="margin-left:12px">Cancel</a>
    </div>
  </form>
</body>
</html>
