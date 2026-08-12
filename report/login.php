<?php
require_once 'login_code.php';


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <style>
    :root {
      --sidebar-bg: #1e3a8a;
      --accent: #2563eb;
      --bg: #f8fafc;
      --card-bg: #ffffff;
      --text: #0f172a;
      --muted: #64748b;
      --radius: 14px;
      --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
      font-family: 'Inter', sans-serif;
    }

    body {
      margin: 0;
      display: flex;
      height: 100vh;
      background: var(--bg);
    }

    /* Left branding */
    .left {
      flex: 1;
      background: var(--sidebar-bg);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-direction: column;
      padding: 40px;
    }

    .left h1 {
      font-size: 28px;
      margin: 0 0 10px;
    }

    .left p {
      font-size: 15px;
      color: #cbd5e1;
    }

    /* Right login card */
    .right {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .card {
      width: 100%;
      max-width: 380px;
      background: var(--card-bg);
      border-radius: var(--radius);
      box-shadow: var(--shadow);
      padding: 30px;
    }

    .card h2 {
      margin: 0 0 20px;
      font-size: 22px;
      font-weight: 600;
      text-align: center;
      color: var(--text);
    }

    .form-group {
      margin-bottom: 16px;
    }

    label {
      display: block;
      font-size: 14px;
      margin-bottom: 6px;
      color: var(--muted);
    }

    input[type="email"],
    input[type="password"] {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #e2e8f0;
      border-radius: var(--radius);
      font-size: 14px;
    }

    .form-options {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 13px;
      margin-bottom: 20px;
      color: var(--muted);
    }

    .form-options label {
      display: flex;
      align-items: center;
      gap: 6px;
      margin: 0;
      font-size: 13px;
    }

    .form-options a {
      color: var(--accent);
      text-decoration: none;
      font-weight: 500;
    }

    button {
      width: 100%;
      padding: 12px;
      background: var(--accent);
      color: white;
      border: none;
      border-radius: var(--radius);
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      margin-bottom: 14px;
    }

    .signup {
      text-align: center;
      font-size: 14px;
      color: var(--muted);
    }

    .signup a {
      color: var(--accent);
      font-weight: 600;
      text-decoration: none;
    }

    @media (max-width: 800px) {
      .left { display: none; }
      .right { flex: 1; }
    }
  </style>
</head>
<body>
  <div class="left">
	<image src="images/logo2.png">
    <h1>Welcome Back!</h1>
    <p>Sign in to access your dashboard</p>
  </div>

  <div class="right">
    <div class="card">
      <h2>Login</h2>
      <form action="" method="post">
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="you@example.com" required>
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="••••••••" required>
        </div>
       <!-- <div class="form-options">
          <label><input type="checkbox"> Remember me</label>
          <a href="#">Forgot password?</a>
        </div>-->
        <button type="submit">Login</button>
       <!-- <div class="signup">
          Don’t have an account? <a href="#">Sign Up</a>
        </div>-->
      </form>
    </div>
  </div>
</body>
</html>
