
<?php
// view/admin/SystemAdminDashboard.php

// Ensure $data array is initialized for passing information from the controller
$data = $data ?? []; 
$pendingOrgs = $data['pendingOrgs'] ?? []; // Passed from OrganizationModel
$allUsers = $data['allUsers'] ?? ['system_admins' => [], 'org_admins' => [], 'employees' => []]; // Passed from SystemAdminModel
$allContent = $data['allContent'] ?? []; // Passed from SystemAdminModel

// Function to display flash messages (success/error banners)
function displayFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        echo '<div class="alert success">' . htmlspecialchars($_SESSION['flash_message']) . '</div>';
        unset($_SESSION['flash_message']);
    }
    if (isset($_SESSION['flash_error'])) {
        echo '<div class="alert error">' . htmlspecialchars($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>System Admin Dashboard</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 90%; margin: 20px auto; }
        .section { margin-bottom: 30px; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .alert { padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .success { background-color: #d4edda; color: #155724; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; color: #721c24; border-color: #f5c6cb; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    </style>
</head>
<body>

<div class="container">
    <h1>System Administration Panel</h1>

    <?php displayFlashMessage(); ?>

    <div class="section">
        <h2>Organization Approvals (New Accounts)</h2>
        <?php if (empty($pendingOrgs)): ?>
            <p>No new organizations are currently pending approval.</p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>Organization Name</th><th>Created At</th><th>Action</th></tr>
                <?php foreach ($pendingOrgs as $org): ?>
                    <tr>
                        <td><?= htmlspecialchars($org['org_id']) ?></td>
                        <td><?= htmlspecialchars($org['name']) ?></td>
                        <td><?= htmlspecialchars($org['created_at']) ?></td>
                        <td>
                            <a href="/admin/approve/<?= $org['org_id'] ?>" 
                               onclick="return confirm('Are you sure you want to approve <?= htmlspecialchars($org['name']) ?>?')"
                            >Approve</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>User Account Management</h2>
        
        <?php foreach ($allUsers as $groupName => $users): ?>
            <?php if (!empty($users)): ?>
                <h3><?= ucfirst(str_replace('_', ' ', $groupName)) ?> (<?= count($users) ?>)</h3>
                <table>
                    <tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th><th>Action</th></tr>
                    <?php 
                    // Determine the table name for deletion (simplistic mapping)
                    $roleTable = match ($groupName) {
                        'system_admins' => 'System_Admin',
                        'org_admins' => 'Organization_Admin',
                        'employees' => 'Employee',
                        default => ''
                    };
                    
                    foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['id']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td>
                                <a href="/admin/user/delete?id=<?= $user['id'] ?>&table=<?= $roleTable ?>" 
                                   onclick="return confirm('WARNING: Delete <?= htmlspecialchars($user['username']) ?> (<?= htmlspecialchars($user['role']) ?>)?')"
                                >Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
        
        <?php if (empty($allUsers['system_admins']) && empty($allUsers['org_admins']) && empty($allUsers['employees'])): ?>
            <p>No users found in the system.</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Content Management</h2>
        <?php if (empty($allContent)): ?>
            <p>No content items found in the system.</p>
        <?php else: ?>
            <table>
                <tr><th>ID</th><th>Title</th><th>Created By</th><th>Created At</th><th>Action</th></tr>
                <?php foreach ($allContent as $content): ?>
                    <tr>
                        <td><?= htmlspecialchars($content['content_id']) ?></td>
                        <td><?= htmlspecialchars($content['title']) ?></td>
                        <td><?= htmlspecialchars($content['created_by']) ?></td>
                        <td><?= htmlspecialchars($content['created_at']) ?></td>
                        <td>
                            <a href="/admin/content/edit/<?= $content['content_id'] ?>">Edit</a> | 
                            <a href="/admin/content/delete/<?= $content['content_id'] ?>" 
                               onclick="return confirm('Delete content: <?= htmlspecialchars($content['title']) ?>?')"
                            >Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
    </div>

</div>

</body>
</html>