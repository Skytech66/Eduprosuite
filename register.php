<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Supabase connection (must use pooler)
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

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $class = trim($_POST['class'] ?? '');

    // Basic validation
    if (empty($username) || empty($password) || empty($class)) {
        $message = "Please fill in all fields.";
    } else {
        // Check if username already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM student_account WHERE username = :username");
        $stmt->execute(['username' => $username]);
        if ($stmt->fetchColumn() > 0) {
            $message = "Username already taken.";
        } else {
            // Hash password before storing
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert new student with class
            $stmt = $conn->prepare("INSERT INTO student_account (username, password, class) VALUES (:username, :password, :class)");
            $stmt->execute([
                'username' => $username,
                'password' => $password_hash,
                'class' => $class
            ]);
            $message = "Registration successful! You can now log in.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Student Registration | EduPro Suite 2.0</title>
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet" />
    <!-- MDB -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1596495577886-d920f1fb7238') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Inter', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            color: #fff;
        }

        .register-card {
            background: rgba(0, 0, 0, 0.65);
            padding: 2.5rem;
            border-radius: 16px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 8px 24px rgba(0,0,0,0.7);
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        label {
            font-weight: 600;
            display: block;
            margin-bottom: 0.5rem;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1.25rem;
            border: none;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.15);
            color: #fff;
            font-size: 1rem;
        }

        input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        button {
            width: 100%;
            background: linear-gradient(135deg, #4e54c8, #8f94fb);
            border: none;
            padding: 0.75rem;
            border-radius: 12px;
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background: linear-gradient(135deg, #3e3e7a, #6a6ddf);
        }

        .message {
            text-align: center;
            margin-bottom: 1rem;
            font-weight: 600;
            color: #f5d97e;
        }

        .back-link {
            text-align: center;
            margin-top: 1rem;
        }

        .back-link a {
            color: #f5d97e;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-card {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-card">
        <h2>Student Registration</h2>

        <?php if ($message): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <form method="post" action="">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="e.g. john.doe21" required />

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required />

            <label for="class">Class Name</label>
            <input type="text" id="class" name="class" placeholder="e.g. Math 101" required />

            <button type="submit"><i class="fas fa-user-plus me-2"></i> Register</button>
        </form>

        <div class="back-link">
            <a href="student_login.php"><i class="fas fa-arrow-left me-1"></i> Back to Login</a>
        </div>
    </div>

    <!-- MDB -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
</body>
</html>
