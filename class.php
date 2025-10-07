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
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check login
if (!isset($_SESSION['student_username'])) {
    header("Location: login.php");
    exit();
}

$student_username = $_SESSION['student_username'];

// Fetch student info
$stmt = $pdo->prepare("SELECT * FROM student_account WHERE username = :username");
$stmt->execute(['username' => $student_username]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) {
    echo "Student not found!";
    exit();
}

$class = $student['class'] ?? null;
if (!$class) {
    echo "Your class is not assigned. Contact the administrator.";
    exit();
}

// Fetch tests for student class (assuming subject = class)
$stmt = $pdo->prepare("SELECT * FROM tests WHERE subject = :class");
$stmt->execute(['class' => $class]);
$tests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<title><?php echo htmlspecialchars($student['username']); ?> - Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #2c3e50;
        --primary-dark: #1a252f;
        --primary-light: #34495e;
        --secondary: #3498db;
        --accent: #9b59b6;
        --success: #27ae60;
        --warning: #f39c12;
        --danger: #e74c3c;
        --info: #17a2b8;
        --light: #ecf0f1;
        --dark: #2c3e50;
        --text-dark: #2c3e50;
        --text-medium: #5d6d7e;
        --text-light: #7f8c8d;
        --card-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        --hover-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        --transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        --safe-top: env(safe-area-inset-top);
        --safe-bottom: env(safe-area-inset-bottom);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html {
        scroll-behavior: smooth;
    }

    body {
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #1a2a3a, #2c3e50);
        color: var(--text-dark);
        line-height: 1.6;
        min-height: 100vh;
        padding: 16px;
        padding-top: calc(16px + var(--safe-top));
        padding-bottom: calc(16px + var(--safe-bottom));
        -webkit-font-smoothing: antialiased;
        -webkit-tap-highlight-color: transparent;
        overflow-x: hidden;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding: 20px;
        background: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 6px;
        height: 100%;
        background: linear-gradient(to bottom, var(--secondary), var(--accent));
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .logo-icon {
        font-size: 24px;
        color: var(--secondary);
        background: rgba(52, 152, 219, 0.1);
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .logo-text {
        font-size: 20px;
        font-weight: 800;
        color: var(--text-dark);
        letter-spacing: -0.5px;
    }

    .user-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid var(--secondary);
        box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
        flex-shrink: 0;
    }

    .user-details {
        text-align: right;
    }

    .user-name {
        font-weight: 700;
        color: var(--text-dark);
        font-size: 16px;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .user-class {
        font-size: 13px;
        color: var(--text-medium);
        background: rgba(52, 152, 219, 0.1);
        padding: 4px 10px;
        border-radius: 16px;
        display: inline-block;
        font-weight: 500;
    }

    /* Enhanced Carousel Styles - Mobile Optimized */
    .carousel-container {
        position: relative;
        width: 100%;
        height: 200px;
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: var(--card-shadow);
        touch-action: pan-y;
    }

    .carousel-slides {
        display: flex;
        width: 500%;
        height: 100%;
        transition: transform 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    .carousel-slide {
        width: 20%;
        height: 100%;
        position: relative;
        flex-shrink: 0;
    }

    .carousel-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .carousel-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        padding: 16px;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: white;
    }

    .carousel-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 6px;
        line-height: 1.2;
    }

    .carousel-description {
        font-size: 13px;
        max-width: 100%;
        opacity: 0.9;
        line-height: 1.4;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .carousel-nav {
        position: absolute;
        bottom: 12px;
        left: 50%;
        transform: translateX(-50%);
        display: flex;
        gap: 8px;
        z-index: 5;
    }

    .carousel-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        cursor: pointer;
        transition: var(--transition);
        flex-shrink: 0;
    }

    .carousel-dot.active {
        background: white;
        transform: scale(1.2);
    }

    .carousel-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.9);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: var(--text-dark);
        cursor: pointer;
        transition: var(--transition);
        box-shadow: 0 3px 10px rgba(0,0,0,0.15);
        z-index: 10;
        opacity: 0;
    }

    .carousel-container:hover .carousel-arrow {
        opacity: 1;
    }

    .carousel-arrow:hover {
        background: white;
        transform: translateY(-50%) scale(1.1);
    }

    .carousel-arrow.prev {
        left: 12px;
    }

    .carousel-arrow.next {
        right: 12px;
    }

    .hero-cards {
        display: grid;
        grid-template-columns: 1fr;
        gap: 16px;
        margin-bottom: 24px;
    }

    .hero-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        display: flex;
        align-items: center;
        gap: 16px;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .hero-card:hover, .hero-card:focus {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }

    .hero-card:active {
        transform: translateY(-2px);
    }

    .hero-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: linear-gradient(to right, var(--secondary), var(--accent));
    }

    .hero-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        color: white;
        flex-shrink: 0;
    }

    .hero-card:nth-child(1) .hero-icon {
        background: linear-gradient(135deg, var(--secondary), #2980b9);
    }

    .hero-card:nth-child(2) .hero-icon {
        background: linear-gradient(135deg, var(--accent), #8e44ad);
    }

    .hero-card:nth-child(3) .hero-icon {
        background: linear-gradient(135deg, var(--success), #229954);
    }

    .hero-content h3 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-dark);
        margin-bottom: 6px;
        line-height: 1.2;
    }

    .hero-content p {
        font-size: 13px;
        color: var(--text-medium);
        line-height: 1.4;
    }

    .dashboard-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .sidebar {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        height: fit-content;
        order: 2;
    }

    .sidebar-title {
        font-size: 18px;
        font-weight: 700;
        margin-bottom: 20px;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 12px;
        border-bottom: 2px solid var(--light);
    }

    .sidebar-title i {
        color: var(--secondary);
    }

    .stats-container {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 16px;
        background: var(--light);
        border-radius: 12px;
        transition: var(--transition);
        border-left: 4px solid var(--secondary);
        min-height: 56px;
    }

    .stat-item:hover {
        background: #e1e8ed;
        transform: translateX(3px);
    }

    .stat-label {
        font-size: 14px;
        color: var(--text-medium);
        font-weight: 500;
    }

    .stat-value {
        font-weight: 700;
        color: var(--text-dark);
        font-size: 16px;
    }

    .main-content {
        display: flex;
        flex-direction: column;
        gap: 24px;
        order: 1;
    }

    .welcome-card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .welcome-card::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 120px;
        height: 120px;
        background: radial-gradient(circle, rgba(52, 152, 219, 0.1) 0%, rgba(52, 152, 219, 0) 70%);
        border-radius: 50%;
        transform: translate(60px, -60px);
    }

    .welcome-title {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 10px;
        color: var(--text-dark);
        line-height: 1.3;
    }

    .welcome-subtitle {
        font-size: 15px;
        color: var(--text-medium);
        margin-bottom: 20px;
        max-width: 100%;
        font-weight: 500;
        line-height: 1.5;
    }

    .date-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-top: 16px;
    }

    .date-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: var(--text-medium);
        background: var(--light);
        padding: 10px 14px;
        border-radius: 10px;
        font-weight: 500;
        min-height: 44px;
    }

    .date-item i {
        color: var(--secondary);
        font-size: 14px;
        width: 16px;
        text-align: center;
    }

    .cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 16px;
    }

    .card {
        background: rgba(255, 255, 255, 0.98);
        border-radius: 14px;
        padding: 20px 16px;
        text-align: center;
        color: var(--dark);
        text-decoration: none;
        transition: var(--transition);
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 140px;
        position: relative;
        overflow: hidden;
        min-width: 0;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }

    .card:hover, .card:focus {
        transform: translateY(-5px);
        box-shadow: var(--hover-shadow);
    }

    .card:active {
        transform: translateY(-2px);
    }

    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
    }

    .card-homework::before {
        background: linear-gradient(to right, var(--secondary), #2980b9);
    }

    .card-test::before {
        background: linear-gradient(to right, var(--accent), #8e44ad);
    }

    .card-project::before {
        background: linear-gradient(to right, var(--success), #229954);
    }

    .card-performance::before {
        background: linear-gradient(to right, var(--warning), #e67e22);
    }

    .card-history::before {
        background: linear-gradient(to right, var(--danger), #c0392b);
    }

    .card-scores::before {
        background: linear-gradient(to right, var(--info), #148f77);
    }

    .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        font-size: 20px;
        color: white;
        flex-shrink: 0;
    }

    .card-homework .card-icon {
        background: linear-gradient(135deg, var(--secondary), #2980b9);
    }

    .card-test .card-icon {
        background: linear-gradient(135deg, var(--accent), #8e44ad);
    }

    .card-project .card-icon {
        background: linear-gradient(135deg, var(--success), #229954);
    }

    .card-performance .card-icon {
        background: linear-gradient(135deg, var(--warning), #e67e22);
    }

    .card-history .card-icon {
        background: linear-gradient(135deg, var(--danger), #c0392b);
    }

    .card-scores .card-icon {
        background: linear-gradient(135deg, var(--info), #148f77);
    }

    .card-title {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 6px;
        color: var(--text-dark);
        line-height: 1.2;
    }

    .card-description {
        font-size: 12px;
        color: var(--text-medium);
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .logout-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        background: linear-gradient(135deg, var(--danger), #c0392b);
        color: white;
        padding: 14px 20px;
        border-radius: 12px;
        text-decoration: none;
        font-weight: 600;
        transition: var(--transition);
        margin-top: 20px;
        border: none;
        cursor: pointer;
        font-family: 'Inter', sans-serif;
        font-size: 14px;
        box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
        width: 100%;
        min-height: 48px;
    }

    .logout-btn:hover, .logout-btn:focus {
        transform: translateY(-3px);
        box-shadow: 0 6px 16px rgba(231, 76, 60, 0.4);
    }

    .logout-btn:active {
        transform: translateY(-1px);
    }

    /* Mobile-first responsive improvements */
    @media (max-width: 480px) {
        .dashboard-header {
            flex-direction: column;
            gap: 16px;
            text-align: center;
        }
        
        .user-info {
            flex-direction: column;
        }
        
        .user-details {
            text-align: center;
        }
        
        .carousel-container {
            height: 180px;
        }
        
        .carousel-title {
            font-size: 16px;
        }
        
        .carousel-description {
            font-size: 12px;
        }
        
        .hero-card {
            flex-direction: column;
            text-align: center;
        }
        
        .hero-content h3 {
            font-size: 15px;
        }
        
        .hero-content p {
            font-size: 12px;
        }
        
        .cards-grid {
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }
        
        .card {
            height: 130px;
            padding: 16px 12px;
        }
        
        .card-icon {
            width: 40px;
            height: 40px;
            font-size: 18px;
            margin-bottom: 8px;
        }
        
        .card-title {
            font-size: 13px;
        }
        
        .card-description {
            font-size: 11px;
        }
    }

    /* Tablet Styles */
    @media (min-width: 768px) {
        body {
            padding: 20px;
        }
        
        .dashboard-header {
            padding: 24px;
            margin-bottom: 28px;
        }
        
        .carousel-container {
            height: 280px;
            margin-bottom: 28px;
        }
        
        .carousel-overlay {
            padding: 24px;
        }
        
        .carousel-title {
            font-size: 22px;
        }
        
        .carousel-description {
            font-size: 14px;
        }
        
        .hero-cards {
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }
        
        .hero-card {
            padding: 24px;
        }
        
        .dashboard-content {
            gap: 28px;
        }
        
        .sidebar {
            padding: 24px;
        }
        
        .welcome-card {
            padding: 28px;
        }
        
        .date-info {
            flex-direction: row;
            gap: 16px;
        }
        
        .cards-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
        }
        
        .card {
            height: 160px;
            padding: 24px 16px;
        }
        
        .logout-btn {
            width: auto;
            align-self: flex-start;
        }
    }

    /* Desktop Styles */
    @media (min-width: 1024px) {
        .dashboard-content {
            flex-direction: row;
        }
        
        .sidebar {
            width: 300px;
            order: 1;
        }
        
        .main-content {
            flex: 1;
            order: 2;
        }
        
        .carousel-container {
            height: 320px;
        }
        
        .carousel-arrow {
            opacity: 1;
        }
    }

    /* Large Desktop Styles */
    @media (min-width: 1200px) {
        .carousel-container {
            height: 360px;
        }
        
        .cards-grid {
            grid-template-columns: repeat(3, 1fr);
        }
    }

    /* Accessibility improvements */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
        
        .carousel-slides {
            transition: none;
        }
    }

    /* Focus styles for better accessibility */
    button:focus-visible,
    a:focus-visible,
    .card:focus-visible,
    .hero-card:focus-visible {
        outline: 2px solid var(--secondary);
        outline-offset: 2px;
    }
</style>
</head>
<body>

<div class="dashboard-container">
    <header class="dashboard-header">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <span class="logo-text">Eduprosuite 2.0</span>
        </div>
        <div class="user-info">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="User Avatar" class="user-avatar">
            <div class="user-details">
                <div class="user-name"><?php echo htmlspecialchars($student['username']); ?></div>
                <div class="user-class">Class: <?php echo htmlspecialchars($student['class']); ?></div>
            </div>
        </div>
    </header>

    <!-- Enhanced Image Carousel -->
    <div class="carousel-container">
        <div class="carousel-slides">
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80" alt="Digital Resources" class="carousel-image">
                <div class="carousel-overlay">
                    <h2 class="carousel-title">Digital Resources</h2>
                    <p class="carousel-description">Access a wealth of digital materials, from interactive textbooks to video lectures and practice tests.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80" alt="Collaborative Learning" class="carousel-image">
                <div class="carousel-overlay">
                    <h2 class="carousel-title">Collaborative Learning</h2>
                    <p class="carousel-description">Work together with peers and instructors to enhance your understanding and achieve better results.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1434030216411-0b793f4b4173?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Online Resources" class="carousel-image">
                <div class="carousel-overlay">
                    <h2 class="carousel-title">Online Resources</h2>
                    <p class="carousel-description">Access a wealth of digital materials, from interactive textbooks to video lectures and practice tests.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2022&q=80" alt="Academic Success" class="carousel-image">
                <div class="carousel-overlay">
                    <h2 class="carousel-title">Track Your Progress</h2>
                    <p class="carousel-description">Monitor your academic growth with detailed analytics and personalized feedback from instructors.</p>
                </div>
            </div>
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Study Environment" class="carousel-image">
                <div class="carousel-overlay">
                    <h2 class="carousel-title">Study Environment</h2>
                    <p class="carousel-description">Create the perfect study environment with our tools and resources tailored to your learning style.</p>
                </div>
            </div>
        </div>
        
        <div class="carousel-arrow prev" aria-label="Previous slide">
            <i class="fas fa-chevron-left"></i>
        </div>
        <div class="carousel-arrow next" aria-label="Next slide">
            <i class="fas fa-chevron-right"></i>
        </div>
        
        <div class="carousel-nav">
            <div class="carousel-dot active" data-slide="0" aria-label="Go to slide 1"></div>
            <div class="carousel-dot" data-slide="1" aria-label="Go to slide 2"></div>
            <div class="carousel-dot" data-slide="2" aria-label="Go to slide 3"></div>
            <div class="carousel-dot" data-slide="3" aria-label="Go to slide 4"></div>
            <div class="carousel-dot" data-slide="4" aria-label="Go to slide 5"></div>
        </div>
    </div>

    <div class="hero-cards">
        <div class="hero-card" onclick="window.location.href='performance.php'">
            <div class="hero-icon">
                <i class="fas fa-trophy"></i>
            </div>
            <div class="hero-content">
                <h3>Academic Excellence</h3>
                <p>Your current performance places you in the top 15% of your class. Keep up the great work!</p>
            </div>
        </div>
        <div class="hero-card" onclick="window.location.href='homework.php'">
            <div class="hero-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="hero-content">
                <h3>Upcoming Deadlines</h3>
                <p>You have 3 assignments due this week. Stay on track with your schedule.</p>
            </div>
        </div>
        <div class="hero-card" onclick="window.location.href='performance.php'">
            <div class="hero-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="hero-content">
                <h3>Performance Trend</h3>
                <p>Your scores have improved by 8% compared to last month. Consistent growth!</p>
            </div>
        </div>
    </div>

    <div class="dashboard-content">
        <aside class="sidebar">
            <h2 class="sidebar-title"><i class="fas fa-chart-bar"></i> Academic Metrics</h2>
            <div class="stats-container">
                <div class="stat-item">
                    <span class="stat-label">Assignments Due</span>
                    <span class="stat-value">3</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Tests This Week</span>
                    <span class="stat-value">2</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Avg. Score</span>
                    <span class="stat-value">87%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Active Projects</span>
                    <span class="stat-value">1</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Attendance</span>
                    <span class="stat-value">96%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Class Rank</span>
                    <span class="stat-value">12/85</span>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <section class="welcome-card">
                <h1 class="welcome-title">Welcome back, <?php echo htmlspecialchars($student['username']); ?>!</h1>
                <p class="welcome-subtitle">Here's an overview of your academic progress, upcoming tasks, and performance metrics. You're doing great - keep pushing forward!</p>
                
                <div class="date-info">
                    <div class="date-item">
                        <i class="far fa-calendar"></i>
                        <span><?php echo date('F j, Y'); ?></span>
                    </div>
                    <div class="date-item">
                        <i class="far fa-clock"></i>
                        <span><?php echo date('g:i A'); ?></span>
                    </div>
                    <div class="date-item">
                        <i class="fas fa-bell"></i>
                        <span>3 New Notifications</span>
                    </div>
                </div>
            </section>

            <section class="cards-grid">
                <a href="homework.php" class="card card-homework">
                    <div class="card-icon">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="card-title">Homework</h3>
                    <p class="card-description">View and submit assignments with deadlines</p>
                </a>
                
                <a href="online-test.php" class="card card-test">
                    <div class="card-icon">
                        <i class="fas fa-laptop-code"></i>
                    </div>
                    <h3 class="card-title">Online Test</h3>
                    <p class="card-description">Take scheduled assessments and quizzes</p>
                </a>
                
                <a href="project-work.php" class="card card-project">
                    <div class="card-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3 class="card-title">Project Work</h3>
                    <p class="card-description">Manage your ongoing academic projects</p>
                </a>
                
                <a href="performance.php" class="card card-performance">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h3 class="card-title">Performance</h3>
                    <p class="card-description">Track your academic progress and growth</p>
                </a>
                
                <a href="test-history.php" class="card card-history">
                    <div class="card-icon">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="card-title">Test History</h3>
                    <p class="card-description">Review past assessments and results</p>
                </a>
                
                <a href="previous-scores.php" class="card card-scores">
                    <div class="card-icon">
                        <i class="fas fa-medal"></i>
                    </div>
                    <h3 class="card-title">Previous Scores</h3>
                    <p class="card-description">View your academic achievements</p>
                </a>
            </section>
            
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </main>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const slides = document.querySelector('.carousel-slides');
        const dots = document.querySelectorAll('.carousel-dot');
        const prevBtn = document.querySelector('.carousel-arrow.prev');
        const nextBtn = document.querySelector('.carousel-arrow.next');
        let currentSlide = 0;
        const totalSlides = 5;
        let autoSlideInterval;
        let isTransitioning = false;
        
        // Function to update carousel position
        function updateCarousel() {
            if (isTransitioning) return;
            
            isTransitioning = true;
            slides.style.transform = `translateX(-${currentSlide * 20}%)`;
            
            // Update active dot
            dots.forEach((dot, index) => {
                if (index === currentSlide) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
            
            // Reset transition flag after animation completes
            setTimeout(() => {
                isTransitioning = false;
            }, 600);
        }
        
        // Next slide function
        function nextSlide() {
            if (isTransitioning) return;
            currentSlide = (currentSlide + 1) % totalSlides;
            updateCarousel();
        }
        
        // Previous slide function
        function prevSlide() {
            if (isTransitioning) return;
            currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
            updateCarousel();
        }
        
        // Event listeners for arrows
        nextBtn.addEventListener('click', nextSlide);
        prevBtn.addEventListener('click', prevSlide);
        
        // Event listeners for dots
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                if (isTransitioning) return;
                currentSlide = index;
                updateCarousel();
                resetAutoSlide();
            });
        });
        
        // Enhanced touch swipe support for mobile
        let startX = 0;
        let endX = 0;
        let isSwiping = false;
        
        const carouselContainer = document.querySelector('.carousel-container');
        
        carouselContainer.addEventListener('touchstart', (e) => {
            startX = e.touches[0].clientX;
            isSwiping = true;
        });
        
        carouselContainer.addEventListener('touchmove', (e) => {
            if (!isSwiping) return;
            endX = e.touches[0].clientX;
        });
        
        carouselContainer.addEventListener('touchend', () => {
            if (!isSwiping) return;
            handleSwipe();
            isSwiping = false;
        });
        
        function handleSwipe() {
            const swipeThreshold = 50;
            
            if (startX - endX > swipeThreshold) {
                // Swipe left - next slide
                nextSlide();
                resetAutoSlide();
            } else if (endX - startX > swipeThreshold) {
                // Swipe right - previous slide
                prevSlide();
                resetAutoSlide();
            }
        }
        
        // Auto-advance slides
        function startAutoSlide() {
            autoSlideInterval = setInterval(nextSlide, 5000);
        }
        
        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            startAutoSlide();
        }
        
        // Pause auto-slide on hover (for desktop)
        carouselContainer.addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });
        
        carouselContainer.addEventListener('mouseleave', () => {
            startAutoSlide();
        });
        
        // Pause auto-slide on touch (for mobile)
        carouselContainer.addEventListener('touchstart', () => {
            clearInterval(autoSlideInterval);
        });
        
        carouselContainer.addEventListener('touchend', () => {
            setTimeout(startAutoSlide, 3000);
        });
        
        // Start auto-slide
        startAutoSlide();
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                prevSlide();
                resetAutoSlide();
            } else if (e.key === 'ArrowRight') {
                nextSlide();
                resetAutoSlide();
            }
        });
        
        // Make hero cards clickable
        const heroCards = document.querySelectorAll('.hero-card');
        heroCards.forEach(card => {
            card.addEventListener('click', function() {
                const url = this.getAttribute('onclick')?.match(/window\.location\.href='([^']+)'/)?.[1];
                if (url) {
                    window.location.href = url;
                }
            });
            
            // Add keyboard support for hero cards
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    const url = this.getAttribute('onclick')?.match(/window\.location\.href='([^']+)'/)?.[1];
                    if (url) {
                        window.location.href = url;
                    }
                }
            });
        });
    });
</script>

</body>
</html>
