 
<?php
session_start();
require 'config.php'; // Connect to Supabase via PDO

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $password) {
        try {
            // Prepare PDO statement
            $stmt = $conn->prepare("SELECT id, name, assigned_class, password FROM teacher WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($teacher && password_verify($password, $teacher['password'])) {
                $_SESSION['teacher_id'] = $teacher['id'];
                $_SESSION['teacher_name'] = $teacher['name'];
                $_SESSION['assigned_class'] = $teacher['assigned_class'];

                header("Location: assignment.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }

        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
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
    /* keep your existing CSS */
  </style>
</head>
<body>
  <div class="login-container">
    <div class="login-header">
      <h2>Assignments/Tests</h2>
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

  <!-- Loader -->
  <div class="loader-overlay" id="loader" style="display: none;">
    <img src="logo.png" alt="Loading..." class="loader-logo" />
  </div>

  <script>
    document.querySelector('form').addEventListener('submit', function () {
        document.getElementById('loader').style.display = 'flex';
    });
    window.addEventListener('pageshow', function () {
        document.getElementById('loader').style.display = 'none';
    });
  </script>
</body>
</html>
