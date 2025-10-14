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

// Read status messages from controller redirects (msg or error)
$status = $_GET['msg'] ?? null;
$error = $_GET['error'] ?? null;
$statusMsg = '';
if ($error) {
    $statusMsg = 'Error: ' . urldecode($error);
} elseif ($status) {
    switch ($status) {
        case 'added': $statusMsg = 'User added successfully.'; break;
        case 'duplicate_email': $statusMsg = 'Email already exists.'; break;
        case 'missing_fields': $statusMsg = 'Please fill in all required fields.'; break;
        case 'invalid_method': $statusMsg = 'Invalid request method.'; break;
        case 'missing_id': $statusMsg = 'Missing user id.'; break;
        case 'approved': $statusMsg = 'User approved.'; break;
        case 'password_reset': $statusMsg = 'Password reset successfully.'; break;
            case 'updated': $statusMsg = 'User updated successfully.'; break;
            case 'deleted': $statusMsg = 'User deleted successfully.'; break;
        case 'bad_password': $statusMsg = 'Password is invalid (min 6 chars).'; break;
        default: $statusMsg = htmlspecialchars($status); break;
    }
}

// Get users in this organisation
$users = $userController->listUsers($currentUser);
// If system admin, load organisations for the add-user form
$organisations = [];
if ($currentUser->getRoleId() == 1) {
    require_once __DIR__ . '/../Controllers/OrganizationController.php';
    $orgCtrl = new OrganisationController();
    $organisations = $orgCtrl->listOrganisations($currentUser);
}
?>
<?php include __DIR__ . '/_user_badge.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <style>
    table {
        /* Set a max-width to allow the table to grow */
        border-collapse: collapse;
        width: 95%; /* Increased width for better button fit */
        margin: 16px auto;
        table-layout: auto; /* Ensures column widths are respected */
    }

    th, td {
        border: 1px solid #ccc;
        padding: 4px 8px;
        text-align: left;
    }
    /* Set a specific width for the Actions column */
    th:nth-child(5), td:nth-child(5) {
        width: 270px; /* Give the Actions column enough space for all buttons */
        min-width: 320px;
    }
    th { background: #f0f0f0; }
    /* compact rows */
    table tr {
        height: 36px;
    }
    .actions {
        display: flex;
        gap: 4px; /* Slightly reduced gap to save space */
        align-items: center;
        /* Ensure content doesn't wrap inside the actions cell */
        flex-wrap: nowrap; 
    }
    .actions { display: flex; gap: 4px; align-items: center; }
        .container { width: 90%; margin: 0 auto; }
        h1 { text-align: center; }
        label { display: block; margin-top: 8px; }
        input, select { width: 100%; padding: 6px; margin-top: 4px; }
        
        button {  border: none; background: #007BFF; color: white; border-radius: 4px;margin-top: 4px;}
        /* Make inline action buttons compact to match Approve/Reset */
        .actions button, .actions form button {
            margin-top: 0;
            padding: 4px 8px;
            font-size: 0.85rem;
            height: 28px;
            line-height: 20px;
            box-sizing: border-box;
            align-items: center;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Users</h1>

    <?php if ($statusMsg): ?>
        <p style="color: green; font-weight: bold;"> <?= htmlspecialchars($statusMsg) ?> </p>
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
                    <?php if ($currentUser->getRoleId() == 1 || ($currentUser->getRoleId() == 2 && $user['organisation_id'] == $currentUser->getOrganisationId())): ?>
                        <a href="/Views/edit_user.php?id=<?= htmlspecialchars($user['id']) ?>"><button type="button">Edit</button></a>
                        
                       
                        <form method="post" action="/Controllers/UserController.php?action=reset"  style="margin-bottom: 0px; "onsubmit="return resetPrompt(this);">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>" />
                            <input type="hidden" name="new_password" />
                            <button type="submit">Reset Password</button>
                        </form>
                        <?php if ($user['id'] != $currentUser->getId()): // don't offer delete for yourself ?>
                            <form method="post" action="/Controllers/UserController.php?action=delete" style="margin-bottom: 0px; "onsubmit="return confirmDelete(this, '<?= htmlspecialchars(addslashes($user['name'])) ?>');">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>" />
                                <button type="submit" style="background:#dc3545;">Delete</button>
                            </form>
                        <?php endif; ?>
                        <?php if (!$user['approved']): ?>
                            <form method="post" action="/Controllers/UserController.php?action=approve"style="margin-bottom: 0px; " ">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($user['id']) ?>" />
                                <button type="submit" style="background:#2e8b57;">Approve</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- no actions allowed for this row -->
                        <span style="color:#888">-</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Add New Employee</h2>
        <form method="post" action="/Controllers/UserController.php?action=add">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <?php if ($currentUser->getRoleId() == 1): ?>
            <!-- SystemAdmin may choose role and organisation -->
            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required>
                <option value="1">SystemAdmin</option>
                <option value="2">OrgAdmin</option>
                <option value="3" selected>Employee</option>
            </select>

            <label for="organisation_id">Organisation:</label>
            <select id="organisation_id" name="organisation_id">
                <option value="">-- None / Global --</option>
                <?php foreach ($organisations as $org): ?>
                    <option value="<?= htmlspecialchars($org['id']) ?>"><?= htmlspecialchars($org['name']) ?></option>
                <?php endforeach; ?>
            </select>
        <?php else: ?>
            <!-- OrgAdmin can only create employees in their own organisation -->
            <label for="role_id">Role:</label>
            <select id="role_id" name="role_id" required disabled>
                <option value="3">Employee</option>
            </select>
        <?php endif; ?>

        <button type="submit">Add User</button>
    </form>
</div>
<script>
function resetPrompt(form) {
    var pw = prompt('Enter new password for the user (min 6 chars):');
    if (!pw) return false;
    if (pw.length < 6) { alert('Password too short'); return false; }
    var hidden = form.querySelector('input[name="new_password"]');
    if (hidden) hidden.value = pw;
    return true;
}
function confirmDelete(form, name) {
    return confirm('Delete user "' + name + '"? This action cannot be undone.');
}
</script>
</body>
</html>
