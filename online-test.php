<?php
session_start(); 
error_reporting(E_ALL); 
ini_set('display_errors', 1);  

// DB Connection
$host = "aws-1-eu-north-1.pooler.supabase.com"; 
$port = "6543"; 
$dbname = "postgres"; 
$user = "postgres.mqtuzltstbshtjigzujz"; 
$password = "Ernestbizz..123";  

try {     
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);     
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    
    // Create test_results table if it doesn't exist
    $createTableSQL = "
        CREATE TABLE IF NOT EXISTS test_results (
            id SERIAL PRIMARY KEY,
            test_id INTEGER NOT NULL,
            username VARCHAR(255) NOT NULL,
            score INTEGER NOT NULL,
            total_marks INTEGER NOT NULL,
            percentage INTEGER NOT NULL,
            submitted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(test_id, username)
        );
        
        CREATE INDEX IF NOT EXISTS idx_test_results_username ON test_results(username);
        CREATE INDEX IF NOT EXISTS idx_test_results_test_id ON test_results(test_id);
        CREATE INDEX IF NOT EXISTS idx_test_results_submitted_at ON test_results(submitted_at);
    ";
    
    $pdo->exec($createTableSQL);
    
} catch (PDOException $e) {     
    die("DB Connection failed: " . $e->getMessage()); 
}

// Handle score submission to database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'submit_score') {
        header('Content-Type: application/json');
        
        // Validate required fields
        if (!isset($_POST['test_id']) || !isset($_POST['score']) || !isset($_POST['total_marks'])) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
            exit();
        }
        
        $test_id = $_POST['test_id'];
        $username = $_SESSION['student_username'];
        $score = intval($_POST['score']);
        $total_marks = intval($_POST['total_marks']);
        $percentage = round(($score / $total_marks) * 100);
        
        try {
            // Check if record already exists
            $check_stmt = $pdo->prepare("SELECT id FROM test_results WHERE test_id = :test_id AND username = :username");
            $check_stmt->execute(['test_id' => $test_id, 'username' => $username]);
            $existing_record = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_record) {
                // Update existing record
                $update_stmt = $pdo->prepare("
                    UPDATE test_results 
                    SET score = :score, total_marks = :total_marks, percentage = :percentage, submitted_at = NOW() 
                    WHERE test_id = :test_id AND username = :username
                ");
                $update_stmt->execute([
                    'score' => $score,
                    'total_marks' => $total_marks,
                    'percentage' => $percentage,
                    'test_id' => $test_id,
                    'username' => $username
                ]);
                $message = "Score updated successfully!";
            } else {
                // Insert new record
                $insert_stmt = $pdo->prepare("
                    INSERT INTO test_results (test_id, username, score, total_marks, percentage, submitted_at) 
                    VALUES (:test_id, :username, :score, :total_marks, :percentage, NOW())
                ");
                $insert_stmt->execute([
                    'test_id' => $test_id,
                    'username' => $username,
                    'score' => $score,
                    'total_marks' => $total_marks,
                    'percentage' => $percentage
                ]);
                $message = "Score saved successfully!";
            }
            
            echo json_encode(['success' => true, 'message' => $message, 'percentage' => $percentage]);
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        exit();
    }
    
    // Handle history data request
    if ($_POST['action'] === 'get_history') {
        header('Content-Type: application/json');
        $username = $_SESSION['student_username'];
        
        try {
            $stmt = $pdo->prepare("
                SELECT tr.*, t.title as test_title 
                FROM test_results tr 
                JOIN tests t ON tr.test_id = t.id 
                WHERE tr.username = :username 
                ORDER BY tr.submitted_at DESC
            ");
            $stmt->execute(['username' => $username]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode(['success' => true, 'data' => $history]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Error fetching history']);
        }
        exit();
    }
}

// Check login
if (!isset($_SESSION['student_username'])) {     
    header("Location: login.php");     
    exit(); 
}  

$student_username = $_SESSION['student_username'];  

// Update last_active timestamp for current student
$update_stmt = $pdo->prepare("UPDATE student_account SET last_active = NOW() WHERE username = :username");
$update_stmt->execute(['username' => $student_username]);

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM student_account WHERE username = :username"); 
$stmt->execute(['username' => $student_username]); 
$student = $stmt->fetch(PDO::FETCH_ASSOC);  

// Force class to Basic Six
$class = "Basic Six";  

// Fetch tests for Basic Six (DISTINCT ON title to avoid duplicates)
$stmt = $pdo->prepare("
    SELECT DISTINCT ON (title) id, title, duration, total_marks
    FROM tests
    WHERE subject = :class
    ORDER BY title, created_at ASC
"); 
$stmt->execute(['class' => $class]); 
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);  

// Fetch students online (active in last 5 minutes)
$online_stmt = $pdo->prepare("
    SELECT username, last_active 
    FROM student_account 
    WHERE last_active >= NOW() - INTERVAL '5 minutes' 
    ORDER BY last_active DESC
");
$online_stmt->execute();
$online_students = $online_stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle AJAX request for questions
if (isset($_GET['fetch_questions']) && $_GET['fetch_questions'] == 1) {
    $test_id = $_GET['test_id'] ?? null; 
    if (!$test_id) { 
        echo "Test ID missing."; 
        exit(); 
    }  

    $stmt = $pdo->prepare("
        SELECT id, question_text, option_a, option_b, option_c, option_d, correct_option
        FROM questions
        WHERE test_id = :test_id
        ORDER BY id ASC
    "); 
    $stmt->execute(['test_id' => $test_id]); 
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);  

    if (!$questions) {
        echo "No questions found for this test.";
        exit();
    }  

    foreach ($questions as $q) {
        echo '<div class="question" data-correct="'.htmlspecialchars($q['correct_option']).'">';
        echo '<p><strong>'.htmlspecialchars($q['question_text']).'</strong></p>';
        echo '<div class="options">';
        if ($q['option_a']) echo '<label><input type="radio" name="q_'.$q['id'].'" value="A"> '.htmlspecialchars($q['option_a']).'</label>';
        if ($q['option_b']) echo '<label><input type="radio" name="q_'.$q['id'].'" value="B"> '.htmlspecialchars($q['option_b']).'</label>';
        if ($q['option_c']) echo '<label><input type="radio" name="q_'.$q['id'].'" value="C"> '.htmlspecialchars($q['option_c']).'</label>';
        if ($q['option_d']) echo '<label><input type="radio" name="q_'.$q['id'].'" value="D"> '.htmlspecialchars($q['option_d']).'</label>';
        echo '</div></div>';
    } 
    exit(); 
} 

// Handle AJAX request for online students
if (isset($_GET['fetch_online_students']) && $_GET['fetch_online_students'] == 1) {
    $online_stmt = $pdo->prepare("
        SELECT username, last_active 
        FROM student_account 
        WHERE last_active >= NOW() - INTERVAL '5 minutes' 
        ORDER BY last_active DESC
    ");
    $online_stmt->execute();
    $online_students = $online_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($online_students)) {
        echo '<div class="empty-online-state">No students online at the moment</div>';
    } else {
        foreach ($online_students as $student) {
            $time_ago = time_elapsed_string($student['last_active']);
            echo '<div class="online-student">';
            echo '<div class="student-avatar">'.strtoupper(substr($student['username'], 0, 1)).'</div>';
            echo '<div class="student-info">';
            echo '<div class="student-name">'.htmlspecialchars($student['username']).'</div>';
            echo '<div class="last-active">Active '.$time_ago.'</div>';
            echo '</div>';
            echo '<div class="online-indicator"></div>';
            echo '</div>';
        }
    }
    exit();
}

// Safe time difference function for PHP 8.2+
function time_elapsed_string($datetime, $full = false) {
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    // Calculate weeks safely
    $days  = $diff->d;
    $weeks = floor($days / 7);
    $days  = $days % 7;

    $units = [
        'y' => ['value' => $diff->y, 'label' => 'year'],
        'm' => ['value' => $diff->m, 'label' => 'month'],
        'w' => ['value' => $weeks, 'label' => 'week'],
        'd' => ['value' => $days, 'label' => 'day'],
        'h' => ['value' => $diff->h, 'label' => 'hour'],
        'i' => ['value' => $diff->i, 'label' => 'minute'],
        's' => ['value' => $diff->s, 'label' => 'second'],
    ];

    $string = [];
    foreach ($units as $u) {
        if ($u['value']) {
            $string[] = $u['value'].' '.$u['label'].($u['value'] > 1 ? 's' : '');
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduProsuite 2.0 | Advanced Testing Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #8b5cf6;
            --secondary: #06b6d4;
            --accent: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --warning: #f59e0b;
            --dark: #1e293b;
            --darker: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --border-radius: 16px;
            --border-radius-sm: 12px;
            --shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 35px 60px -15px rgba(0, 0, 0, 0.2);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --glass: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.95);
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
            --gradient-secondary: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0fdf4 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 80%, rgba(99, 102, 241, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(6, 182, 212, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(139, 92, 246, 0.05) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 0;
            position: relative;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 24px;
            color: var(--dark);
            text-decoration: none;
        }

        .logo img {
            height: 40px;
            width: auto;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .nav-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-sm);
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .nav-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
            background: white;
        }

        .nav-btn i {
            color: var(--primary);
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            padding: 10px 20px;
            border-radius: 50px;
            border: 1px solid var(--glass-border);
            box-shadow: var(--shadow);
            font-weight: 500;
        }

        .user-info i {
            color: var(--primary);
            font-size: 18px;
        }

        .online-section {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--glass-border);
            min-width: 280px;
        }

        .online-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .online-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .online-count {
            background: var(--gradient-primary);
            color: white;
            border-radius: 20px;
            padding: 4px 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .refresh-btn {
            background: none;
            border: none;
            color: var(--gray);
            cursor: pointer;
            font-size: 13px;
            transition: var(--transition);
            padding: 6px;
            border-radius: 6px;
            background: rgba(99, 102, 241, 0.1);
        }

        .refresh-btn:hover {
            color: var(--primary);
            background: rgba(99, 102, 241, 0.2);
            transform: rotate(180deg);
        }

        .online-students-container {
            max-height: 280px;
            overflow-y: auto;
        }

        .online-student {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .online-student:hover {
            transform: translateX(5px);
        }

        .online-student:last-child {
            border-bottom: none;
        }

        .student-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: var(--gradient-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 12px;
            font-size: 14px;
            box-shadow: var(--shadow);
        }

        .student-info {
            flex: 1;
        }

        .student-name {
            font-weight: 500;
            font-size: 14px;
            color: var(--dark);
        }

        .last-active {
            font-size: 12px;
            color: var(--gray);
        }

        .online-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--success);
            margin-left: auto;
            box-shadow: 0 0 10px var(--success);
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        .empty-online-state {
            text-align: center;
            padding: 30px 20px;
            color: var(--gray);
            font-size: 14px;
        }

        .welcome-section {
            text-align: center;
            margin-bottom: 50px;
            padding: 50px 0;
            position: relative;
        }

        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-section h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 48px;
            font-weight: 700;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
            position: relative;
        }

        .welcome-section p {
            font-size: 18px;
            color: var(--gray);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.8;
        }

        .tests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .test-card {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
        }

        .test-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--gradient-primary);
        }

        .test-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-xl);
            border-color: rgba(99, 102, 241, 0.3);
        }

        .test-header {
            padding: 25px;
            position: relative;
        }

        .test-header h2 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
            line-height: 1.4;
        }

        .test-header .test-icon {
            position: absolute;
            top: 25px;
            right: 25px;
            font-size: 28px;
            color: var(--primary);
            opacity: 0.8;
        }

        .test-body {
            padding: 0 25px 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .test-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .detail-item i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 10px;
        }

        .detail-label {
            font-size: 13px;
            color: var(--gray);
            margin-bottom: 5px;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 700;
            font-size: 18px;
            color: var(--dark);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px 28px;
            border: none;
            border-radius: var(--border-radius-sm);
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.7s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.4);
        }

        .btn-secondary {
            background: var(--gradient-secondary);
            color: white;
            box-shadow: 0 8px 25px rgba(6, 182, 212, 0.3);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(6, 182, 212, 0.4);
        }

        .btn-outline {
            background: transparent;
            color: var(--primary);
            border: 2px solid var(--primary);
        }

        .btn-outline:hover {
            background: rgba(99, 102, 241, 0.1);
            transform: translateY(-2px);
        }

        .timer-container {
            background: rgba(99, 102, 241, 0.08);
            border-radius: var(--border-radius);
            padding: 20px;
            margin: 20px 0;
            text-align: center;
            display: none;
            border: 1px solid rgba(99, 102, 241, 0.15);
            backdrop-filter: blur(10px);
        }

        .timer {
            font-size: 36px;
            font-weight: 700;
            color: var(--dark);
            font-family: 'Poppins', sans-serif;
            letter-spacing: 2px;
        }

        .timer-warning {
            color: var(--warning);
        }

        .timer-critical {
            color: var(--danger);
            animation: pulse 1s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .questions-container {
            margin: 25px 0;
            display: none;
        }

        .question {
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--glass-border);
            transition: var(--transition);
        }

        .question:hover {
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateY(-2px);
        }

        .question p {
            font-weight: 500;
            margin-bottom: 20px;
            font-size: 16px;
            color: var(--dark);
            line-height: 1.6;
        }

        .options {
            display: grid;
            gap: 12px;
        }

        .options label {
            display: flex;
            align-items: center;
            padding: 16px 20px;
            border-radius: var(--border-radius-sm);
            background: rgba(255, 255, 255, 0.6);
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .options label:hover {
            background: white;
            border-color: rgba(99, 102, 241, 0.3);
            transform: translateX(5px);
        }

        .options input {
            margin-right: 15px;
            transform: scale(1.2);
            accent-color: var(--primary);
        }

        .test-actions {
            display: none;
            justify-content: center;
            gap: 20px;
            margin-top: 25px;
        }

        .score-container {
            text-align: center;
            margin: 40px 0;
            display: none;
        }

        .score-display {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .score-circle {
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: conic-gradient(var(--primary) 0% var(--p), #f1f5f9 var(--p) 100%);
            color: var(--dark);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            font-size: 42px;
            font-family: 'Poppins', sans-serif;
            font-weight: bold;
            margin: 0 auto 20px;
            box-shadow: var(--shadow-xl);
            position: relative;
            border: 8px solid #f1f5f9;
        }

        .score-circle::before {
            content: '';
            position: absolute;
            width: 170px;
            height: 170px;
            border-radius: 50%;
            background: white;
        }

        .score-text {
            position: relative;
            z-index: 1;
        }

        .score-label {
            font-size: 18px;
            margin-top: 10px;
            color: var(--gray);
            font-weight: 500;
        }

        .performance-message {
            font-size: 18px;
            margin-top: 15px;
            padding: 12px 30px;
            border-radius: 50px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            display: inline-block;
            font-weight: 600;
        }

        .correct-answer {
            color: var(--success);
            font-weight: 600;
        }

        .wrong-answer {
            color: var(--danger);
        }

        .mark {
            margin-left: 12px;
            font-size: 18px;
        }

        .disabled input {
            pointer-events: none;
        }

        .disabled label {
            opacity: 0.8;
        }

        #toast {
            visibility: hidden;
            min-width: 350px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            color: var(--dark);
            text-align: center;
            border-radius: var(--border-radius);
            padding: 20px;
            position: fixed;
            z-index: 1000;
            left: 50%;
            bottom: 30px;
            transform: translateX(-50%);
            font-size: 15px;
            opacity: 0;
            transition: opacity 0.5s ease, bottom 0.5s ease;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--glass-border);
        }

        #toast.show {
            visibility: visible;
            opacity: 1;
            bottom: 50px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: var(--glass);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            border: 1px solid var(--glass-border);
        }

        .empty-state i {
            font-size: 80px;
            color: var(--gray);
            margin-bottom: 25px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 28px;
            color: var(--gray);
            margin-bottom: 15px;
        }

        .empty-state p {
            color: var(--gray);
            max-width: 500px;
            margin: 0 auto;
            font-size: 17px;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(10px);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: var(--glass);
            backdrop-filter: blur(30px);
            border-radius: var(--border-radius);
            padding: 40px;
            max-width: 500px;
            width: 90%;
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--glass-border);
            text-align: center;
        }

        .modal h3 {
            font-size: 24px;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .modal p {
            color: var(--gray);
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .history-modal {
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .history-table th,
        .history-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .history-table th {
            background: rgba(99, 102, 241, 0.1);
            font-weight: 600;
            color: var(--dark);
        }

        .history-table tr:hover {
            background: rgba(99, 102, 241, 0.05);
        }

        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .test-in-progress {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            z-index: 3000;
            display: none;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
        }

        .test-in-progress-content {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(30px);
            border-radius: var(--border-radius);
            padding: 40px;
            max-width: 400px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        @media (max-width: 1024px) {
            .tests-grid {
                grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            }
            
            .user-section {
                flex-direction: column;
                align-items: flex-end;
                gap: 15px;
            }
            
            .online-section {
                min-width: 250px;
            }
        }

        @media (max-width: 768px) {
            .tests-grid {
                grid-template-columns: 1fr;
            }
            
            .header {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }
            
            .user-section {
                align-items: center;
                width: 100%;
            }
            
            .test-actions {
                flex-direction: column;
            }
            
            .test-details {
                flex-direction: column;
                gap: 20px;
            }
            
            .welcome-section h1 {
                font-size: 36px;
            }
            
            .nav-actions {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Test in Progress Warning -->
    <div class="test-in-progress" id="testInProgress">
        <div class="test-in-progress-content">
            <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px; color: #f59e0b;"></i>
            <h3 style="margin-bottom: 15px; color: white;">Test in Progress</h3>
            <p style="color: rgba(255,255,255,0.8); line-height: 1.6;">
                You cannot navigate away from this page while the test is in progress. 
                If you leave, you won't be able to continue the test.
            </p>
            <button class="btn btn-primary" onclick="hideTestWarning()" style="margin-top: 20px;">
                <i class="fas fa-arrow-left"></i> Return to Test
            </button>
        </div>
    </div>

    <!-- History Modal -->
    <div class="modal" id="historyModal">
        <div class="modal-content history-modal">
            <h3><i class="fas fa-history"></i> Test History</h3>
            <div id="historyContent">
                <div style="text-align: center; padding: 40px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: var(--primary);"></i>
                    <p style="margin-top: 15px; color: var(--gray);">Loading your test history...</p>
                </div>
            </div>
            <div class="modal-actions" style="margin-top: 25px;">
                <button class="btn btn-outline" onclick="closeModal('historyModal')">
                    <i class="fas fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <!-- Navigation Warning Modal -->
    <div class="modal" id="navigationWarning">
        <div class="modal-content">
            <i class="fas fa-exclamation-circle" style="font-size: 48px; color: var(--warning); margin-bottom: 20px;"></i>
            <h3>Navigation Warning</h3>
            <p>You have a test in progress. Navigating away will submit your current answers and end the test. Are you sure you want to continue?</p>
            <div class="modal-actions">
                <button class="btn btn-danger" onclick="forceNavigate()">
                    <i class="fas fa-external-link-alt"></i> Leave Anyway
                </button>
                <button class="btn btn-outline" onclick="closeModal('navigationWarning')">
                    <i class="fas fa-times"></i> Stay on Page
                </button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header">
            <a href="dashboard.php" class="logo">
                <img src="logo.png" alt="EduSphere Logo">
                <span>EduProsuite 2.0</span>
            </a>
            
            <div class="nav-actions">
                <a href="class.php" class="nav-btn">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
                <button class="nav-btn" onclick="showHistory()">
                    <i class="fas fa-history"></i>
                    View History
                </button>
                <a href="logout.php" class="nav-btn" style="color: var(--danger);">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>

            <div class="user-section">
                <div class="online-section">
                    <div class="online-header">
                        <div class="online-title">
                            <i class="fas fa-user-clock"></i>
                            Active Students <span class="online-count"><?php echo count($online_students); ?></span>
                        </div>
                        <button class="refresh-btn" id="refresh-online">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                    <div class="online-students-container" id="online-students-container">
                        <?php if(empty($online_students)): ?>
                            <div class="empty-online-state">No students online at the moment</div>
                        <?php else: ?>
                            <?php foreach($online_students as $student): ?>
                                <?php 
                                    $time_ago = time_elapsed_string($student['last_active']);
                                ?>
                                <div class="online-student">
                                    <div class="student-avatar"><?php echo strtoupper(substr($student['username'], 0, 1)); ?></div>
                                    <div class="student-info">
                                        <div class="student-name"><?php echo htmlspecialchars($student['username']); ?></div>
                                        <div class="last-active">Active <?php echo $time_ago; ?></div>
                                    </div>
                                    <div class="online-indicator"></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($student_username); ?></span>
                </div>
            </div>
        </div>

        <div class="welcome-section">
            <h1>Welcome to EduProsuite 2.0</h1>
            <p>Your advanced testing platform with real-time analytics, professional assessment tools, and comprehensive performance tracking.</p>
        </div>

        <?php if(empty($tests)): ?>
            <div class="empty-state">
                <i class="fas fa-file-alt"></i>
                <h3>No Tests Available</h3>
                <p>There are currently no tests available for your class. Please check back later or contact your instructor.</p>
            </div>
        <?php else: ?>
            <div class="tests-grid">
                <?php foreach($tests as $test): ?>
                    <div class="test-card" data-test-id="<?php echo $test['id']; ?>" data-duration="<?php echo $test['duration']; ?>">
                        <div class="test-header">
                            <h2><?php echo htmlspecialchars($test['title']); ?></h2>
                            <i class="fas fa-file-alt test-icon"></i>
                        </div>
                        <div class="test-body">
                            <div class="test-details">
                                <div class="detail-item">
                                    <i class="fas fa-stopwatch"></i>
                                    <div class="detail-label">Duration</div>
                                    <div class="detail-value"><?php echo $test['duration']; ?> min</div>
                                </div>
                                <div class="detail-item">
                                    <i class="fas fa-star"></i>
                                    <div class="detail-label">Total Marks</div>
                                    <div class="detail-value"><?php echo $test['total_marks']; ?></div>
                                </div>
                            </div>
                            
                            <button class="btn btn-primary start-test">
                                <i class="fas fa-play-circle"></i> Start Test
                            </button>
                            
                            <div class="timer-container">
                                <div class="timer"></div>
                            </div>
                            
                            <div class="questions-container"></div>
                            
                            <div class="test-actions">
                                <button class="btn btn-primary submit-test">
                                    <i class="fas fa-paper-plane"></i> Submit Test
                                </button>
                                <button class="btn btn-outline cancel-test">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                            
                            <div class="score-container"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="toast"></div>

    <script>
        let testInProgress = false;
        let currentTestCard = null;

        function showToast(msg, type = 'info') {
            const toast = document.getElementById('toast');
            toast.textContent = msg;
            
            // Add type-based styling
            if (type === 'success') {
                toast.style.background = 'linear-gradient(135deg, var(--success) 0%, #059669 100%)';
                toast.style.color = 'white';
            } else if (type === 'error') {
                toast.style.background = 'linear-gradient(135deg, var(--danger) 0%, #dc2626 100%)';
                toast.style.color = 'white';
            } else if (type === 'warning') {
                toast.style.background = 'linear-gradient(135deg, var(--warning) 0%, #d97706 100%)';
                toast.style.color = 'white';
            } else {
                toast.style.background = 'var(--glass)';
                toast.style.color = 'var(--dark)';
            }
            
            toast.className = "show";
            setTimeout(() => { 
                toast.className = toast.className.replace("show", ""); 
            }, 4000);
        }

        function updateScoreCircle(circle, percentage) {
            circle.style.setProperty('--p', `${percentage}%`);
        }

        function updateOnlineStudents() {
            fetch('online-test.php?fetch_online_students=1')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('online-students-container').innerHTML = html;
                    // Update the online count
                    const studentCount = document.querySelectorAll('.online-student').length;
                    const emptyState = document.querySelector('.empty-online-state');
                    const countElement = document.querySelector('.online-count');
                    
                    if (emptyState) {
                        countElement.textContent = '0';
                    } else {
                        countElement.textContent = studentCount;
                    }
                })
                .catch(error => {
                    console.error('Error updating online students:', error);
                });
        }

        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showHistory() {
            showModal('historyModal');
            
            // Fetch history data
            const formData = new FormData();
            formData.append('action', 'get_history');
            
            fetch('online-test.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const historyContent = document.getElementById('historyContent');
                
                if (data.success && data.data.length > 0) {
                    let html = `
                        <table class="history-table">
                            <thead>
                                <tr>
                                    <th>Test</th>
                                    <th>Score</th>
                                    <th>Percentage</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    
                    data.data.forEach(result => {
                        const submitted = new Date(result.submitted_at).toLocaleDateString() + ' ' + 
                                        new Date(result.submitted_at).toLocaleTimeString();
                        
                        html += `
                            <tr>
                                <td>${result.test_title}</td>
                                <td><strong>${result.score}/${result.total_marks}</strong></td>
                                <td>
                                    <span style="color: ${result.percentage >= 70 ? 'var(--success)' : result.percentage >= 50 ? 'var(--warning)' : 'var(--danger)'}">
                                        ${result.percentage}%
                                    </span>
                                </td>
                                <td>${submitted}</td>
                            </tr>
                        `;
                    });
                    
                    html += `</tbody></table>`;
                    historyContent.innerHTML = html;
                } else {
                    historyContent.innerHTML = `
                        <div style="text-align: center; padding: 40px;">
                            <i class="fas fa-inbox" style="font-size: 48px; color: var(--gray); margin-bottom: 20px; opacity: 0.5;"></i>
                            <p style="color: var(--gray);">No test history found. Complete a test to see your results here.</p>
                        </div>
                    `;
                }
            })
            .catch(error => {
                document.getElementById('historyContent').innerHTML = `
                    <div style="text-align: center; padding: 40px; color: var(--danger);">
                        <i class="fas fa-exclamation-triangle"></i>
                        <p style="margin-top: 15px;">Error loading history. Please try again.</p>
                    </div>
                `;
            });
        }

        function showTestWarning() {
            document.getElementById('testInProgress').style.display = 'flex';
        }

        function hideTestWarning() {
            document.getElementById('testInProgress').style.display = 'none';
        }

        function showNavigationWarning() {
            showModal('navigationWarning');
        }

        function forceNavigate() {
            testInProgress = false;
            closeModal('navigationWarning');
            // Submit current test if any
            if (currentTestCard) {
                submitTest(currentTestCard);
            }
            // Allow navigation
            window.location.href = 'class.php';
        }

        // Set up automatic refresh every 60 seconds
        setInterval(updateOnlineStudents, 60000);

        // Manual refresh button
        document.getElementById('refresh-online').addEventListener('click', function() {
            this.classList.add('fa-spin');
            updateOnlineStudents();
            setTimeout(() => {
                this.classList.remove('fa-spin');
            }, 1000);
        });

        // Prevent navigation during test
        window.addEventListener('beforeunload', function (e) {
            if (testInProgress) {
                e.preventDefault();
                e.returnValue = 'You have a test in progress. Are you sure you want to leave?';
                return 'You have a test in progress. Are you sure you want to leave?';
            }
        });

        // Intercept all navigation clicks during test
        document.addEventListener('click', function(e) {
            if (testInProgress && (e.target.closest('a') || e.target.closest('button[onclick*="window.location"]'))) {
                e.preventDefault();
                showNavigationWarning();
            }
        });

        document.querySelectorAll('.start-test').forEach(btn => {
            btn.addEventListener('click', function() {
                const card = this.closest('.test-card');
                const testId = card.getAttribute('data-test-id');
                const durationMinutes = parseInt(card.getAttribute('data-duration'));
                const container = card.querySelector('.questions-container');
                const timerEl = card.querySelector('.timer');
                const timerContainer = card.querySelector('.timer-container');
                const actionsEl = card.querySelector('.test-actions');
                const scoreContainer = card.querySelector('.score-container');
                const startBtn = card.querySelector('.start-test');

                // Set test in progress state
                testInProgress = true;
                currentTestCard = card;

                // Hide start button and show timer
                startBtn.style.display = 'none';
                timerContainer.style.display = 'block';
                
                // Reset containers
                container.innerHTML = '<div style="text-align:center; padding:30px;"><i class="fas fa-spinner fa-spin" style="font-size:24px; margin-right:10px;"></i> Loading questions...</div>';
                scoreContainer.innerHTML = '';
                scoreContainer.style.display = 'none';
                actionsEl.style.display = 'flex';

                // Initialize timer
                let timeLeft = durationMinutes * 60;
                
                function updateTimer() {
                    const mins = Math.floor(timeLeft / 60);
                    const secs = timeLeft % 60;
                    timerEl.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    
                    // Add warning styles when time is running out
                    if (timeLeft <= 300) { // 5 minutes
                        timerEl.classList.add('timer-critical');
                    } else if (timeLeft <= 600) { // 10 minutes
                        timerEl.classList.add('timer-warning');
                    }
                    
                    if(timeLeft <= 0) {
                        clearInterval(timerInterval);
                        showToast('Time is up! Test submitted automatically.', 'error');
                        submitTest(card);
                    }
                    timeLeft--;
                }
                
                updateTimer();
                const timerInterval = setInterval(updateTimer, 1000);

                // Fetch questions
                fetch(`online-test.php?fetch_questions=1&test_id=${testId}`)
                    .then(res => res.text())
                    .then(html => { 
                        container.innerHTML = html;
                        container.style.display = 'block';
                    })
                    .catch(err => { 
                        container.innerHTML = '<div style="text-align:center; padding:30px; color:var(--danger);"><i class="fas fa-exclamation-triangle"></i> Error loading questions. Please try again.</div>';
                        console.error(err); 
                    });

                // Cancel test button
                card.querySelector('.cancel-test').addEventListener('click', function(){
                    if(confirm('Are you sure you want to cancel this test? All progress will be lost.')){
                        container.innerHTML = '';
                        container.style.display = 'none';
                        actionsEl.style.display = 'none';
                        timerContainer.style.display = 'none';
                        timerEl.textContent = '';
                        timerEl.classList.remove('timer-warning', 'timer-critical');
                        startBtn.style.display = 'inline-flex';
                        clearInterval(timerInterval);
                        testInProgress = false;
                        currentTestCard = null;
                        showToast('Test cancelled.', 'warning');
                    }
                });

                // Submit test button
                card.querySelector('.submit-test').addEventListener('click', function(){
                    if(confirm('Are you sure you want to submit the test?')) {
                        submitTest(card);
                        clearInterval(timerInterval);
                    }
                });

                function submitTest(card){
                    const questions = card.querySelectorAll('.question');
                    let totalMarks = 0;
                    let obtainedMarks = 0;
                    const testId = card.getAttribute('data-test-id');

                    questions.forEach(q => {
                        const correct = q.getAttribute('data-correct');
                        const selectedInput = q.querySelector('input[type=radio]:checked');
                        const selected = selectedInput?.value || '';

                        q.classList.add('disabled');

                        // Remove any existing marks
                        q.querySelectorAll('.mark').forEach(m => m.remove());

                        // Add visual feedback for answers
                        if(selected) {
                            const markEl = document.createElement('span');
                            markEl.classList.add('mark');
                            markEl.textContent = (selected === correct) ? '✅' : '❌';
                            markEl.classList.add(selected === correct ? 'correct-answer' : 'wrong-answer');
                            q.querySelector('.options').appendChild(markEl);
                        }

                        // Highlight correct answers
                        q.querySelectorAll('input[type=radio]').forEach(opt => {
                            if(opt.value === correct) {
                                opt.parentElement.classList.add('correct-answer');
                            }
                        });

                        totalMarks++;
                        if(selected === correct) obtainedMarks++;
                    });

                    // Calculate percentage
                    const percentage = Math.round((obtainedMarks / totalMarks) * 100);
                    
                    // Save score to database and display results
                    saveScoreToDatabase(testId, obtainedMarks, totalMarks, percentage, card);
                }

                function saveScoreToDatabase(testId, score, totalMarks, percentage, card) {
                    const submitBtn = card.querySelector('.submit-test');
                    const originalText = submitBtn.innerHTML;
                    
                    // Show loading state
                    submitBtn.innerHTML = '<div class="loading-spinner"></div> Saving...';
                    submitBtn.disabled = true;

                    // Create form data
                    const formData = new FormData();
                    formData.append('action', 'submit_score');
                    formData.append('test_id', testId);
                    formData.append('score', score);
                    formData.append('total_marks', totalMarks);

                    // Send AJAX request to save score
                    fetch('online-test.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            showToast(`${data.message} Score: ${score}/${totalMarks} (${percentage}%)`, 'success');
                            displayScore(card, score, totalMarks, percentage);
                        } else {
                            // Show error but still display the score locally
                            showToast(`Error saving score: ${data.message}`, 'error');
                            displayScore(card, score, totalMarks, percentage);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving score:', error);
                        // Still display the score even if database save fails
                        showToast('Score calculated locally but could not save to database.', 'warning');
                        displayScore(card, score, totalMarks, percentage);
                    })
                    .finally(() => {
                        // Restore button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        testInProgress = false;
                        currentTestCard = null;
                    });
                }

                function displayScore(card, score, totalMarks, percentage) {
                    const scoreContainer = card.querySelector('.score-container');
                    
                    // Display score with animated circle
                    scoreContainer.innerHTML = `
                        <div class="score-display">
                            <div class="score-circle" style="--p: 0%">
                                <div class="score-text">${score}/${totalMarks}</div>
                            </div>
                            <div class="score-label">${percentage}% Correct</div>
                            <div class="performance-message" id="performance-message"></div>
                        </div>
                    `;
                    
                    // Animate the score circle
                    const scoreCircle = scoreContainer.querySelector('.score-circle');
                    setTimeout(() => {
                        updateScoreCircle(scoreCircle, percentage);
                    }, 100);
                    
                    // Set performance message
                    const performanceMessage = scoreContainer.querySelector('#performance-message');
                    let message = '';
                    let messageType = 'info';
                    
                    if (percentage >= 90) {
                        message = 'Outstanding Performance! 🎉';
                        messageType = 'success';
                    } else if (percentage >= 80) {
                        message = 'Excellent Work! 👏';
                        messageType = 'success';
                    } else if (percentage >= 70) {
                        message = 'Good Job! 👍';
                        messageType = 'success';
                    } else if (percentage >= 60) {
                        message = 'Solid Performance 💪';
                    } else if (percentage >= 50) {
                        message = 'You Passed! 📚';
                    } else {
                        message = 'Keep Practicing! 📖';
                        messageType = 'warning';
                    }
                    
                    performanceMessage.textContent = message;
                    if (messageType === 'success') {
                        performanceMessage.style.background = 'linear-gradient(135deg, var(--success) 0%, #059669 100%)';
                        performanceMessage.style.color = 'white';
                    } else if (messageType === 'warning') {
                        performanceMessage.style.background = 'linear-gradient(135deg, var(--warning) 0%, #d97706 100%)';
                        performanceMessage.style.color = 'white';
                    }
                    
                    scoreContainer.style.display = 'block';
                    
                    // Hide test actions and timer
                    card.querySelector('.test-actions').style.display = 'none';
                    card.querySelector('.timer-container').style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
