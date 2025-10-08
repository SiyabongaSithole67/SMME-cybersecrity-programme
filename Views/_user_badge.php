<?php
// Small floating user badge that links to the user's home page
if (session_status() === PHP_SESSION_NONE) session_start();
$user = $_SESSION['user'] ?? null;
if (!$user) return;
$name = htmlspecialchars($user['name'] ?? '');
$roleId = (int)($user['role_id'] ?? 0);
$home = '/Views/login.php';
switch ($roleId) {
    case 1: $home = '/Views/SystemAdmin_home.php'; break;
    case 2: $home = '/Views/OrgAdmin_home.php'; break;
    case 3: $home = '/Views/employee_home.php'; break;
}
?>
<style>
#user-badge { position: fixed; top: 12px; right: 12px; background: rgba(0,0,0,0.75); color: #fff; padding: 6px 10px; border-radius: 6px; font-size: 0.9rem; z-index: 9999; box-shadow: 0 2px 6px rgba(0,0,0,0.2); }
#user-badge a { color: #fff; text-decoration: none; }
#user-badge small { display:block; color: rgba(255,255,255,0.8); font-size: 0.75rem; }
</style>
<div id="user-badge">
  <a href="<?= $home ?>"><?= $name ?></a>
  <small>My Home</small>
</div>
