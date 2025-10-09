<?php
require_once __DIR__ . "/../Controllers/LoginController.php";

session_start();

if (isset($_SESSION['user'])) {
    // Already logged in, redirect to home
    $role = $_SESSION['user']['role'];
    header("Location: /Views/{$role}_home.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $loginController = new LoginController();
    $loginController->login($email, $password); // redirects on success
    $message = "Invalid login credentials"; // will show only if login fails
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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
    }
    h1, h2 {
      text-align: center;
    }
    p.message {
      color: red;
      text-align: center;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>
  <form method="post">
      <h2>Login</h2>
      <?php if ($message) echo "<p class='message'>$message</p>"; ?>
      <input type="email" name="email" placeholder="Email" required>
      <input type="password" name="password" placeholder="Password" required>
      <button type="submit">Login</button>
      <p class="register-link"><a href="register.php">Need an account? Register here.</a></p> 
  </form>
</body>
</html>

