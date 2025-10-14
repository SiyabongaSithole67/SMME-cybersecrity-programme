<?php
require_once __DIR__ . "/../Controllers/LoginController.php";

session_start();

if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    header("Location: /Views/{$role}_home.php");
    exit();
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $loginController = new LoginController();
    $loginController->login($email, $password);
    $message = "Invalid login credentials";
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
      width: 320px;
    }

    input {
      display: block;
      margin-bottom: 1rem;
      padding: 0.6rem;
      width: 100%;
      border: 1px solid #ccc;
      border-radius: 4px;
      font-size: 14px;
    }

    input:focus {
      border-color: #007BFF;
      outline: none;
      box-shadow: 0 0 5px rgba(0,123,255,0.3);
    }

    button {
      width: 100%;
      padding: 0.7rem;
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 15px;
      transition: background 0.2s ease, transform 0.1s ease;
    }

    button:hover {
      background: #0056b3;
      transform: translateY(-1px);
    }

    h2 {
      text-align: center;
      margin-bottom: 1rem;
      color: #333;
    }

    p.message {
      color: red;
      text-align: center;
      margin-bottom: 1rem;
    }

    /* ✅ Fixed styling for "Need an account?" link */
    .register-link {
      text-align: center;
      margin-top: 1rem;
    }

    .register-link a {
      display: inline-block;
      background: #6c757d;
      color: white;
      text-decoration: none;
      padding: 0.6rem 1rem;
      border-radius: 4px;
      font-size: 14px;
      transition: background 0.2s ease, transform 0.1s ease;
    }

    .register-link a:hover {
      background: #5a6268;
      transform: translateY(-1px);
    }

    .register-link a:active {
      background: #444;
      transform: translateY(0);
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

      <!-- ✅ Styled "Need an account" button -->
      <p class="register-link">
        <a href="register.php">Need an account? Register here</a>
      </p>
  </form>
</body>
</html>

