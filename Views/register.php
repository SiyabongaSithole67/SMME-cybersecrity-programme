<?php
// We only need the controller to handle the form submission on POST
// On GET, this script just serves the form
require_once __DIR__ . "/../Controllers/RegistrationController.php"; 

$message = '';
if (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
} elseif (isset($_GET['registration']) && $_GET['registration'] === 'success') {
    $message = "Registration successful! Your account is pending admin approval.";
}

// Pre-fill fields if registration failed
$name_value = htmlspecialchars($_GET['name'] ?? '');
$email_value = htmlspecialchars($_GET['email'] ?? '');
$org_value = htmlspecialchars($_GET['org'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f3f3f3;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    form {
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      width: 300px;
    }
    input {
      display: block;
      margin-bottom: 1rem;
      padding: 0.5rem;
      width: 100%;
    }
    button {
      width: 100%;
      padding: 0.7rem;
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-bottom: 1rem;
    }
    h2 {
      text-align: center;
    }
    p.message {
      color: <?php echo isset($_GET['error']) ? 'red' : 'green'; ?>;
      text-align: center;
      margin-bottom: 1rem;
      font-weight: bold;
    }
    a {
        display: block;
        text-align: center;
        color: #007BFF;
        text-decoration: none;
    }
  </style>
</head>
<body>
  <form method="post" action="/Controllers/RegistrationController.php">
      <h2>User Registration</h2>
      <?php if ($message) echo "<p class='message'>$message</p>"; ?>
      
      <input type="text" name="name" placeholder="Name" value="<?php echo $name_value; ?>" required>
      <input type="email" name="email" placeholder="Email" value="<?php echo $email_value; ?>" required>
      <input type="password" name="password" placeholder="Password (min 6 chars)" minlength="6" required>
      <input type="text" name="organisation" placeholder="Organisation Name" value="<?php echo $org_value; ?>" required>
      
      <input type="hidden" name="action" value="register"> 
      
      <button type="submit">Register Account</button>
      <a href="index.php">Back to Login</a>
  </form>
</body>
</html>