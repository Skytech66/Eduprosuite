<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Redirect if not logged in
if (!isset($_SESSION['teacher_id'])) {
    header("Location: logginn.php");
    exit;
}

// Supabase connection
$host = "aws-1-eu-north-1.pooler.supabase.com";
$port = "6543";
$dbname = "postgres";
$user = "postgres.mqtuzltstbshtjigzujz";
$password = "Ernestbizz..123";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Sanitize input function
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Handle delete
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $teacher_id = $_SESSION['teacher_id'];
    $stmt = $pdo->prepare("DELETE FROM lesson_notes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$delete_id, $teacher_id]);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Handle edit (populate form)
$edit_note = null;
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['edit_id'])) {
    $edit_id = (int)$_POST['edit_id'];
    $teacher_id = $_SESSION['teacher_id'];
    $stmt = $pdo->prepare("SELECT * FROM lesson_notes WHERE id = ? AND teacher_id = ?");
    $stmt->execute([$edit_id, $teacher_id]);
    $edit_note = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Add or update lesson note
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_note'])) {
    $fields = [
        'week', 'subject', 'week_ending', 'class_size', 'day', 'date', 'period',
        'lesson', 'strand', 'sub_strand', 'indicator_code', 'content_standard_code',
        'performance_indicator', 'core_competencies', 'keywords', 'tls', 'ref',
        'phase1', 'phase2', 'phase3'
    ];

    $values = [];
    foreach ($fields as $field) {
        $values[$field] = sanitize($_POST[$field] ?? '');
    }

    $teacher_id = $_SESSION['teacher_id'];
    $note_id = (int)($_POST['note_id'] ?? 0);

    // Check if indicator_code already exists; if yes, append next available one
    $indicator_codes = explode(',', $values['indicator_code']);
    $selected_indicator = '';
    foreach ($indicator_codes as $code) {
        $code = trim($code);
        $check = $pdo->prepare("SELECT COUNT(*) FROM lesson_notes WHERE teacher_id = ? AND indicator_code = ? AND id != ?");
        $check->execute([$teacher_id, $code, $note_id]);
        if ($check->fetchColumn() == 0) {
            $selected_indicator = $code;
            break;
        }
    }

    // If all exist, use the last one anyway
    if (empty($selected_indicator) && !empty($indicator_codes)) {
        $selected_indicator = end($indicator_codes);
    }

    $values['indicator_code'] = $selected_indicator;

    if ($note_id > 0) {
        // Update existing note
        $stmt = $pdo->prepare("
            UPDATE lesson_notes SET
                week = :week, subject = :subject, week_ending = :week_ending, class_size = :class_size,
                day = :day, date = :date, period = :period, lesson = :lesson, strand = :strand,
                sub_strand = :sub_strand, indicator_code = :indicator_code, content_standard_code = :content_standard_code,
                performance_indicator = :performance_indicator, core_competencies = :core_competencies,
                keywords = :keywords, tls = :tls, ref = :ref, phase1 = :phase1, phase2 = :phase2, phase3 = :phase3
            WHERE id = :note_id AND teacher_id = :teacher_id
        ");
        $stmt->execute(array_merge($values, ['note_id' => $note_id, 'teacher_id' => $teacher_id]));
    } else {
        // Insert new note
        $stmt = $pdo->prepare("
            INSERT INTO lesson_notes (
                teacher_id, week, subject, week_ending, class_size, day, date, period,
                lesson, strand, sub_strand, indicator_code, content_standard_code,
                performance_indicator, core_competencies, keywords, tls, ref,
                phase1, phase2, phase3, created_at
            ) VALUES (
                :teacher_id, :week, :subject, :week_ending, :class_size, :day, :date, :period,
                :lesson, :strand, :sub_strand, :indicator_code, :content_standard_code,
                :performance_indicator, :core_competencies, :keywords, :tls, :ref,
                :phase1, :phase2, :phase3, NOW()
            )
        ");
        $stmt->execute(array_merge(['teacher_id' => $teacher_id], $values));
    }

    // Prevent resubmission: Redirect after successful POST
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch all notes
$teacher_id = $_SESSION['teacher_id'];
$stmt = $pdo->prepare("SELECT * FROM lesson_notes WHERE teacher_id = ? ORDER BY created_at DESC");
$stmt->execute([$teacher_id]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>LessonNotes Pro - AI-Powered Education Platform</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --primary-light: #eef2ff;
      --secondary: #7209b7;
      --success: #4cc9f0;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --border-radius: 12px;
      --shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
      --shadow-hover: 0 8px 24px rgba(0, 0, 0, 0.12);
      --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
      --ai-gradient: linear-gradient(135deg, #4361ee, #7209b7, #4cc9f0);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, Oxygen, Ubuntu, sans-serif;
      line-height: 1.6;
      color: var(--dark);
      background-color: #f5f7fb;
      padding: 0;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }

    /* AI-Inspired Header */
    header {
      background: var(--ai-gradient);
      color: white;
      padding: 18px 0;
      box-shadow: var(--shadow);
      margin-bottom: 30px;
      position: sticky;
      top: 0;
      z-index: 100;
      border-radius: 0 0 var(--border-radius) var(--border-radius);
    }

    .header-content {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
      font-size: 1.5rem;
      font-weight: 700;
    }

    .logo i {
      font-size: 1.8rem;
    }

    .user-info {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    /* Enhanced Hero Slider */
    .hero-slider {
      position: relative;
      height: 350px;
      border-radius: var(--border-radius);
      overflow: hidden;
      margin-bottom: 30px;
      box-shadow: var(--shadow);
    }

    .slide {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      opacity: 0;
      transition: opacity 1s ease-in-out;
      background-size: cover;
      background-position: center;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .slide.active {
      opacity: 1;
    }

    .slide::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(rgba(67, 97, 238, 0.85), rgba(114, 9, 183, 0.8));
      z-index: 1;
    }

    .slide-content {
      position: relative;
      z-index: 2;
      text-align: center;
      max-width: 800px;
      padding: 0 20px;
    }

    .slide-title {
      font-size: 2.8rem;
      font-weight: 800;
      color: white;
      margin-bottom: 15px;
      text-shadow: 0 2px 10px rgba(0, 0, 0, 0.4);
      line-height: 1.2;
      font-family: 'Montserrat', sans-serif;
      letter-spacing: -0.5px;
    }

    .slide-subtitle {
      font-size: 1.6rem;
      font-weight: 600;
      color: white;
      margin-bottom: 20px;
      text-shadow: 0 2px 8px rgba(0, 0, 0, 0.4);
      font-family: 'Raleway', sans-serif;
    }

    .slide-tagline {
      font-size: 1.2rem;
      font-weight: 500;
      color: white;
      text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
      font-family: 'Raleway', sans-serif;
      opacity: 0.95;
    }

    .slider-nav {
      position: absolute;
      bottom: 20px;
      left: 50%;
      transform: translateX(-50%);
      display: flex;
      gap: 10px;
      z-index: 3;
    }

    .slider-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      cursor: pointer;
      transition: var(--transition);
    }

    .slider-dot.active {
      background: white;
      transform: scale(1.2);
    }

    /* Toast Notification Styles */
    .toast-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 9999;
      max-width: 350px;
    }

    .toast {
      background: white;
      border-radius: var(--border-radius);
      padding: 16px 20px;
      margin-bottom: 10px;
      box-shadow: var(--shadow-hover);
      display: flex;
      align-items: center;
      gap: 12px;
      transform: translateX(400px);
      transition: transform 0.3s ease;
      border-left: 4px solid var(--primary);
    }

    .toast.show {
      transform: translateX(0);
    }

    .toast-icon {
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      flex-shrink: 0;
    }

    .toast-success .toast-icon {
      background-color: var(--success);
    }

    .toast-info .toast-icon {
      background-color: var(--primary);
    }

    .toast-warning .toast-icon {
      background-color: #ffc107;
    }

    .toast-error .toast-icon {
      background-color: #e63946;
    }

    .toast-content {
      flex: 1;
    }

    .toast-title {
      font-weight: 600;
      margin-bottom: 4px;
      color: var(--dark);
    }

    .toast-message {
      font-size: 0.9rem;
      color: var(--gray);
    }

    .toast-close {
      background: none;
      border: none;
      color: var(--gray);
      cursor: pointer;
      padding: 4px;
      border-radius: 4px;
      transition: var(--transition);
    }

    .toast-close:hover {
      background-color: var(--light-gray);
      color: var(--dark);
    }

    /* AI-Polished Buttons */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 12px 22px;
      background-color: var(--primary);
      color: white;
      border: none;
      border-radius: var(--border-radius);
      cursor: pointer;
      font-weight: 600;
      transition: var(--transition);
      text-decoration: none;
      text-align: center;
      font-size: 0.95rem;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: left 0.5s;
    }

    .btn:hover::before {
      left: 100%;
    }

    .btn:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .btn-secondary {
      background-color: var(--gray);
    }

    .btn-secondary:hover {
      background-color: #5a6268;
    }

    .btn-success {
      background-color: var(--success);
    }

    .btn-danger {
      background-color: #e63946;
    }

    .btn-danger:hover {
      background-color: #d00000;
    }

    .btn-outline {
      background-color: transparent;
      border: 2px solid var(--primary);
      color: var(--primary);
    }

    .btn-outline:hover {
      background-color: var(--primary);
      color: white;
    }

    .btn-ai {
      background: var(--ai-gradient);
      background-size: 200% 200%;
      animation: gradientShift 3s ease infinite;
    }

    @keyframes gradientShift {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    .btn-sm {
      padding: 8px 14px;
      font-size: 0.875rem;
    }

    /* Enhanced Cards with AI Feel */
    .card {
      background-color: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      padding: 28px;
      margin-bottom: 25px;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }

    .card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 4px;
      height: 100%;
      background: var(--ai-gradient);
    }

    .card:hover {
      box-shadow: var(--shadow-hover);
      transform: translateY(-5px);
    }

    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
      padding-bottom: 18px;
      border-bottom: 1px solid var(--light-gray);
    }

    .card-title {
      font-size: 1.6rem;
      font-weight: 700;
      color: var(--dark);
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .form-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 22px;
    }

    .form-group {
      margin-bottom: 22px;
      position: relative;
    }

    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--dark);
      font-size: 0.95rem;
    }

    .form-control {
      width: 100%;
      padding: 14px;
      border: 1px solid var(--light-gray);
      border-radius: var(--border-radius);
      font-size: 1rem;
      transition: var(--transition);
      background-color: #fff;
    }

    .form-control:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    textarea.form-control {
      min-height: 100px;
      resize: vertical;
      line-height: 1.5;
    }

    .voice-btn {
      position: absolute;
      right: 10px;
      bottom: 10px;
      background-color: var(--light-gray);
      border: none;
      border-radius: 50%;
      width: 38px;
      height: 38px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: var(--transition);
      color: var(--gray);
    }

    .voice-btn:hover {
      background-color: var(--primary);
      color: white;
      transform: scale(1.05);
    }

    .notes-container {
      margin-top: 30px;
    }

    .notes-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 22px;
    }

    .filter-controls {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    .note-card {
      background-color: white;
      border-radius: var(--border-radius);
      box-shadow: var(--shadow);
      padding: 24px;
      margin-bottom: 22px;
      position: relative;
      transition: var(--transition);
      border-left: 4px solid var(--primary);
    }

    .note-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-hover);
    }

    .note-header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 18px;
    }

    .note-title {
      font-size: 1.3rem;
      font-weight: 700;
      color: var(--dark);
      margin-bottom: 6px;
    }

    .note-meta {
      color: var(--gray);
      font-size: 0.9rem;
      margin-bottom: 10px;
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .note-meta span {
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .note-actions {
      display: flex;
      gap: 8px;
    }

    .note-content {
      margin-top: 18px;
    }

    .phase {
      margin-bottom: 18px;
      padding: 16px;
      background-color: var(--primary-light);
      border-radius: var(--border-radius);
    }

    .phase-title {
      font-weight: 700;
      margin-bottom: 8px;
      color: var(--primary);
      font-size: 1.05rem;
    }

    .hidden-content {
      display: none;
    }

    .toggle-btn {
      background: none;
      border: none;
      color: var(--primary);
      cursor: pointer;
      font-weight: 600;
      display: flex;
      align-items: center;
      gap: 6px;
      margin-top: 12px;
      padding: 8px 0;
      transition: var(--transition);
    }

    .toggle-btn:hover {
      color: var(--primary-dark);
    }

    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--gray);
    }

    .empty-state i {
      font-size: 4rem;
      margin-bottom: 20px;
      color: var(--light-gray);
    }

    .empty-state h3 {
      font-size: 1.5rem;
      margin-bottom: 12px;
    }

    .tabs {
      display: flex;
      border-bottom: 1px solid var(--light-gray);
      margin-bottom: 25px;
      overflow-x: auto;
      scrollbar-width: none;
    }

    .tabs::-webkit-scrollbar {
      display: none;
    }

    .tab {
      padding: 14px 24px;
      cursor: pointer;
      font-weight: 600;
      color: var(--gray);
      transition: var(--transition);
      border-bottom: 3px solid transparent;
      white-space: nowrap;
    }

    .tab.active {
      color: var(--primary);
      border-bottom: 3px solid var(--primary);
    }

    .tab:hover:not(.active) {
      color: var(--primary);
      background-color: rgba(67, 97, 238, 0.05);
    }

    .tab-content {
      display: none;
    }

    .tab-content.active {
      display: block;
      animation: fadeIn 0.4s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .alert {
      padding: 16px;
      border-radius: var(--border-radius);
      margin-bottom: 22px;
    }

    .alert-success {
      background-color: #d4edda;
      color: #155724;
      border: 1px solid #c3e6cb;
    }

    .alert-info {
      background-color: #d1ecf1;
      color: #0c5460;
      border: 1px solid #bee5eb;
    }

    .badge {
      display: inline-block;
      padding: 4px 10px;
      background-color: var(--primary-light);
      color: var(--primary);
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    /* Dashboard Navigation */
    .dashboard-nav {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .nav-actions {
      display: flex;
      gap: 12px;
      align-items: center;
    }

    /* Loading animation */
    .loading {
      display: inline-block;
      width: 20px;
      height: 20px;
      border: 3px solid rgba(255,255,255,.3);
      border-radius: 50%;
      border-top-color: #fff;
      animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }

    /* Accessibility improvements */
    .high-contrast {
      filter: contrast(1.2);
    }

    .text-large {
      font-size: 1.1em;
    }

    .accessibility-controls {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }

    /* Mobile optimizations */
    @media (max-width: 768px) {
      .container {
        padding: 15px;
      }
      
      .hero-slider {
        height: 300px;
      }
      
      .slide-title {
        font-size: 2.2rem;
      }
      
      .slide-subtitle {
        font-size: 1.3rem;
      }
      
      .slide-tagline {
        font-size: 1rem;
      }
      
      .form-grid {
        grid-template-columns: 1fr;
      }
      
      .header-content {
        flex-direction: column;
        gap: 15px;
        text-align: center;
      }
      
      .notes-header {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
      }
      
      .filter-controls {
        width: 100%;
        flex-direction: column;
      }
      
      .filter-controls .form-control {
        width: 100%;
      }
      
      .note-header {
        flex-direction: column;
        gap: 15px;
      }
      
      .note-actions {
        width: 100%;
        justify-content: flex-start;
      }
      
      .card {
        padding: 20px;
      }
      
      .tab {
        padding: 12px 18px;
        font-size: 0.9rem;
      }
      
      .dashboard-nav {
        flex-direction: column;
        align-items: flex-start;
      }
    }

    @media (max-width: 480px) {
      .btn {
        width: 100%;
        justify-content: center;
      }
      
      .note-meta {
        flex-direction: column;
        gap: 5px;
      }
      
      .hero-slider {
        height: 250px;
      }
      
      .slide-title {
        font-size: 1.8rem;
      }
      
      .slide-subtitle {
        font-size: 1.1rem;
      }
      
      .slide-tagline {
        font-size: 0.9rem;
      }
    }
  </style>
</head>
<body>
  <!-- Toast Notifications Container -->
  <div class="toast-container" id="toastContainer"></div>

  <header>
    <div class="container">
      <div class="header-content">
        <div class="logo">
          <i class="fas fa-robot"></i>
          <span>LessonNotes Pro AI</span>
        </div>
        <div class="user-info">
          <span>Welcome, <?= sanitize($_SESSION['teacher_name']) ?></span>
          <a href="logout.php" class="btn btn-outline">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </div>
      </div>
    </div>
  </header>

  <div class="container">
    <!-- Dashboard Navigation -->
    <div class="dashboard-nav">
      <a href="dashboard.php" class="btn btn-ai">
        <i class="fas fa-tachometer-alt"></i> Back to Dashboard
      </a>
      <div class="nav-actions">
        <button class="btn btn-outline" onclick="toggleHighContrast()">
          <i class="fas fa-adjust"></i> High Contrast
        </button>
        <button class="btn btn-outline" onclick="toggleTextSize()">
          <i class="fas fa-text-height"></i> Larger Text
        </button>
        <button class="btn btn-info" onclick="showAIAssistant()">
          <i class="fas fa-robot"></i> AI Assistant
        </button>
      </div>
    </div>

    <!-- Enhanced Hero Slider -->
    <div class="hero-slider" id="heroSlider">
      <div class="slide active" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80')">
        <div class="slide-content">
          <h1 class="slide-title">Smart Tools for Smarter Education</h1>
          <p class="slide-subtitle">Your School, Your Classroom, Your Control</p>
          <p class="slide-tagline">Simplifying School Management, One Click at a Time</p>
        </div>
      </div>
      <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2022&q=80')">
        <div class="slide-content">
          <h1 class="slide-title">AI-Powered Lesson Planning</h1>
          <p class="slide-subtitle">Create Engaging Lessons in Minutes</p>
          <p class="slide-tagline">Voice Input, Smart Analysis, Instant Results</p>
        </div>
      </div>
      <div class="slide" style="background-image: url('https://images.unsplash.com/photo-1588072432836-e10032781450?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2072&q=80')">
        <div class="slide-content">
          <h1 class="slide-title">Streamlined Classroom Management</h1>
          <p class="slide-subtitle">Organize, Track, and Analyze</p>
          <p class="slide-tagline">Everything You Need in One Platform</p>
        </div>
      </div>
      <div class="slider-nav" id="sliderNav">
        <div class="slider-dot active" data-slide="0"></div>
        <div class="slider-dot" data-slide="1"></div>
        <div class="slider-dot" data-slide="2"></div>
      </div>
    </div>

    <div class="tabs">
      <div class="tab active" data-tab="form">
        <i class="fas fa-plus-circle"></i> Create Lesson Note
      </div>
      <div class="tab" data-tab="notes">
        <i class="fas fa-clipboard-list"></i> My Lesson Notes
        <?php if ($notes): ?>
          <span class="badge" style="margin-left: 8px;"><?= count($notes) ?></span>
        <?php endif; ?>
      </div>
    </div>

    <div class="tab-content active" id="form-tab">
      <div class="card">
        <div class="card-header">
          <h2 class="card-title">
            <i class="fas fa-<?= $edit_note ? 'edit' : 'plus-circle' ?>"></i>
            <?= $edit_note ? 'Edit' : 'Create New' ?> Lesson Note
          </h2>
          <button type="button" class="btn btn-ai" onclick="showDemoAlert()">
            <i class="fas fa-robot"></i> AI Assistant
          </button>
        </div>
        
        <form method="POST" id="lessonForm">
          <input type="hidden" name="note_id" id="note_id" value="<?= $edit_note ? $edit_note['id'] : '' ?>">
          
          <div class="form-grid">
            <div class="form-group">
              <label for="week">Week</label>
              <input type="text" name="week" id="week" class="form-control" value="<?= $edit_note ? sanitize($edit_note['week']) : '' ?>">
            </div>
            
            <div class="form-group">
              <label for="subject">Subject</label>
              <input type="text" name="subject" id="subject" class="form-control" value="<?= $edit_note ? sanitize($edit_note['subject']) : '' ?>">
            </div>
            
            <div class="form-group">
              <label for="week_ending">Week Ending</label>
              <input type="date" name="week_ending" id="week_ending" class="form-control" value="<?= $edit_note ? sanitize($edit_note['week_ending']) : '' ?>">
            </div>
            
            <div class="form-group">
              <label for="class_size">Class Size</label>
              <input type="text" name="class_size" id="class_size" class="form-control" value="<?= $edit_note ? sanitize($edit_note['class_size']) : '' ?>">
            </div>
            
            <div class="form-group">
              <label for="day">Day</label>
              <select name="day" id="day" class="form-control">
                <option value="">Select Day</option>
                <option value="Monday" <?= $edit_note && $edit_note['day'] == 'Monday' ? 'selected' : '' ?>>Monday</option>
                <option value="Tuesday" <?= $edit_note && $edit_note['day'] == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                <option value="Wednesday" <?= $edit_note && $edit_note['day'] == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                <option value="Thursday" <?= $edit_note && $edit_note['day'] == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                <option value="Friday" <?= $edit_note && $edit_note['day'] == 'Friday' ? 'selected' : '' ?>>Friday</option>
                <option value="Saturday" <?= $edit_note && $edit_note['day'] == 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                <option value="Sunday" <?= $edit_note && $edit_note['day'] == 'Sunday' ? 'selected' : '' ?>>Sunday</option>
              </select>
            </div>
            
            <div class="form-group">
              <label for="date">Date</label>
              <input type="date" name="date" id="date" class="form-control" value="<?= $edit_note ? sanitize($edit_note['date']) : '' ?>">
            </div>
            
            <div class="form-group">
              <label for="period">Period</label>
              <input type="text" name="period" id="period" class="form-control" value="<?= $edit_note ? sanitize($edit_note['period']) : '' ?>">
            </div>
            
            <div class="form-group">
              <label for="lesson">Lesson</label>
              <input type="text" name="lesson" id="lesson" class="form-control" value="<?= $edit_note ? sanitize($edit_note['lesson']) : '' ?>">
            </div>
          </div>
          
          <div class="form-group">
            <label for="strand">Strand</label>
            <input type="text" name="strand" id="strand" class="form-control" value="<?= $edit_note ? sanitize($edit_note['strand']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="sub_strand">Sub-Strand</label>
            <input type="text" name="sub_strand" id="sub_strand" class="form-control" value="<?= $edit_note ? sanitize($edit_note['sub_strand']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="indicator_code">Indicator (Code)</label>
            <input type="text" name="indicator_code" id="indicator_code" class="form-control" value="<?= $edit_note ? sanitize($edit_note['indicator_code']) : '' ?>" placeholder="Enter codes separated by commas, e.g., A1.1.1.1.1, A1.1.1.1.2">
          </div>
          
          <div class="form-group">
            <label for="content_standard_code">Content Standard (Code)</label>
            <input type="text" name="content_standard_code" id="content_standard_code" class="form-control" value="<?= $edit_note ? sanitize($edit_note['content_standard_code']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="performance_indicator">Performance Indicator</label>
            <input type="text" name="performance_indicator" id="performance_indicator" class="form-control" value="<?= $edit_note ? sanitize($edit_note['performance_indicator']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="core_competencies">Core Competencies</label>
            <input type="text" name="core_competencies" id="core_competencies" class="form-control" value="<?= $edit_note ? sanitize($edit_note['core_competencies']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="keywords">Keywords</label>
            <input type="text" name="keywords" id="keywords" class="form-control" value="<?= $edit_note ? sanitize($edit_note['keywords']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="tls">T.L.S</label>
            <input type="text" name="tls" id="tls" class="form-control" value="<?= $edit_note ? sanitize($edit_note['tls']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="ref">Reference</label>
            <input type="text" name="ref" id="ref" class="form-control" value="<?= $edit_note ? sanitize($edit_note['ref']) : '' ?>">
          </div>
          
          <div class="form-group">
            <label for="curriculum_text">Paste Curriculum Here</label>
            <textarea id="curriculum_text" class="form-control" rows="6"></textarea>
            <button type="button" class="btn btn-ai" onclick="analyzeCurriculum()">
              <i class="fas fa-magic"></i> AI Curriculum Analysis
            </button>
          </div>
          
          <div class="form-group">
            <label for="phase1">Phase 1 (Starter)</label>
            <textarea name="phase1" id="phase1" class="form-control" rows="3"><?= $edit_note ? sanitize($edit_note['phase1']) : '' ?></textarea>
            <button type="button" class="voice-btn" onclick="startListening('phase1')" title="Voice Input">
              <i class="fas fa-microphone"></i>
            </button>
          </div>
          
          <div class="form-group">
            <label for="phase2">Phase 2 (Main)</label>
            <textarea name="phase2" id="phase2" class="form-control" rows="3"><?= $edit_note ? sanitize($edit_note['phase2']) : '' ?></textarea>
            <button type="button" class="voice-btn" onclick="startListening('phase2')" title="Voice Input">
              <i class="fas fa-microphone"></i>
            </button>
          </div>
          
          <div class="form-group">
            <label for="phase3">Phase 3 (Plenary/Reflections)</label>
            <textarea name="phase3" id="phase3" class="form-control" rows="3"><?= $edit_note ? sanitize($edit_note['phase3']) : '' ?></textarea>
            <button type="button" class="voice-btn" onclick="startListening('phase3')" title="Voice Input">
              <i class="fas fa-microphone"></i>
            </button>
          </div>
          
          <div style="display: flex; gap: 15px; margin-top: 25px; flex-wrap: wrap;">
            <button type="submit" name="add_note" class="btn btn-success" onclick="showSuccessToast()">
              <i class="fas fa-save"></i> <?= $edit_note ? 'Update' : 'Save' ?> Lesson Note
            </button>
            <?php if ($edit_note): ?>
              <a href="<?= $_SERVER['PHP_SELF'] ?>" class="btn btn-secondary">Cancel Edit</a>
            <?php endif; ?>
            <button type="button" class="btn btn-outline" onclick="clearForm()">
              <i class="fas fa-eraser"></i> Clear Form
            </button>
            <button type="button" class="btn btn-info" onclick="showFeatureToast()">
              <i class="fas fa-bell"></i> Test Notification
            </button>
          </div>
        </form>
      </div>
    </div>

   <div class="tab-content" id="notes-tab">
  <div class="notes-container">
    <div class="notes-header">
      <h2 class="card-title">
        <i class="fas fa-clipboard-list"></i> My Lesson Notes
      </h2>
      <div class="filter-controls">
        <input type="text" id="search-notes" class="form-control" placeholder="Search notes...">
        <select id="filter-subject" class="form-control">
          <option value="">All Subjects</option>
          <?php
          $subjects = array_unique(array_column($notes, 'subject'));
          foreach ($subjects as $subject) {
            echo "<option value=\"$subject\">$subject</option>";
          }
          ?>
        </select>
        <!-- NaCCA Button -->
        <a href="https://nacca.gov.gh/learning-areas-subjects/new-standards-based-curriculum-2019/#1554979862938-431d18ec-e24e" 
           class="btn btn-info" 
           target="_blank" 
           style="margin-left: 10px;">
          View NaCCA Curriculum
        </a>
      </div>
    </div>

        
        <?php if ($notes): ?>
          <div id="notes-list">
            <?php foreach ($notes as $note): ?>
              <div class="note-card" data-subject="<?= sanitize($note['subject']) ?>">
                <div class="note-header">
                  <div>
                    <h3 class="note-title"><?= sanitize($note['lesson']) ?></h3>
                    <div class="note-meta">
                      <span><i class="fas fa-calendar-week"></i> <strong>Week:</strong> <?= sanitize($note['week']) ?></span>
                      <span><i class="fas fa-book"></i> <strong>Subject:</strong> <?= sanitize($note['subject']) ?></span>
                      <span><i class="fas fa-calendar-day"></i> <strong>Date:</strong> <?= sanitize($note['date']) ?></span>
                    </div>
                  </div>
                  <div class="note-actions">
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="edit_id" value="<?= $note['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-outline" title="Edit">
                        <i class="fas fa-edit"></i>
                      </button>
                    </form>
                    <form method="POST" style="display:inline;" onsubmit="return confirmDelete(this);">
                      <input type="hidden" name="delete_id" value="<?= $note['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger" title="Delete">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                    <form method="POST" action="generate_pdf.php" style="display:inline;">
                      <input type="hidden" name="note_id" value="<?= $note['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-success" title="Download PDF">
                        <i class="fas fa-download"></i>
                      </button>
                    </form>
                  </div>
                </div>
                
                <div class="note-content">
                  <p><strong>Strand:</strong> <?= sanitize($note['strand']) ?></p>
                  <p><strong>Sub-Strand:</strong> <?= sanitize($note['sub_strand']) ?></p>
                  <p><strong>Indicator:</strong> <?= sanitize($note['indicator_code']) ?></p>
                  
                  <button class="toggle-btn" onclick="toggleContent(this)">
                    <i class="fas fa-chevron-down"></i> Show Lesson Details
                  </button>
                  
                  <div class="hidden-content">
                    <div class="phase">
                      <div class="phase-title">Phase 1 (Starter)</div>
                      <p><?= nl2br(sanitize($note['phase1'])) ?></p>
                    </div>
                    
                    <div class="phase">
                      <div class="phase-title">Phase 2 (Main)</div>
                      <p><?= nl2br(sanitize($note['phase2'])) ?></p>
                    </div>
                    
                    <div class="phase">
                      <div class="phase-title">Phase 3 (Plenary/Reflections)</div>
                      <p><?= nl2br(sanitize($note['phase3'])) ?></p>
                    </div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <h3>No Lesson Notes Yet</h3>
            <p>Create your first lesson note to get started!</p>
            <a href="#" class="btn btn-ai" style="margin-top: 15px;" onclick="switchToFormTab()">
              <i class="fas fa-plus-circle"></i> Create Your First Note
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    // Slider functionality
    let currentSlide = 0;
    const slides = document.querySelectorAll('.slide');
    const dots = document.querySelectorAll('.slider-dot');
    const slideInterval = 5000; // 5 seconds

    function showSlide(index) {
      // Hide all slides
      slides.forEach(slide => slide.classList.remove('active'));
      dots.forEach(dot => dot.classList.remove('active'));
      
      // Show selected slide
      slides[index].classList.add('active');
      dots[index].classList.add('active');
      currentSlide = index;
    }

    function nextSlide() {
      let next = currentSlide + 1;
      if (next >= slides.length) next = 0;
      showSlide(next);
    }

    // Initialize slider
    dots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        showSlide(index);
        resetTimer();
      });
    });

    // Auto-advance slides
    let slideTimer = setInterval(nextSlide, slideInterval);

    function resetTimer() {
      clearInterval(slideTimer);
      slideTimer = setInterval(nextSlide, slideInterval);
    }

    // Accessibility functions
    function toggleHighContrast() {
      document.body.classList.toggle('high-contrast');
      showToast('info', 'Accessibility', 
        document.body.classList.contains('high-contrast') 
          ? 'High contrast mode enabled' 
          : 'High contrast mode disabled');
    }

    function toggleTextSize() {
      document.body.classList.toggle('text-large');
      showToast('info', 'Accessibility', 
        document.body.classList.contains('text-large') 
          ? 'Large text mode enabled' 
          : 'Large text mode disabled');
    }

    // Toast Notification Functions
    function showToast(type, title, message, duration = 5000) {
      const toastContainer = document.getElementById('toastContainer');
      const toast = document.createElement('div');
      toast.className = `toast toast-${type}`;
      toast.innerHTML = `
        <div class="toast-icon">
          <i class="fas fa-${getToastIcon(type)}"></i>
        </div>
        <div class="toast-content">
          <div class="toast-title">${title}</div>
          <div class="toast-message">${message}</div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
          <i class="fas fa-times"></i>
        </button>
      `;
      
      toastContainer.appendChild(toast);
      
      // Show toast with animation
      setTimeout(() => {
        toast.classList.add('show');
      }, 100);
      
      // Auto remove after duration
      if (duration > 0) {
        setTimeout(() => {
          if (toast.parentElement) {
            toast.classList.remove('show');
            setTimeout(() => {
              if (toast.parentElement) {
                toast.remove();
              }
            }, 300);
          }
        }, duration);
      }
    }
    
    function getToastIcon(type) {
      const icons = {
        success: 'check',
        info: 'info',
        warning: 'exclamation-triangle',
        error: 'exclamation-circle'
      };
      return icons[type] || 'info';
    }
    
    // Demo toast functions
    function showSuccessToast() {
      showToast('success', 'Success!', 'Lesson note saved successfully.');
    }
    
    function showFeatureToast() {
      showToast('info', 'New Feature', 'Voice input is now available for lesson phases!');
    }
    
    // SweetAlert Modal Functions
    function showDemoAlert() {
      Swal.fire({
        title: 'AI Assistant - LessonNotes Pro',
        html: `
          <div style="text-align: left;">
            <p><strong>How can I help you today?</strong></p>
            <ul>
              <li>📝 Create engaging lesson plans with AI suggestions</li>
              <li>🎤 Use voice input for faster note creation</li>
              <li>🔍 Analyze curriculum standards automatically</li>
              <li>📊 Get insights on your teaching patterns</li>
              <li>📄 Generate professional PDF reports</li>
            </ul>
            <p>Try the voice input feature by clicking the microphone icons next to text areas!</p>
          </div>
        `,
        icon: 'info',
        confirmButtonText: 'Got It!',
        confirmButtonColor: '#4361ee',
        width: 600
      });
    }

    function showAIAssistant() {
      Swal.fire({
        title: 'AI Teaching Assistant',
        html: `
          <div style="text-align: left;">
            <p>I can help you with:</p>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin: 15px 0;">
              <button class="btn btn-outline" onclick="Swal.close(); analyzeCurriculum();" style="padding: 10px;">
                <i class="fas fa-magic"></i> Analyze Curriculum
              </button>
              <button class="btn btn-outline" onclick="Swal.close(); generateLessonIdeas();" style="padding: 10px;">
                <i class="fas fa-lightbulb"></i> Lesson Ideas
              </button>
              <button class="btn btn-outline" onclick="Swal.close(); showAssessmentTips();" style="padding: 10px;">
                <i class="fas fa-clipboard-check"></i> Assessment Tips
              </button>
              <button class="btn btn-outline" onclick="Swal.close(); showDemoAlert();" style="padding: 10px;">
                <i class="fas fa-info-circle"></i> Platform Guide
              </button>
            </div>
          </div>
        `,
        showConfirmButton: false,
        showCloseButton: true,
        width: 500
      });
    }

    function generateLessonIdeas() {
      Swal.fire({
        title: 'AI Lesson Ideas',
        html: `
          <div style="text-align: left;">
            <p>Based on your subject and curriculum, here are some engaging activity ideas:</p>
            <ul>
              <li><strong>Interactive Quiz:</strong> Use Kahoot or Quizlet for vocabulary review</li>
              <li><strong>Group Project:</strong> Collaborative problem-solving activity</li>
              <li><strong>Role Play:</strong> Historical reenactment or scenario simulation</li>
              <li><strong>Digital Storytelling:</strong> Create multimedia presentations</li>
              <li><strong>Hands-on Experiment:</strong> Practical application of concepts</li>
            </ul>
            <p>Would you like me to add any of these to your lesson plan?</p>
          </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add to Phase 2',
        cancelButtonText: 'Maybe Later',
        confirmButtonColor: '#4361ee'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('phase2').value += "\n• Interactive group activity based on AI suggestions";
          showToast('success', 'AI Suggestion Added', 'Lesson activity added to Phase 2');
        }
      });
    }

    function showAssessmentTips() {
      Swal.fire({
        title: 'AI Assessment Strategies',
        html: `
          <div style="text-align: left;">
            <p>Effective assessment strategies for your lesson:</p>
            <ul>
              <li><strong>Exit Tickets:</strong> Quick formative assessment at lesson end</li>
              <li><strong>Think-Pair-Share:</strong> Collaborative understanding check</li>
              <li><strong>One-Minute Paper:</strong> Brief written reflection</li>
              <li><strong>Traffic Light System:</strong> Visual understanding indicators</li>
              <li><strong>Peer Assessment:</strong> Students evaluate each other's work</li>
            </ul>
            <p>Consider adding 1-2 of these to your lesson reflection phase.</p>
          </div>
        `,
        confirmButtonText: 'Thanks!',
        confirmButtonColor: '#4361ee',
        width: 550
      });
    }
    
    function confirmDelete(form) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e63946',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
      return false;
    }

    // Tab functionality
    document.querySelectorAll('.tab').forEach(tab => {
      tab.addEventListener('click', () => {
        // Remove active class from all tabs and contents
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        
        // Add active class to clicked tab and corresponding content
        tab.classList.add('active');
        document.getElementById(`${tab.dataset.tab}-tab`).classList.add('active');
        
        // Store active tab in localStorage
        localStorage.setItem('activeTab', tab.dataset.tab);
      });
    });

    // Restore active tab on page load
    window.addEventListener('load', function() {
      const activeTab = localStorage.getItem('activeTab') || 'form';
      document.querySelector(`.tab[data-tab="${activeTab}"]`).click();
      
      // Restore form data
      const savedData = localStorage.getItem('lessonFormData');
      if (savedData) {
        const data = JSON.parse(savedData);
        for (let key in data) {
          const element = document.getElementById(key);
          if (element && key !== 'note_id') {
            element.value = data[key];
          }
        }
      }
      
      // Show welcome toast on first load
      setTimeout(() => {
        showToast('info', 'Welcome to LessonNotes Pro AI!', 'Your AI-powered lesson planning assistant is ready to help.');
      }, 1000);
    });

    // Toggle content visibility
    function toggleContent(button) {
      const content = button.nextElementSibling;
      const icon = button.querySelector('i');
      
      if (content.style.display === 'block') {
        content.style.display = 'none';
        icon.className = 'fas fa-chevron-down';
        button.innerHTML = '<i class="fas fa-chevron-down"></i> Show Lesson Details';
      } else {
        content.style.display = 'block';
        icon.className = 'fas fa-chevron-up';
        button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Lesson Details';
      }
    }

    // Search and filter functionality
    document.getElementById('search-notes').addEventListener('input', filterNotes);
    document.getElementById('filter-subject').addEventListener('change', filterNotes);
    
    function filterNotes() {
      const searchTerm = document.getElementById('search-notes').value.toLowerCase();
      const subjectFilter = document.getElementById('filter-subject').value;
      
      document.querySelectorAll('.note-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        const subject = card.dataset.subject;
        
        const matchesSearch = text.includes(searchTerm);
        const matchesSubject = !subjectFilter || subject === subjectFilter;
        
        if (matchesSearch && matchesSubject) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      });
    }

    // Auto-save form data to localStorage every 5 seconds
    function autoSave() {
      const form = document.getElementById('lessonForm');
      const formData = new FormData(form);
      const data = {};
      for (let [key, value] of formData.entries()) {
        data[key] = value;
      }
      localStorage.setItem('lessonFormData', JSON.stringify(data));
    }
    setInterval(autoSave, 5000); // Save every 5 seconds

    // Clear form data
    function clearForm() {
      Swal.fire({
        title: 'Clear Form?',
        text: 'This will remove all entered data from the form.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4361ee',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, clear it!',
        cancelButtonText: 'Cancel'
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('lessonForm').reset();
          localStorage.removeItem('lessonFormData');
          showToast('success', 'Form Cleared', 'All form data has been cleared.');
        }
      });
    }

    // Switch to form tab
    function switchToFormTab() {
      document.querySelector('.tab[data-tab="form"]').click();
    }

    // Clear localStorage after successful submission
    document.getElementById('lessonForm').addEventListener('submit', function() {
      localStorage.removeItem('lessonFormData');
    });

    function analyzeCurriculum() {
      const text = document.getElementById('curriculum_text').value;
      if (!text.trim()) {
        showToast('warning', 'Empty Input', 'Please paste curriculum content first.');
        return;
      }

      const lines = text.split('\n').map(l => l.trim()).filter(l => l);

      let strand = '';
      let subStrand = '';
      let contentStandardCode = '';
      let contentStandardText = '';
      let indicatorCodes = [];
      let indicatorsText = [];

      for (let line of lines) {
        if (line.startsWith('STRAND')) {
          const match = line.match(/STRAND \d+: (.+)/);
          if (match) strand = match[1];
        } else if (line.startsWith('Sub-Strand')) {
          const match = line.match(/Sub-Strand \d+: (.+)/);
          if (match) subStrand = match[1];
        } else if (/^[A-Z]\d+\.\d+\.\d+\.\d+:\s/.test(line)) { 
          const match = line.match(/^([A-Z]\d+\.\d+\.\d+\.\d+):\s(.+)/);
          if (match) {
            contentStandardCode = match[1];
            contentStandardText = match[2];
          }
        } else if (/^[A-Z]\d+\.\d+\.\d+\.\d+\.\d+:\s/.test(line)) { 
          const match = line.match(/^([A-Z]\d+\.\d+\.\d+\.\d+\.\d+):\s(.+)/);
          if (match) {
            indicatorCodes.push(match[1]);
            indicatorsText.push(match[2]);
          }
        }
      }

      // Extract bullet sentence for performance indicator
      const bulletMatch = text.match(/•\s*([^•]+?\.)/);
      let performanceText = '';
      if (bulletMatch) {
        const bulletSentence = bulletMatch[1].trim();
        performanceText = "Learners can " + bulletSentence.charAt(0).toLowerCase() + bulletSentence.slice(1);
      } else {
        performanceText = indicatorsText[0] || contentStandardText;
      }

      // Smart core competency detection (subject-independent)
      function detectCoreCompetency(txt) {
        const t = txt.toLowerCase();
        const categories = {
          "Communication and Collaboration": ["explain", "discuss", "share", "express", "present", "communicate", "listen", "describe", "report", "argue"],
          "Personal Development and Leadership": ["lead", "organize", "participate", "motivate", "responsibility", "teamwork", "support", "guide", "confidence", "initiative"],
          "Critical Thinking and Problem Solving": ["solve", "analyze", "decide", "evaluate", "investigate", "reason", "compare", "judge", "interpret", "examine"],
          "Creativity and Innovation": ["design", "create", "invent", "imagine", "develop", "compose", "construct", "build", "plan", "produce"],
          "Cultural Identity and Global Citizenship": ["respect", "culture", "tradition", "diversity", "values", "citizenship", "responsible", "rights", "community", "heritage"],
          "Digital Literacy": ["use", "apply", "technology", "computer", "digital", "internet", "online", "data", "device", "software"]
        };

        let bestMatch = "Communication and Collaboration";
        let highestScore = 0;
        for (const [competency, keywords] of Object.entries(categories)) {
          let score = 0;
          keywords.forEach(word => {
            const regex = new RegExp(`\\b${word}\\b`, "g");
            const matches = t.match(regex);
            if (matches) score += matches.length;
          });
          if (score > highestScore) {
            highestScore = score;
            bestMatch = competency;
          }
        }
        return bestMatch;
      }

      // Generate at least five keywords intelligently
      function extractKeywords(...texts) {
        const combined = texts.join(' ').toLowerCase();
        const words = combined.match(/\b[a-z]{4,}\b/g) || [];
        const freq = {};
        words.forEach(w => freq[w] = (freq[w] || 0) + 1);
        const sorted = Object.keys(freq).sort((a, b) => freq[b] - freq[a]);
        return sorted.slice(0, 5).join(', ');
      }

      const detectedCompetency = detectCoreCompetency(performanceText);
      const extractedKeywords = extractKeywords(contentStandardText, indicatorsText.join(' '), performanceText);

      // Fill form fields
      document.querySelector('[name="strand"]').value = strand;
      document.querySelector('[name="sub_strand"]').value = subStrand;
      document.querySelector('[name="content_standard_code"]').value = contentStandardCode;
      document.querySelector('[name="indicator_code"]').value = indicatorCodes.join(', ');
      document.querySelector('[name="performance_indicator"]').value = performanceText;
      document.querySelector('[name="core_competencies"]').value = detectedCompetency;
      document.querySelector('[name="keywords"]').value = extractedKeywords;
      
      // Show success message
      showToast('success', 'AI Analysis Complete', 'Curriculum successfully analyzed and form fields populated!');
    }

    function startListening(fieldId) {
      if (!('webkitSpeechRecognition' in window)) {
        showToast('error', 'Browser Not Supported', 'Your browser does not support speech recognition.');
        return;
      }

      const recognition = new webkitSpeechRecognition();
      recognition.lang = "en-US";
      recognition.interimResults = true;
      recognition.continuous = true;

      const textarea = document.getElementById(fieldId);
      textarea.placeholder = "🎤 Listening... Speak naturally...";

      let finalTranscript = '';

      recognition.onresult = function(event) {
        let interimTranscript = '';
        for (let i = event.resultIndex; i < event.results.length; ++i) {
          let transcript = event.results[i][0].transcript.trim().toLowerCase();
          transcript = transcript
            .replace(/\b(full stop|period)\b/g, ".")
            .replace(/\b(comma)\b/g, ",")
            .replace(/\b(question mark)\b/g, "?")
            .replace(/\b(exclamation mark|exclamation point)\b/g, "!")
            .replace(/\b(colon)\b/g, ":")
            .replace(/\b(semi colon|semicolon)\b/g, ";");
          if (/\b(clear all|delete all)\b/.test(transcript)) {
            finalTranscript = "";
            transcript = "";
          } else if (/\berase\b/.test(transcript)) {
            let words = finalTranscript.trim().split(" ");
            words.pop();
            finalTranscript = words.join(" ");
            transcript = "";
          }
          if (event.results[i].isFinal) {
            finalTranscript += (finalTranscript && transcript ? " " : "") + transcript;
          } else {
            interimTranscript += transcript;
          }
        }
        textarea.value = (finalTranscript + " " + interimTranscript).trim();
        textarea.scrollTop = textarea.scrollHeight;
      };
      recognition.onerror = (event) => console.error("Speech recognition error:", event.error);
      recognition.onend = () => recognition.start();
      recognition.start();
      
      showToast('info', 'Voice Input Active', 'Speak now to add text to the field.');
    }
  </script>
</body>
</html>
