<?php
session_start();
require_once __DIR__ . '/../Controllers/UserController.php';
require_once __DIR__ . '/../Models/UserModel.php';

// Helper to get role name
function getRoleName($role_id) {
    switch ($role_id) {
        case 1: return 'System Admin';
        case 2: return 'Org Admin';
        case 3: return 'Employee';
        default: return 'Unknown';
    }
}

// Get current user from session
$currentUser = null;
if (isset($_SESSION['user'])) {
    $u = $_SESSION['user'];
    $currentUser = new UserModel();
    $currentUser->setId($u['id'] ?? null);
    $currentUser->setName($u['name'] ?? null);
    $currentUser->setEmail($u['email'] ?? null);
    $currentUser->setRoleId($u['role_id'] ?? null);
    $currentUser->setOrganisationId($u['organisation_id'] ?? null);
}

if (!$currentUser || !in_array($currentUser->getRoleId(), [1,2])) {
    die('Access denied: Only SystemAdmins or OrgAdmins can access this page.');
}

$userController = new UserController();

// Handle form submission
$addUserMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $role_id = 3; // Only employees can be added by OrgAdmin
    $organisation_id = $currentUser->getOrganisationId();

    if ($name && $email && $password) {
        $userData = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role_id' => $role_id,
            'organisation_id' => $organisation_id
        ];
        $success = $userController->createUser($currentUser, $userData);
        if ($success) {
            $addUserMsg = 'User added successfully!';
        } else {
            $addUserMsg = 'Failed to add user.';
        }
    } else {
        $addUserMsg = 'Please fill in all fields.';
    }
}

// Get users in this organisation
$users = $userController->listUsers($currentUser);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <style>
        table { border-collapse: collapse; width: 80%; margin: 20px auto; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f0f0f0; }
        .actions { display: flex; gap: 8px; }
        .container { width: 90%; margin: 0 auto; }
        h1 { text-align: center; }
        form { margin: 20px auto; width: 60%; background: #fafafa; padding: 16px; border-radius: 8px; }
        label { display: block; margin-top: 8px; }
        input, select { width: 100%; padding: 6px; margin-top: 4px; }
        button { margin-top: 12px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Users</h1>

    <h2>Users in Your Organisation</h2>
    <?php if ($addUserMsg): ?>
        <p style="color: green; font-weight: bold;"> <?= htmlspecialchars($addUserMsg) ?> </p>
    <?php endif; ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user['id']) ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= getRoleName($user['role_id']) ?></td>
                <td class="actions">
                    <button disabled>Edit</button>
                    <button disabled>Delete</button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add New Employee</h2>
    <form method="post" action="">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="role_id">Role:</label>
        <select id="role_id" name="role_id" required disabled>
            <option value="3">Employee</option>
        </select>

        <button type="submit">Add User</button>
    </form>
</div>
</body>
</html>
