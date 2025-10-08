<?php
session_start();
// Require the Organisation Controller and the UserModel for authentication logic
require_once __DIR__ . '/../Controllers/OrganizationController.php';

require_once __DIR__ . '/../Models/OrganizationModel.php'; // Included to match user's file structure assumption

/**
 * Helper function to format the 'approved' status for display.
 * @param int $approved_flag 0 for pending, 1 for approved.
 * @return string HTML formatted status.
 */

// --- Authentication and Authorization ---
$currentUser = null;
if (isset($_SESSION['user'])) {
    $u = $_SESSION['user'];
    // Instantiate UserModel for current user check (necessary for listOrganisations role check)
    $currentUser = new UserModel();
    $currentUser->setId($u['id'] ?? null);
    $currentUser->setRoleId($u['role_id'] ?? null);
    $currentUser->setOrganisationId($u['organisation_id'] ?? null);
    $currentUser->setName($u['name'] ?? null);
}

// Restrict access: Only SystemAdmins (Role ID 1) can fully manage organisations
if (!$currentUser || $currentUser->getRoleId() != 1) {
    die('Access denied: Only SystemAdmins can access this page.');
}

// Initialize the controller and fetch data
$orgController = new OrganisationController();

// --- Status Message Handling ---
// Read status messages from controller redirects (msg or error)
$status = $_GET['msg'] ?? null;
$error = $_GET['error'] ?? null;
$statusMsg = '';
if ($error) {
    $statusMsg = 'Error: ' . urldecode($error);
} elseif ($status) {
    switch ($status) {
        case 'added': $statusMsg = 'Organisation added successfully.'; break;
        case 'updated': $statusMsg = 'Organisation updated successfully.'; break;
        case 'deleted': $statusMsg = 'Organisation deleted successfully.'; break;
        case 'approved': $statusMsg = 'Organisation approved.'; break;
        case 'duplicate_name': $statusMsg = 'Organisation name already exists.'; break;
        case 'missing_fields': $statusMsg = 'Please fill in all required fields.'; break;
        case 'invalid_method': $statusMsg = 'Invalid request method.'; break;
        case 'missing_id': $statusMsg = 'Missing organisation id.'; break;
        default: $statusMsg = htmlspecialchars($status); break;
    }
}

// --- Fetch Data ---
// SystemAdmin can list all organisations
$organisations = $orgController->listOrganisations($currentUser);

?>
<?php include __DIR__ . '/_user_badge.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Organisations</title>
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
        padding: 2px 8px;
        text-align: left;
    }
    /* Set a specific width for the Actions column */
    th:nth-child(5), td:nth-child(5) {
        width: 300px; /* Give the Actions column enough space for all buttons */
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
        form { margin: 20px auto; width: 60%; background: #fafafa; padding: 2px; border-radius: 8px; }
        label { display: block; margin-top: 8px; }
        input, select { width: 100%; padding: 6px; margin-top: 4px; }
        button {  border: none; background: #007BFF; color: white; border-radius: 4px; }
        /* Make inline action buttons compact to match Approve/Reset */
        .actions button, .actions form button {
            margin-top: 0;
            padding: 4px 8px;
            font-size: 0.85rem;
            height: 28px;
            line-height: 20px;
            box-sizing: border-box;
        }
    
    /* Form styling for Add Organisation */
    .add-form {
        margin: 20px auto;
        padding: 15px; /* Reduced padding */
        background: #fafafa; /* Light background from manage_users form */
        border: 1px solid #e9ecef;
        border-radius: 8px;
        width: 60%; /* Width from manage_users form */
        max-width: 500px;
        box-shadow: none; /* Removed box shadow */
    }
    label { display: block; margin-top: 8px; font-weight: 600; color: #495057; }
    input { 
        width: 100%; 
        padding: 6px; /* Compact padding from manage_users input */
        margin-top: 4px; 
        border: 1px solid #ced4da; 
        border-radius: 4px; 
        box-sizing: border-box;
        transition: border-color 0.3s;
    }
    input:focus { border-color: #007bff; outline: none; box-shadow: 0 0 0 0.1rem rgba(0, 123, 255, 0.25); }

    /* Status Messages */
    /* Simple styling to match the manage_users.php success message */
    .status-msg {
        color: green; 
        font-weight: bold;
        text-align: center;
        margin: 10px 0;
    }
    .status-msg.error {
        color: #dc3545; /* Red for errors */
    }
    .status-msg.warning {
        color: #ffc107; /* Yellow for warnings */
    }
    </style>
</head>
<body>
<div class="container">
    <h1>Manage Organisations</h1>

    <?php if ($statusMsg): ?>
        <?php 
            $statusClass = '';
            if ($error) { $statusClass = 'error'; }
            elseif (in_array($status, ['duplicate_name', 'missing_fields', 'missing_id', 'invalid_method'])) { $statusClass = 'warning'; }
        ?>
        <p class="status-msg <?= $statusClass ?>"> 
            <?= htmlspecialchars($statusMsg) ?> 
        </p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($organisations)): ?>
            <tr>
                <td colspan="4" style="text-align: center; color: #6c757d;">No organisations found.</td>
            </tr>
        <?php else: ?>
            <?php foreach ($organisations as $org): ?>
                <tr>
                    <td><?= htmlspecialchars($org['id']) ?></td>
                    <td><?= htmlspecialchars($org['name']) ?></td>
                    <td class="actions">
                       
                        
                        <!-- Delete Action -->
                        <form method="post" action="/Controllers/OrganizationController.php?action=delete" style="display:inline" onsubmit="return confirmDelete(this, '<?= htmlspecialchars(addslashes($org['name'])) ?>');">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($org['id']) ?>" />
                            <button type="submit" style="background:#dc3545;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <div class="add-form">
        <h2>Add New Organisation</h2>
        <form method="post" action="/Controllers/OrganizationController.php?action=add">
            <label for="name">Organisation Name:</label>
            <input type="text" id="name" name="name" required placeholder="Enter organisation name">

            <button type="submit" class="add-btn">Add Organisation</button>
        </form>
    </div>
</div>
<script>
    /**
     * Shows a confirmation dialog before submitting the delete form.
     * @param {HTMLFormElement} form The form to submit if confirmed.
     * @param {string} name The name of the organisation being deleted.
     * @returns {boolean} True to submit, False to cancel.
     */
    function confirmDelete(form, name) {
        // IMPORTANT: Avoid using standard alert/confirm in an iframe environment.
        // For this context, we use it, but a custom modal is recommended for production.
        return confirm('Are you sure you want to delete organisation "' + name + '"? This action cannot be undone and will not automatically delete associated users.');
    }
</script>
</body>
</html>
