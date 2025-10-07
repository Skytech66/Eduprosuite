<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Supabase (PostgreSQL) connection
$host = "aws-1-eu-north-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.mqtuzltstbshtjigzujz";
$password = "Ernestbizz..123";

$toastMessage = "";

try {
  $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
  ]);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $passwordInput = trim($_POST['password']);

    if (!empty($username) && !empty($passwordInput)) {
      // Select student record
      $stmt = $pdo->prepare("SELECT * FROM student_account WHERE username = :username LIMIT 1");
      $stmt->execute([':username' => $username]);
      $student = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($student && password_verify($passwordInput, $student['password'])) {
        $_SESSION['student_username'] = $student['username'];
        $_SESSION['student_class'] = $student['class'];

        echo "<script>
          localStorage.setItem('toastMsg', 'Login successful! Redirecting...');
          window.location.href = 'class.php';
        </script>";
        exit;
      } else {
        $toastMessage = "Invalid username or password.";
      }
    } else {
      $toastMessage = "Please fill in all fields.";
    }
  }
} catch (PDOException $e) {
  $toastMessage = "Database connection failed.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Student Login | EduPro Suite 2.0</title>

  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />
  <!-- MDB -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />

  <style>
    body {
      margin: 0;
      height: 100vh;
      font-family: 'Inter', sans-serif;
      background: url('https://images.unsplash.com/photo-1596495577886-d920f1fb7238')
        no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 1rem;
      position: relative;
    }
    body::before {
      content: "";
      position: fixed;
      top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      z-index: 0;
    }
    .login-card {
      position: relative;
      z-index: 1;
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(15px);
      padding: 2.5rem;
      border-radius: 16px;
      max-width: 400px;
      width: 100%;
      color: #fff;
      border: 1px solid rgba(255, 255, 255, 0.3);
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }
    .login-card h2 {
      font-weight: 700;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    .form-label {
      font-weight: 600;
      color: #f1f1f1;
    }
    .form-control {
      background-color: rgba(0, 0, 0, 0.5);
      border: 1px solid rgba(255, 255, 255, 0.3);
      color: #fff;
    }
    .form-control::placeholder {
      color: rgba(255, 255, 255, 0.7);
    }
    .btn-primary {
      background: linear-gradient(135deg, #4e54c8, #8f94fb);
      border: none;
      width: 100%;
      font-weight: 600;
      padding: 0.75rem;
      transition: background 0.3s ease;
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #3e3e7a, #6a6ddf);
    }
    .extra-links {
      text-align: center;
      margin-top: 1rem;
    }
    .extra-links a {
      color: #f5d97e;
      font-size: 0.875rem;
      text-decoration: none;
      display: inline-block;
      width: 100%;
      padding: 0.5rem 0;
      border-radius: 8px;
      border: 1px solid transparent;
      transition: all 0.3s ease;
    }
    .extra-links a.btn-outline-light {
      border-color: #f5d97e;
      color: #f5d97e;
      font-weight: 600;
      margin-bottom: 1rem;
    }
    .extra-links a.btn-outline-light:hover {
      background-color: #f5d97e;
      color: #222;
      text-decoration: none;
    }
    .logo {
      display: block;
      margin: 0 auto 1rem;
      width: 70px;
    }
  </style>
</head>
<body>
  <div class="login-card">
    <img src="logo.png" alt="EduPro Logo" class="logo" />
    <h2>Student Login</h2>
    <form method="POST">
      <div class="mb-4">
        <label for="username" class="form-label">Username</label>
        <input type="text" name="username" id="username" class="form-control" required placeholder="e.g. john.doe21" />
      </div>
      <div class="mb-4">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" id="password" class="form-control" required placeholder="Enter password" />
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-sign-in-alt me-2"></i> Login
      </button>
    </form>
    <div class="extra-links mt-4">
      <a href="register.php" class="btn btn-outline-light w-100 mb-3">
        <i class="fas fa-user-plus me-2"></i> Register
      </a>
      <a href="index.php"><i class="fas fa-arrow-left"></i> Back to Main Portal</a>
    </div>
  </div>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
  <script>
    const msg = <?php echo json_encode($toastMessage); ?>;
    if (msg) {
      const toast = document.createElement('div');
      toast.className = 'toast align-items-center text-white bg-dark border-0 show position-fixed bottom-0 end-0 m-3';
      toast.innerHTML = `
        <div class="d-flex">
          <div class="toast-body">${msg}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-mdb-dismiss="toast"></button>
        </div>`;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 4000);
    }
  </script>
</body>
</html>
