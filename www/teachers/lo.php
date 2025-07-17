<?php
session_start();
require 'config.php'; // Database connection

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        $stmt = $conn->prepare("SELECT id, name, assigned_class, password FROM teacher WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $teacher = $result->fetch_assoc();

        if ($teacher && password_verify($password, $teacher['password'])) {
            $_SESSION['teacher_id'] = $teacher['id'];
            $_SESSION['teacher_name'] = $teacher['name'];
            $_SESSION['assigned_class'] = $teacher['assigned_class'];
            header("Location:ratings.php");
            exit;
        } else {
            $error = "Invalid email or password.";
        }

        $stmt->close();
    } else {
        $error = "Please enter both email and password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Teacher Login | EduTrack</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <style>
    :root {
        --primary: #4361ee;
        --primary-dark: #3a56d4;
        --secondary: #3f37c9;
        --dark: #1a1a2e;
        --light: #f8f9fa;
        --gray: #6c757d;
        --success: #4cc9f0;
        --error: #f72585;
    }
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f5f7ff;
        color: var(--dark);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        background-image:
            radial-gradient(at 80% 0%, hsla(189, 100%, 56%, 0.1) 0px, transparent 50%),
            radial-gradient(at 0% 50%, hsla(355, 100%, 93%, 0.1) 0px, transparent 50%);
    }
    .login-container {
        width: 100%;
        max-width: 420px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .login-header {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        padding: 30px;
        text-align: center;
    }
    .login-header h2 {
        font-weight: 600;
        margin-bottom: 8px;
        font-size: 1.5rem;
    }
    .login-header p {
        opacity: 0.9;
        font-size: 0.9rem;
    }
    .login-form {
        padding: 30px;
    }
    .error-message {
        color: var(--error);
        font-size: 0.9rem;
        margin-top: 5px;
        text-align: center;
        margin-bottom: 15px;
    }
    .form-group {
        margin-bottom: 20px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: var(--dark);
        font-size: 0.9rem;
    }
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-size: 0.95rem;
        transition: all 0.3s;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    .btn {
        display: inline-block;
        width: 100%;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
        background-color: var(--primary);
        color: white;
    }
    .btn:hover {
        background-color: var(--primary-dark);
        transform: translateY(-2px);
    }
    .btn-secondary {
        background-color: white;
        color: var(--primary);
        border: 1px solid var(--primary);
        margin-top: 10px;
        cursor: pointer;
        font-weight: 600;
        padding: 12px;
        border-radius: 8px;
        width: 100%;
        transition: all 0.3s;
        text-align: center;
    }
    .btn-secondary:hover {
        background-color: #f8f9ff;
    }
    .divider {
        display: flex;
        align-items: center;
        margin: 20px 0;
        color: var(--gray);
        font-size: 0.8rem;
    }
    .divider::before, .divider::after {
        content: "";
        flex: 1;
        border-bottom: 1px solid #e0e0e0;
    }
    .divider::before {
        margin-right: 10px;
    }
    .divider::after {
        margin-left: 10px;
    }
    .login-footer {
        text-align: center;
        margin-top: 20px;
        font-size: 0.85rem;
        color: var(--gray);
    }
    .login-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }
    @media (max-width: 480px) {
        .login-container {
            border-radius: 12px;
        }
        .login-header, .login-form {
            padding: 25px;
        }
    }

    /* Loader styles */
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .loader-logo {
        width: 100px;
        height: 100px;
        opacity: 0;
        animation: logoFadeInSpin 1.8s ease-out forwards;
    }

    @keyframes logoFadeInSpin {
        0% {
            transform: scale(2) rotate(0deg);
            opacity: 0;
        }
        50% {
            opacity: 1;
        }
        100% {
            transform: scale(1) rotate(360deg);
            opacity: 1;
        }
    }
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <h2>Assigments/Tests</h2>
      <p>Sign in to access your teacher dashboard</p>
    </div>
    <div class="login-form">
      <?php if ($error): ?>
        <div class="error-message"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" class="form-control" placeholder="your@email.com" required />
        </div>
        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" class="form-control" placeholder="Enter your password" required />
        </div>
        <button type="submit" class="btn">Sign In</button>
      </form>
      <div class="divider">or</div>
      <div class="login-footer">
        <a href="#">Forgot password?</a>
      </div>
    </div>
  </div>

  <!-- Loader HTML -->
  <div class="loader-overlay" id="loader" style="display: none;">
    <img src="logo.png" alt="Loading..." class="loader-logo" />
  </div>

  <!-- Loader Scripts -->
  <script>
    // Show loader when form is submitted
    document.querySelector('form').addEventListener('submit', function () {
        document.getElementById('loader').style.display = 'flex';
    });

    // Ensure loader is hidden when navigating back
    window.addEventListener('pageshow', function () {
        document.getElementById('loader').style.display = 'none';
    });
  </script>
</body>
</html>
