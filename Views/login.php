<!-- Views/login.php -->
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Employee Login</title>
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
    }
    input {
      display: block;
      margin-bottom: 1rem;
      padding: .5rem;
      width: 100%;
    }
    button {
      width: 100%;
      padding: .7rem;
      background: #007BFF;
      color: white;
      border: none;
      border-radius: 4px;
    }
  </style>
</head>
<body>
  <form method="POST" action="/Controllers/LoginController.php">
    <h2>Employee Login</h2>
    <input type="text" name="email" placeholder="Email" required />
    <input type="password" name="password" placeholder="Password" required />
    <button type="submit">Login</button>
  </form>
</body>
</html>