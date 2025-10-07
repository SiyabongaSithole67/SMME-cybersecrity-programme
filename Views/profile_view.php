<?php
if (!isset($user)) {
    echo "Error: user data not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f3f3f3;
      margin: 0;
      padding: 0;
    }
    nav {
      background: #007BFF;
      color: white;
      padding: 1rem;
    }
    nav a {
      color: white;
      margin-right: 1rem;
      text-decoration: none;
    }
    .profile-container {
      background: white;
      margin: 3rem auto;
      padding: 2rem;
      width: 400px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    h2 {
      text-align: center;
    }
    p {
      line-height: 1.6;
    }
  </style>
</head>
<body>
  <nav>
    <a href="/Views/employee_home.php">Home</a>
    <a href="/Controllers/ProfileController.php">Profile</a>
    <a href="/logout.php">Logout</a>
  </nav>

  <div class="profile-container">
    <h2>Employee Profile</h2>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>
    <p><strong>Organisation ID:</strong> <?php echo htmlspecialchars($user['organisation_id']); ?></p>
  </div>
</body>
</html>