 <?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Supabase connection
$host = "aws-1-eu-north-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.mqtuzltstbshtjigzujz";
$password = "Ernestbizz..123";

try {
    $conn = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // LOGIN
    if (isset($_POST['login'])) {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error = "Please enter both email and password.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM teacher_system WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            $teacher = $stmt->fetch();

            if ($teacher && password_verify($password, $teacher['password'])) {
                $_SESSION['teacher_id'] = $teacher['id'];
                $_SESSION['teacher_name'] = $teacher['name'];
                $_SESSION['assigned_class'] = $teacher['assigned_class'];

                header("Location: assignment.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        }
    }

    // REGISTRATION
    if (isset($_POST['register'])) {
        $name = trim($_POST['reg_name'] ?? '');
        $email = trim($_POST['reg_email'] ?? '');
        $class = trim($_POST['reg_class'] ?? '');
        $password = $_POST['reg_password'] ?? '';

        if (empty($name) || empty($email) || empty($class) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            // Check if email already exists
            $stmt = $conn->prepare("SELECT * FROM teacher_system WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $existing = $stmt->fetch();

            if ($existing) {
                $error = "Email already registered.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO teacher_system (email, password, name, assigned_class)
                                        VALUES (:email, :password, :name, :assigned_class)");
                $stmt->execute([
                    'email' => $email,
                    'password' => $hashed,
                    'name' => $name,
                    'assigned_class' => $class
                ]);

                // Auto-login after registration
                $teacher_id = $conn->lastInsertId();
                $_SESSION['teacher_id'] = $teacher_id;
                $_SESSION['teacher_name'] = $name;
                $_SESSION['assigned_class'] = $class;

                header("Location: assignment.php");
                exit;
            }
        }
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
    * {margin:0;padding:0;box-sizing:border-box;}
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
    .login-form { padding: 30px; }
    .error-message {
        color: var(--error);
        font-size: 0.9rem;
        text-align: center;
        margin-bottom: 15px;
    }
    .form-group { margin-bottom: 20px; }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
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
    .btn:hover { background-color: var(--primary-dark); transform: translateY(-2px); }
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
    .btn-secondary:hover { background-color: #f8f9ff; }
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
    .divider::before { margin-right: 10px; }
    .divider::after { margin-left: 10px; }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      padding-top: 100px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.4);
    }
    .modal-content {
      background-color: white;
      margin: auto;
      padding: 25px;
      border-radius: 12px;
      width: 90%;
      max-width: 400px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
      position: relative;
      animation: fadeInScale 0.3s ease;
    }
    @keyframes fadeInScale {
      from { transform: scale(0.8); opacity: 0; }
      to { transform: scale(1); opacity: 1; }
    }
    .close {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      font-weight: bold;
      color: #333;
      cursor: pointer;
    }
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
        <button type="submit" class="btn" name="login">Sign In</button>
      </form>

      <div class="divider">or</div>
      <button type="button" class="btn-secondary" id="openRegisterModal">Register</button>
    </div>
  </div>

  <!-- Registration Modal -->
  <div id="registerModal" class="modal">
    <div class="modal-content">
      <span class="close" id="closeModal">&times;</span>
      <h3>Teacher Registration</h3>
      <form method="POST" action="">
        <div class="form-group">
          <label for="reg_name">Full Name</label>
          <input id="reg_name" name="reg_name" type="text" class="form-control" placeholder="John Doe" required />
        </div>
        <div class="form-group">
          <label for="reg_email">Email Address</label>
          <input id="reg_email" name="reg_email" type="email" class="form-control" placeholder="your@email.com" required />
        </div>
        <div class="form-group">
          <label for="reg_class">Assigned Class</label>
          <input id="reg_class" name="reg_class" type="text" class="form-control" placeholder="Grade 6A" required />
        </div>
        <div class="form-group">
          <label for="reg_password">Password</label>
          <input id="reg_password" name="reg_password" type="password" class="form-control" placeholder="Enter password" required />
        </div>
        <button type="submit" class="btn" name="register">Register</button>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById('registerModal');
    const openBtn = document.getElementById('openRegisterModal');
    const closeBtn = document.getElementById('closeModal');

    openBtn.onclick = () => modal.style.display = 'block';
    closeBtn.onclick = () => modal.style.display = 'none';
    window.onclick = (e) => { if (e.target == modal) modal.style.display = 'none'; };
  </script>
</body>
</html>
