<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../include/functions.php";

// Check session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$session_id = $_SESSION["id"] ?? '';

if ($session_id == "") {
    header("Location: ../index.php?error=Invalid username or password");
    exit();
}

$conn = db_conn();

// Get total students count
$stmt = $conn->query("SELECT COUNT(name) as 'tstudents' FROM student");
$row = $stmt->fetchArray(SQLITE3_ASSOC);
$totalStudents = $row['tstudents'] ?? 0;

// Get total classes count
$stmt = $conn->query("SELECT COUNT(DISTINCT class) as 'tclasses' FROM student");
$row = $stmt->fetchArray(SQLITE3_ASSOC);
$totalClasses = $row['tclasses'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educator Dashboard | Adinkra International School</title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.css" rel="stylesheet" />
    
    <style>
        :root {
            /* Color System */
            --primary: #4f46e5;
            --primary-light: #6366f1;
            --primary-lighter: #a5b4fc;
            --primary-dark: #4338ca;
            --primary-darker: #3730a3;
            
            --secondary: #7c3aed;
            --secondary-light: #8b5cf6;
            --secondary-lighter: #c4b5fd;
            
            --success: #10b981;
            --success-light: #34d399;
            --success-lighter: #a7f3d0;
            
            --info: #0ea5e9;
            --info-light: #38bdf8;
            --info-lighter: #bae6fd;
            
            --warning: #f59e0b;
            --warning-light: #fbbf24;
            --warning-lighter: #fde68a;
            
            --danger: #ef4444;
            --danger-light: #f87171;
            --danger-lighter: #fecaca;
            
            --light: #f8fafc;
            --dark: #1e293b;
            --darker: #0f172a;
            
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-400: #94a3b8;
            --gray-500: #64748b;
            --gray-600: #475569;
            --gray-700: #334155;
            --gray-800: #1e293b;
            --gray-900: #0f172a;
            
            /* Dark Mode Colors */
            --dark-primary: #818cf8;
            --dark-primary-light: rgba(129, 140, 248, 0.1);
            --dark-primary-dark: #6366f1;
            
            --dark-bg: #0f172a;
            --dark-card: #1e293b;
            --dark-text: #f8fafc;
            --dark-border: #334155;
            
            /* Elevation */
            --shadow-xs: 0 1px 2px 0 rgba(15, 23, 42, 0.05);
            --shadow-sm: 0 1px 3px 0 rgba(15, 23, 42, 0.1), 0 1px 2px 0 rgba(15, 23, 42, 0.06);
            --shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.1), 0 2px 4px -1px rgba(15, 23, 42, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(15, 23, 42, 0.1), 0 4px 6px -2px rgba(15, 23, 42, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 10px 10px -5px rgba(15, 23, 42, 0.04);
            --shadow-xl: 0 25px 50px -12px rgba(15, 23, 42, 0.25);
            --shadow-2xl: 0 35px 60px -15px rgba(15, 23, 42, 0.3);
            --shadow-primary: 0 4px 14px 0 rgba(79, 70, 229, 0.3);
            
            /* Border Radius */
            --rounded-xs: 4px;
            --rounded-sm: 6px;
            --rounded: 8px;
            --rounded-md: 12px;
            --rounded-lg: 16px;
            --rounded-xl: 20px;
            --rounded-2xl: 24px;
            --rounded-3xl: 32px;
            --rounded-full: 9999px;
            
            /* Transitions */
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.5s cubic-bezier(0.68, -0.6, 0.32, 1.6);
            
            /* Sidebar */
            --sidebar-width: 300px;
            --sidebar-collapsed-width: 90px;
            --sidebar-bg: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            --sidebar-text: #f8fafc;
            --sidebar-active-bg: rgba(79, 70, 229, 0.2);
            --sidebar-active-border: #4f46e5;
            --sidebar-hover-bg: rgba(79, 70, 229, 0.1);
            --sidebar-logo-size: 120px;
        }

        /* Dark Mode Variables */
        [data-theme="dark"] {
            --primary: var(--dark-primary);
            --primary-light: var(--dark-primary-light);
            --primary-dark: var(--dark-primary-dark);
            
            --light: var(--dark-bg);
            --dark: var(--dark-text);
            --darker: var(--dark-text);
            
            --gray-50: var(--dark-bg);
            --gray-100: var(--dark-card);
            --gray-200: var(--dark-border);
            --gray-300: #334155;
            --gray-400: #64748b;
            --gray-500: #94a3b8;
            --gray-600: #cbd5e1;
            --gray-700: #e2e8f0;
            --gray-800: #f1f5f9;
            --gray-900: #f8fafc;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            height: 100%;
            width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background-color: var(--gray-50);
            color: var(--dark);
            line-height: 1.6;
            font-size: 16px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
            display: flex;
        }

        /* Skip to content link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 0;
            padding: 8px 16px;
            background: var(--primary);
            color: white;
            z-index: 100;
            border-radius: var(--rounded-md);
            transition: top 0.3s;
            font-weight: 500;
        }
        
        .skip-link:focus {
            top: 20px;
            outline: 2px solid var(--primary-dark);
            outline-offset: 2px;
        }

        /* Sidebar Styles - Premium Redesign */
        #sidebar {
            width: var(--sidebar-width);
            min-width: var(--sidebar-width);
            background: var(--sidebar-bg);
            color: var(--sidebar-text);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            z-index: 1000;
            height: 100vh;
            overflow-y: auto;
            box-shadow: var(--shadow-xl);
            border-right: 1px solid var(--dark-border);
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
            min-width: var(--sidebar-collapsed-width);
        }

        #sidebar.collapsed .sidebar-header h4,
        #sidebar.collapsed .nav-link-text,
        #sidebar.collapsed .footer p,
        #sidebar.collapsed .dropdown-toggle::after {
            display: none;
        }

        #sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 14px 0;
        }

        #sidebar.collapsed .nav-item {
            position: relative;
        }

        #sidebar.collapsed .nav-item:hover .nav-link-text {
            display: block;
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: var(--sidebar-bg);
            padding: 10px 18px;
            border-radius: var(--rounded-md);
            margin-left: 12px;
            white-space: nowrap;
            box-shadow: var(--shadow-lg);
            z-index: 1000;
            font-size: 14px;
            font-weight: 500;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .sidebar-header::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 20px;
            right: 20px;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(79, 70, 229, 0.5), transparent);
        }

        .sidebar-logo {
            width: var(--sidebar-logo-size);
            height: var(--sidebar-logo-size);
            border-radius: 50%;
            margin: 0 auto 20px;
            background-size: cover;
            background-position: center;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.2);
            transition: var(--transition-slow);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: white;
        }

        .sidebar-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-header h4 {
            color: white;
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0;
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.5px;
            background: linear-gradient(90deg, #fff 0%, #c7d2fe 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .sidebar-header h4 span {
            color: #a5b4fc;
        }

        .sidebar-content {
            padding: 25px 0;
        }

        .nav-item {
            position: relative;
            margin-bottom: 2px;
        }

        .nav-link {
            color: var(--sidebar-text);
            padding: 14px 25px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: var(--transition);
            opacity: 0.9;
            font-weight: 500;
            font-size: 15px;
            margin: 0 10px;
            border-radius: var(--rounded-md);
        }

        .nav-link:hover {
            opacity: 1;
            background: var(--sidebar-hover-bg);
            transform: translateX(4px);
        }

        .nav-link.active {
            background: var(--sidebar-active-bg);
            opacity: 1;
            border-left: 4px solid var(--sidebar-active-border);
            font-weight: 600;
        }

        .nav-link i {
            margin-right: 15px;
            font-size: 1.1rem;
            width: 24px;
            text-align: center;
            color: #a5b4fc;
        }

        .nav-link.active i {
            color: var(--primary-light);
        }

        .dropdown-toggle::after {
            margin-left: auto;
            transition: transform 0.2s;
            border-top: 0.35em solid;
            border-right: 0.35em solid transparent;
            border-left: 0.35em solid transparent;
            color: #a5b4fc;
        }

        .nav-item.show .dropdown-toggle::after {
            transform: rotate(180deg);
        }

        .collapse:not(.show) {
            display: none;
        }

        .nav.flex-column .nav {
            padding-left: 30px;
        }

        .nav.flex-column .nav .nav-link {
            padding: 10px 20px;
            font-size: 0.9rem;
            margin: 0;
            border-radius: var(--rounded-sm);
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .footer a {
            color: #a5b4fc;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .footer a:hover {
            color: var(--primary-light);
        }

        /* Main Content Area */
        #main-content {
            flex: 1;
            overflow-y: auto;
            height: 100vh;
            transition: margin-left 0.3s ease;
            background-color: var(--gray-50);
        }

        /* Premium Top Navigation Bar */
        .top-navbar {
            height: 80px;
            background: white;
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: sticky;
            top: 0;
            z-index: 900;
            border-bottom: 1px solid var(--gray-200);
        }

        [data-theme="dark"] .top-navbar {
            background: var(--dark-card);
            border-bottom-color: var(--dark-border);
        }

        .navbar-left {
            display: flex;
            align-items: center;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.3rem;
            color: var(--gray-600);
            cursor: pointer;
            margin-right: 20px;
            padding: 10px;
            border-radius: var(--rounded-full);
            transition: var(--transition);
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        [data-theme="dark"] .sidebar-toggle {
            color: var(--gray-400);
        }

        .sidebar-toggle:hover {
            background: var(--gray-100);
            color: var(--primary);
            transform: rotate(90deg);
        }

        [data-theme="dark"] .sidebar-toggle:hover {
            background: var(--dark-border);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 15px;
            color: var(--gray-600);
        }

        .breadcrumb-item {
            color: var(--gray-600);
            transition: var(--transition);
            text-decoration: none;
            font-weight: 500;
        }

        .breadcrumb-item:hover {
            color: var(--primary);
        }

        .breadcrumb-item.active {
            color: var(--primary);
            font-weight: 600;
        }

        .breadcrumb-divider {
            margin: 0 10px;
            color: var(--gray-400);
            font-size: 12px;
            opacity: 0.6;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* Premium User Profile Section */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notifications {
            position: relative;
            font-size: 18px;
            color: var(--gray-600);
            cursor: pointer;
            transition: var(--transition);
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--rounded-full);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-sm);
        }

        [data-theme="dark"] .notifications {
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(30, 41, 59, 0.3);
            color: var(--gray-400);
        }

        .notifications:hover, .notifications:focus {
            color: var(--dark);
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            outline: none;
        }

        [data-theme="dark"] .notifications:hover {
            background: rgba(30, 41, 59, 0.95);
            color: var(--gray-300);
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: var(--danger);
            color: white;
            border-radius: var(--rounded-full);
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
            border: 2px solid white;
        }

        [data-theme="dark"] .notification-badge {
            border-color: var(--dark-card);
        }

        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.5);
            }
            70% {
                box-shadow: 0 0 0 12px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .profile-dropdown {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 10px 16px 10px 12px;
            border-radius: var(--rounded-xl);
            cursor: pointer;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: var(--shadow-sm);
        }

        [data-theme="dark"] .profile-dropdown {
            background: rgba(30, 41, 59, 0.9);
            border-color: rgba(30, 41, 59, 0.3);
        }

        .profile-dropdown:hover, .profile-dropdown:focus {
            background: rgba(255, 255, 255, 0.95);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            outline: none;
        }

        [data-theme="dark"] .profile-dropdown:hover {
            background: rgba(30, 41, 59, 0.95);
        }

        .profile-avatar {
            width: 44px;
            height: 44px;
            border-radius: var(--rounded-full);
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.8);
            box-shadow: var(--shadow-xs);
        }

        [data-theme="dark"] .profile-avatar {
            border-color: rgba(30, 41, 59, 0.8);
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 700;
            font-size: 16px;
            color: var(--darker);
            line-height: 1.3;
        }

        .user-role {
            font-size: 14px;
            color: var(--secondary);
            line-height: 1.3;
            font-weight: 500;
        }

        .dropdown-arrow {
            font-size: 14px;
            color: var(--secondary);
            transition: transform 0.2s;
        }

        .profile-dropdown:hover .dropdown-arrow, .profile-dropdown:focus .dropdown-arrow {
            transform: rotate(180deg);
        }

        /* Premium Dashboard Content */
        .dashboard-container {
            padding: 0;
            max-width: 1800px;
            margin: 0 auto;
            width: 100%;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                padding: 0;
            }
        }

        /* Mobile App Style Header Slider */
        .header-slider {
            position: relative;
            width: 100%;
            height: 280px;
            overflow: hidden;
            border-radius: 0 0 var(--rounded-2xl) var(--rounded-2xl);
            box-shadow: var(--shadow-lg);
        }

        .slider-container {
            display: flex;
            width: 300%;
            height: 100%;
            transition: transform 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }

        .slider-slide {
            width: 33.333%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .slider-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.7) 0%, rgba(15, 23, 42, 0.4) 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 0 30px;
        }

        .slide-content {
            max-width: 500px;
        }

        .slide-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 32px;
            font-weight: 800;
            color: white;
            margin-bottom: 12px;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .slide-subtitle {
            font-size: 18px;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
            line-height: 1.4;
            font-weight: 500;
        }

        .slide-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 16px;
            border-radius: var(--rounded-full);
            font-size: 14px;
            font-weight: 600;
            margin-top: 10px;
        }

        .slider-indicators {
            position: absolute;
            bottom: 20px;
            left: 0;
            right: 0;
            display: flex;
            justify-content: center;
            gap: 8px;
            z-index: 10;
        }

        .slider-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .slider-indicator.active {
            background: white;
            transform: scale(1.2);
        }

        .slider-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .slider-nav:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-50%) scale(1.1);
        }

        .slider-nav.prev {
            left: 20px;
        }

        .slider-nav.next {
            right: 20px;
        }

        /* Content after slider */
        .dashboard-content {
            padding: 30px;
            margin-top: -40px;
            position: relative;
            z-index: 5;
        }

        /* Premium Metrics Section */
        .metrics-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 24px;
            margin-bottom: 40px;
        }

        @media (max-width: 768px) {
            .metrics-section {
                grid-template-columns: 1fr;
                gap: 16px;
            }
        }

        .metric-card {
            background: white;
            border-radius: var(--rounded-xl);
            padding: 28px;
            transition: var(--transition-slow);
            display: flex;
            gap: 20px;
            align-items: center;
            position: relative;
            overflow: hidden;
            border: none;
            box-shadow: var(--shadow);
            cursor: pointer;
            border-left: 4px solid transparent;
        }

        [data-theme="dark"] .metric-card {
            background: var(--dark-card);
        }

        .metric-card:hover, .metric-card:focus-within {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }

        .student-card {
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.05) 0%, rgba(255,255,255,1) 100%);
            border-left-color: var(--primary);
        }

        [data-theme="dark"] .student-card {
            background: linear-gradient(135deg, rgba(129, 140, 248, 0.1) 0%, var(--dark-card) 100%);
        }

        .classes-card {
            background: linear-gradient(135deg, rgba(14, 165, 233, 0.05) 0%, rgba(255,255,255,1) 100%);
            border-left-color: var(--info);
        }

        [data-theme="dark"] .classes-card {
            background: linear-gradient(135deg, rgba(56, 189, 248, 0.1) 0%, var(--dark-card) 100%);
        }

        .attendance-card {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05) 0%, rgba(255,255,255,1) 100%);
            border-left-color: var(--success);
        }

        [data-theme="dark"] .attendance-card {
            background: linear-gradient(135deg, rgba(52, 211, 153, 0.1) 0%, var(--dark-card) 100%);
        }

        .metric-wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.7), transparent);
            transform: translateX(-100%);
            transition: transform 0.7s ease;
        }

        [data-theme="dark"] .metric-wave {
            background: linear-gradient(90deg, transparent, rgba(30, 41, 59, 0.7), transparent);
        }

        .metric-card:hover .metric-wave, .metric-card:focus-within .metric-wave {
            transform: translateX(100%);
        }

        .student-card .metric-wave {
            background: linear-gradient(90deg, transparent, var(--primary-light), transparent);
        }

        .classes-card .metric-wave {
            background: linear-gradient(90deg, transparent, var(--info-light), transparent);
        }

        .attendance-card .metric-wave {
            background: linear-gradient(90deg, transparent, var(--success-light), transparent);
        }

        .metric-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-bg {
            width: 60px;
            height: 60px;
            border-radius: var(--rounded-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .metric-card:hover .icon-bg {
            transform: scale(1.05);
        }

        .student-card .icon-bg {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .classes-card .icon-bg {
            background: linear-gradient(135deg, var(--info) 0%, #0284c7 100%);
            color: white;
        }

        .attendance-card .icon-bg {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .metric-icon i {
            font-size: 24px;
        }

        .metric-content {
            flex: 1;
        }

        .metric-label {
            display: block;
            font-size: 16px;
            color: var(--gray-600);
            margin-bottom: 8px;
            font-weight: 600;
        }

        .metric-value {
            font-size: 32px;
            font-weight: 800;
            color: var(--darker);
            margin-bottom: 8px;
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.75px;
            line-height: 1.2;
        }

        .metric-trend {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 15px;
            color: var(--success);
            font-weight: 600;
        }

        .metric-trend.negative {
            color: var(--danger);
        }

        .metric-trend i {
            font-size: 15px;
        }

        /* Premium Section Headers */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .section-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--darker);
            margin: 0;
            display: flex;
            align-items: center;
            letter-spacing: -0.5px;
            line-height: 1.3;
        }

        .section-header h2 i {
            margin-right: 14px;
            font-size: 1.1em;
            color: var(--primary);
        }

        .section-actions {
            display: flex;
            gap: 16px;
        }

        /* Premium Quick Actions Section */
        .actions-section {
            background: white;
            border-radius: var(--rounded-xl);
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: var(--shadow);
            transition: var(--transition-slow);
            border: 1px solid var(--gray-200);
        }

        [data-theme="dark"] .actions-section {
            background: var(--dark-card);
            border-color: var(--dark-border);
        }

        .actions-section:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        @media (max-width: 768px) {
            .action-grid {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            }
        }

        @media (max-width: 480px) {
            .action-grid {
                grid-template-columns: 1fr;
            }
        }

        .action-card {
            background: white;
            border-radius: var(--rounded-lg);
            padding: 28px;
            text-decoration: none;
            transition: var(--transition-slow);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        [data-theme="dark"] .action-card {
            background: var(--dark-card);
            border-color: var(--dark-border);
        }

        .action-card:hover, .action-card:focus {
            border-color: var(--primary-light);
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            outline: none;
        }

        .action-card.add-new {
            background: linear-gradient(135deg, rgba(241, 245, 249, 0.5) 0%, rgba(248, 250, 252, 0.5) 100%);
            border: 2px dashed var(--gray-300);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        [data-theme="dark"] .action-card.add-new {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.5) 0%, rgba(15, 23, 42, 0.5) 100%);
            border-color: var(--dark-border);
        }

        .action-card.add-new:hover, .action-card.add-new:focus {
            background: linear-gradient(135deg, rgba(241, 245, 249, 0.7) 0%, rgba(248, 250, 252, 0.7) 100%);
            border-color: var(--primary);
        }

        [data-theme="dark"] .action-card.add-new:hover {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.7) 0%, rgba(15, 23, 42, 0.7) 100%);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: var(--rounded-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            font-size: 24px;
            color: white;
            box-shadow: var(--shadow-sm);
            transition: var(--transition-bounce);
        }

        .action-card:hover .action-icon, .action-card:focus .action-icon {
            transform: scale(1.1) translateY(-5px);
        }

        .action-icon.notes {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        }

        .action-icon.attendance {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        }

        .action-icon.assignments {
            background: linear-gradient(135deg, var(--warning) 0%, #d97706 100%);
        }

        .action-icon.reports {
            background: linear-gradient(135deg, var(--info) 0%, #0284c7 100%);
        }

        .action-icon.emails {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .action-icon.subjects {
            background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        }

        .action-icon.scores {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        .action-icon.behavior {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        }

        .action-icon.add {
            background: linear-gradient(135deg, var(--gray-300) 0%, var(--gray-400) 100%);
            color: var(--gray-700);
        }

        [data-theme="dark"] .action-icon.add {
            color: var(--dark-text);
        }

        .action-card h3 {
            font-size: 20px;
            font-weight: 700;
            color: var(--darker);
            margin-bottom: 12px;
            font-family: 'Space Grotesk', sans-serif;
            line-height: 1.3;
        }

        .action-card p {
            font-size: 16px;
            color: var(--gray-600);
            margin: 0;
            line-height: 1.5;
        }

        .action-hover {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 36px;
            height: 36px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: var(--rounded-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            opacity: 0;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        [data-theme="dark"] .action-hover {
            background: rgba(30, 41, 59, 0.9);
        }

        .action-card:hover .action-hover, .action-card:focus .action-hover {
            opacity: 1;
            transform: translateY(-2px);
        }

        /* Premium Bottom Section Layout */
        .bottom-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1200px) {
            .bottom-section {
                grid-template-columns: 1fr;
            }
        }

        /* Premium Calendar Section */
        .calendar-section {
            background: white;
            border-radius: var(--rounded-xl);
            padding: 30px;
            margin-bottom: 0;
            box-shadow: var(--shadow);
            transition: var(--transition-slow);
            border: 1px solid var(--gray-200);
        }

        [data-theme="dark"] .calendar-section {
            background: var(--dark-card);
            border-color: var(--dark-border);
        }

        .calendar-section:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .fc {
            font-family: 'Inter', sans-serif;
        }

        .fc .fc-toolbar-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--darker);
            font-family: 'Space Grotesk', sans-serif;
        }

        .fc .fc-button {
            background-color: white;
            border: 1px solid var(--gray-200);
            color: var(--dark);
            font-size: 15px;
            font-weight: 600;
            padding: 8px 16px;
            border-radius: var(--rounded-md);
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }

        [data-theme="dark"] .fc .fc-button {
            background-color: var(--dark-card);
            border-color: var(--dark-border);
            color: var(--dark-text);
        }

        .fc .fc-button:hover, .fc .fc-button:focus {
            background-color: var(--gray-100);
            outline: none;
            box-shadow: var(--shadow);
        }

        [data-theme="dark"] .fc .fc-button:hover {
            background-color: var(--dark-bg);
        }

        .fc .fc-button-primary:not(:disabled).fc-button-active {
            background-color: var(--primary);
            border-color: var(--primary);
            color: white;
            box-shadow: var(--shadow-primary);
        }

        .fc .fc-daygrid-day-number {
            color: var(--dark);
            font-weight: 600;
            padding: 6px;
            font-size: 15px;
        }

        .fc .fc-daygrid-day.fc-day-today {
            background-color: var(--primary-light);
        }

        [data-theme="dark"] .fc .fc-daygrid-day.fc-day-today {
            background-color: rgba(79, 70, 229, 0.2);
        }

        .fc .fc-daygrid-event {
            border-radius: var(--rounded-sm);
            padding: 4px 8px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: var(--shadow-xs);
        }

        .fc .fc-daygrid-event-dot {
            display: none;
        }

        /* Premium Activity Section */
        .activity-section {
            background: white;
            border-radius: var(--rounded-xl);
            padding: 30px;
            box-shadow: var(--shadow);
            transition: var(--transition-slow);
            border: 1px solid var(--gray-200);
        }

        [data-theme="dark"] .activity-section {
            background: var(--dark-card);
            border-color: var(--dark-border);
        }

        .activity-section:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }

        .view-all {
            font-size: 16px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-all:hover, .view-all:focus {
            color: var(--primary-dark);
            text-decoration: underline;
            outline: none;
            transform: translateX(2px);
        }

        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .activity-item {
            display: flex;
            gap: 20px;
            padding: 20px;
            border-radius: var(--rounded-lg);
            background: white;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
        }

        [data-theme="dark"] .activity-item {
            background: var(--dark-card);
            border-color: var(--dark-border);
        }

        .activity-item:hover, .activity-item:focus-within {
            background: var(--gray-100);
            transform: translateX(4px);
            box-shadow: var(--shadow-sm);
        }

        .activity-icon {
            width: 48px;
            height: 48px;
            min-width: 48px;
            border-radius: var(--rounded-full);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            flex-shrink: 0;
        }

        .activity-icon.success {
            background-color: var(--success);
            box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.3);
        }

        .activity-icon.primary {
            background-color: var(--primary);
            box-shadow: var(--shadow-primary);
        }

        .activity-icon.info {
            background-color: var(--info);
            box-shadow: 0 4px 14px 0 rgba(14, 165, 233, 0.3);
        }

        .activity-icon.warning {
            background-color: var(--warning);
            box-shadow: 0 4px 14px 0 rgba(245, 158, 11, 0.3);
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .activity-header h3 {
            font-size: 18px;
            font-weight: 700;
            color: var(--darker);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.3;
        }

        .activity-time {
            font-size: 15px;
            color: var(--gray-500);
            white-space: nowrap;
            margin-left: 12px;
            font-weight: 500;
        }

        .activity-content p {
            font-size: 16px;
            color: var(--gray-600);
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .activity-progress {
            height: 6px;
            background: var(--gray-200);
            border-radius: var(--rounded-full);
            margin-top: 12px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: var(--primary);
            border-radius: var(--rounded-full);
            transition: width 0.6s ease;
        }

        /* Premium Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 24px;
            border-radius: var(--rounded-md);
            font-size: 16px;
            font-weight: 600;
            line-height: 1.5;
            cursor: pointer;
            transition: var(--transition);
            border: 1px solid transparent;
            gap: 10px;
            min-height: 48px;
            font-family: 'Inter', sans-serif;
        }

        .btn-sm {
            padding: 10px 18px;
            font-size: 15px;
            min-height: 40px;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
            box-shadow: var(--shadow-md);
            outline: none;
            transform: translateY(-2px);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-outline {
            background-color: white;
            color: var(--gray-600);
            border-color: var(--gray-300);
            box-shadow: var(--shadow-xs);
        }

        .btn-outline:hover, .btn-outline:focus {
            background-color: var(--gray-100);
            color: var(--dark);
            outline: none;
            transform: translateY(-2px);
            box-shadow: var(--shadow-sm);
        }

        .btn-outline:active {
            transform: translateY(0);
        }

        /* Premium Event Modal */
        .event-modal {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            border-radius: var(--rounded-xl);
            padding: 32px;
            width: 90%;
            max-width: 520px;
            z-index: 1001;
            box-shadow: var(--shadow-2xl);
            animation: modalFadeIn 0.3s ease-out;
            border: 1px solid var(--gray-200);
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translate(-50%, -48%);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%);
            }
        }

        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            z-index: 1000;
            animation: overlayFadeIn 0.3s ease-out;
        }

        @keyframes overlayFadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .modal-header h3 {
            font-size: 24px;
            font-weight: 700;
            color: var(--darker);
            line-height: 1.3;
            font-family: 'Space Grotesk', sans-serif;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--gray-500);
            cursor: pointer;
            transition: var(--transition);
            line-height: 1;
            padding: 8px;
            border-radius: var(--rounded-full);
        }

        .close-modal:hover, .close-modal:focus {
            color: var(--dark);
            background: var(--gray-100);
            outline: none;
        }

        .modal-body p {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 20px;
            color: var(--dark);
            font-size: 16px;
            line-height: 1.6;
        }

        .modal-body i {
            width: 24px;
            text-align: center;
            color: var(--primary);
            font-size: 18px;
            margin-top: 2px;
        }

        .modal-actions {
            display: flex;
            gap: 16px;
            margin-top: 28px;
            justify-content: flex-end;
        }

        /* Loading Overlay */
        #loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.95);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        #loading-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        .loading-spinner {
            width: 80px;
            height: 80px;
            background-image: url('logo.png');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
            animation: spin 1.5s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Form Loading State */
        .form-loading {
            position: relative;
        }
        .form-loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 100;
        }
        .form-loading::before {
            content: "";
            width: 30px;
            height: 30px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #4f46e5;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            z-index: 101;
        }

        /* Focus styles for accessibility */
        a:focus, button:focus, [tabindex="0"]:focus {
            outline: 2px solid var(--primary);
            outline-offset: 4px;
            border-radius: var(--rounded-sm);
        }

        /* Responsive Design */
        @media (max-width: 1280px) {
            .dashboard-content {
                padding: 28px;
            }
            
            .metrics-section {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 1024px) {
            .dashboard-content {
                padding: 24px;
            }
            
            .header-content {
                flex-direction: column;
                align-items: flex-start;
                gap: 24px;
            }
            
            .user-profile {
                width: 100%;
                justify-content: space-between;
            }
            
            .action-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .slide-title {
                font-size: 28px;
            }
            
            .metric-value {
                font-size: 28px;
            }
        }

        @media (max-width: 768px) {
            #sidebar {
                position: fixed;
                z-index: 1000;
                transform: translateX(-100%);
            }
            
            #sidebar.collapsed {
                transform: translateX(0);
                width: 80px;
            }
            
            #main-content {
                margin-left: 0;
            }
            
            .top-navbar {
                padding: 0 20px;
            }
            
            .header-slider {
                height: 220px;
            }
            
            .slide-title {
                font-size: 24px;
            }
            
            .slide-subtitle {
                font-size: 16px;
            }
        }

        @media (max-width: 576px) {
            .action-grid {
                grid-template-columns: 1fr;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 16px;
            }
            
            .section-actions {
                width: 100%;
                justify-content: flex-end;
            }
            
            .metric-value {
                font-size: 24px;
            }
            
            .activity-item {
                padding: 16px;
            }
            
            .sidebar-logo {
                width: 80px;
                height: 80px;
            }
            
            .header-slider {
                height: 200px;
            }
            
            .slide-title {
                font-size: 22px;
            }
            
            .slider-nav {
                display: none;
            }
        }

        /* Animation for cards */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .metric-card, .action-card, .activity-item {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .metric-card:nth-child(1) { animation-delay: 0.1s; }
        .metric-card:nth-child(2) { animation-delay: 0.2s; }
        .metric-card:nth-child(3) { animation-delay: 0.3s; }
        .action-card:nth-child(1) { animation-delay: 0.1s; }
        .action-card:nth-child(2) { animation-delay: 0.2s; }
        .action-card:nth-child(3) { animation-delay: 0.3s; }
        .action-card:nth-child(4) { animation-delay: 0.4s; }
        .action-card:nth-child(5) { animation-delay: 0.5s; }
        .action-card:nth-child(6) { animation-delay: 0.6s; }
        .action-card:nth-child(7) { animation-delay: 0.7s; }
        .action-card:nth-child(8) { animation-delay: 0.8s; }
        .activity-item:nth-child(1) { animation-delay: 0.1s; }
        .activity-item:nth-child(2) { animation-delay: 0.2s; }
        .activity-item:nth-child(3) { animation-delay: 0.3s; }
        .activity-item:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div id="loading-overlay">
    <div class="loading-spinner"></div>
</div>

<!-- Skip to content link for keyboard users -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- Premium Sidebar -->
<nav id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="logo.png" alt="Adinkra International School Crest">
        </div>
        <h4>EduPro Suite <span>2.0</span></h4>
    </div>

    <div class="sidebar-content">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="index.php?dashboard" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span class="nav-link-text">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?exams" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span class="nav-link-text">Exams</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="exam_scores.php?exam_scores" class="nav-link">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-link-text">Exam Scores</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?lesson_notes" class="nav-link">
                    <i class="fas fa-book"></i>
                    <span class="nav-link-text">Lesson Notes</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?subjects" class="nav-link">
                    <i class="fas fa-book-open"></i>
                    <span class="nav-link-text">Subjects</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?form" class="nav-link">
                    <i class="fas fa-user-plus"></i>
                    <span class="nav-link-text">Add Students</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?view_students" class="nav-link">
                    <i class="fas fa-eye"></i>
                    <span class="nav-link-text">View Students</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?login" class="nav-link">
                    <i class="fas fa-user-check"></i>
                    <span class="nav-link-text">Attendance</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?lo" class="nav-link">
                    <i class="fas fa-brain"></i>
                    <span class="nav-link-text">Skill Evaluation</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="index.php?logg" class="nav-link">
                    <i class="fas fa-tasks"></i>
                    <span class="nav-link-text">Assignments</span>
                </a>
            </li>
            <li class="nav-item">
    <a href="email_login.php" class="nav-link">
        <i class="fas fa-envelope"></i>
        <span class="nav-link-text">Emails</span>
    </a>
</li>
<li class="nav-item">
    <a href="config_report.php" class="nav-link">
        <i class="fas fa-comments"></i>
        <span class="nav-link-text">configure report</span>
    </a>
</li>

            <!-- Generate Reports Dropdown -->
            <li class="nav-item">
                <a class="nav-link dropdown-toggle" href="#pageSubmenu" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="pageSubmenu">
                    <i class="fas fa-file-invoice"></i>
                    <span class="nav-link-text">Generate Reports</span>
                </a>
                <div class="collapse" id="pageSubmenu">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-toggle="modal" data-target="#GenerateReportCardsModal">
                                <i class="fas fa-file-alt"></i>
                                <span class="nav-link-text">Students Report</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a href="logout.php" class="nav-link" onclick="return confirm('Are you sure you want to logout?');">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-link-text">Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="footer">
        <p>&copy;<script>document.write(new Date().getFullYear());</script> powered by <a href="https://me.co.ke" target="_blank">Swipeware tech.</a></p>
    </div>
</nav>

<!-- Main Content -->
<div id="main-content">
    <!-- Premium Top Navigation Bar -->
    <div class="top-navbar">
        <div class="navbar-left">
            <button class="sidebar-toggle">
                <i class="fas fa-bars"></i>
            </button>
            <nav class="breadcrumb" aria-label="Breadcrumb">
                <span class="breadcrumb-item active">Dashboard</span>
                <span class="breadcrumb-divider">/</span>
                <span class="breadcrumb-item">Overview</span>
            </nav>
        </div>
        <div class="navbar-right">
            <div class="user-profile">
                <div class="notifications" aria-label="Notifications" tabindex="0" aria-live="polite">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge pulse">3</span>
                </div>
                <div class="profile-dropdown" tabindex="0" aria-label="User profile">
                    <div class="profile-avatar">
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User'); ?>&background=random" alt="Profile picture">
                    </div>
                    <div class="profile-info">
                        <span class="user-name"><?php echo $_SESSION['username'] ?? 'User'; ?></span>
                        <span class="user-role">Facilitator</span>
                    </div>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Premium Dashboard Content -->
    <div class="dashboard-container">
        <!-- Mobile App Style Header Slider -->
        <div class="header-slider">
            <div class="slider-container" id="sliderContainer">
                <!-- Slide 1 -->
                <div class="slider-slide">
                    <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1471&q=80" alt="Education collaboration">
                    <div class="slide-overlay">
                        <div class="slide-content">
                            <h1 class="slide-title">Facilitator Dashboard</h1>
                            <p class="slide-subtitle">Your central hub for managing classes, students, and educational resources</p>
                            <span class="slide-badge">All-in-One Solution</span>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 2 -->
                <div class="slider-slide">
                    <img src="https://images.unsplash.com/photo-1501504905252-473c47e087f8?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1374&q=80" alt="Work seamlessly">
                    <div class="slide-overlay">
                        <div class="slide-content">
                            <h1 class="slide-title">Work Seamlessly</h1>
                            <p class="slide-subtitle">Integrated tools for attendance, grading, communication, and lesson planning</p>
                            <span class="slide-badge">Streamlined Workflow</span>
                        </div>
                    </div>
                </div>
                
                <!-- Slide 3 -->
                <div class="slider-slide">
                    <img src="https://images.unsplash.com/photo-1485827404703-89b55fcc595e?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1470&q=80" alt="AI technology">
                    <div class="slide-overlay">
                        <div class="slide-content">
                            <h1 class="slide-title">Work Faster with AI</h1>
                            <p class="slide-subtitle">Leverage AI-powered insights to enhance teaching effectiveness and student outcomes</p>
                            <span class="slide-badge">Smart Analytics</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Slider Navigation -->
            <button class="slider-nav prev" id="prevSlide">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="slider-nav next" id="nextSlide">
                <i class="fas fa-chevron-right"></i>
            </button>
            
            <!-- Slider Indicators -->
            <div class="slider-indicators" id="sliderIndicators">
                <span class="slider-indicator active" data-slide="0"></span>
                <span class="slider-indicator" data-slide="1"></span>
                <span class="slider-indicator" data-slide="2"></span>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Key Metrics Section with Floating Cards -->
            <div class="metrics-section">
                <!-- Total Students Card with Hover Animation -->
                <div class="metric-card student-card" tabindex="0">
                    <div class="metric-icon">
                        <div class="icon-bg">
                            <i class="fas fa-user-graduate" aria-hidden="true"></i>
                        </div>
                    </div>
                    <div class="metric-content">
                        <span class="metric-label">Total Students</span>
                        <span class="metric-value"><?php echo $totalStudents; ?></span>
                        <div class="metric-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>5% from last term</span>
                        </div>
                    </div>
                    <div class="metric-wave"></div>
                </div>

                <!-- Active Classes Card with Hover Animation -->
                <div class="metric-card classes-card">
                    <div class="metric-icon">
                        <div class="icon-bg">
                            <i class="fas fa-school"></i>
                        </div>
                    </div>
                    <div class="metric-content">
                        <span class="metric-label">Active Classes</span>
                        <span class="metric-value"><?php echo $totalClasses; ?></span>
                        <div class="metric-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>2 new this term</span>
                        </div>
                    </div>
                    <div class="metric-wave"></div>
                </div>

                <!-- Attendance Rate Card with Hover Animation -->
                <div class="metric-card attendance-card">
                    <div class="metric-icon">
                        <div class="icon-bg">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="metric-content">
                        <span class="metric-label">Attendance Rate</span>
                        <span class="metric-value">94%</span>
                        <div class="metric-trend">
                            <i class="fas fa-arrow-up"></i>
                            <span>3% improvement</span>
                        </div>
                    </div>
                    <div class="metric-wave"></div>
                </div>
            </div>

            <!-- Quick Actions Section with Floating Cards -->
            <div class="actions-section glass-card">
                <div class="section-header">
                    <h2><i class="fas fa-bolt mr-2"></i>Quick Actions</h2>
                    <div class="section-actions">
                        <button class="btn btn-outline">
                            <i class="fas fa-ellipsis-h"></i>
                        </button>
                    </div>
                </div>
                <div class="action-grid">
                    <a href="logginn.php" class="action-card">
                        <div class="action-icon notes">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Lesson Notes</h3>
                        <p>Create and manage your teaching materials</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="login.php" class="action-card">
                        <div class="action-icon attendance">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h3>Attendance</h3>
                        <p>Mark and track student attendance</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="logg.php" class="action-card">
                        <div class="action-icon assignments">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h3>Assignments/Video lessons</h3>
                        <p>Create, test or send video lessons</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="#" class="action-card" onclick="showUnderDevelopmentMessage()">
                        <div class="action-icon reports">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h3>Reports</h3>
                        <p>Generate performance reports</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="email_login.php" class="action-card">
                        <div class="action-icon emails">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Emails</h3>
                        <p>Communicate with students/parents</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="subjects.php" class="action-card">
                        <div class="action-icon subjects">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <h3>Subjects</h3>
                        <p>Manage and assign subjects</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="exam_scores.php" class="action-card">
                        <div class="action-icon scores">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h3>Exam Scores</h3>
                        <p>View and input student scores</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="lo.php" class="action-card">
                        <div class="action-icon behavior">
                            <i class="fas fa-clipboard-list"></i>
                        </div>
                        <h3>Skill Evaluation</h3>
                        <p>Evaluate behaviour and soft skills</p>
                        <div class="action-hover">
                            <i class="fas fa-arrow-right"></i>
                        </div>
                    </a>
                    <a href="#" class="action-card add-new">
                        <div class="action-icon add">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h3>Add New</h3>
                        <p>Create new content or activity</p>
                    </a>
                </div>
            </div>

            <!-- Calendar and Activity Section -->
            <div class="bottom-section">
                <!-- Calendar Section with Glassmorphism -->
                <div class="calendar-section glass-card">
                    <div class="section-header">
                        <h2><i class="far fa-calendar-alt mr-2"></i>Academic Calendar</h2>
                        <div class="section-actions">
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Event
                            </button>
                            <button class="btn btn-outline btn-sm">
                                <i class="fas fa-ellipsis-h"></i>
                            </button>
                        </div>
                    </div>
                    <div id="calendar"></div>
                </div>

                <!-- Recent Activity Section with Floating Cards -->
                <div class="activity-section glass-card">
                    <div class="section-header">
                        <h2><i class="fas fa-bell mr-2"></i>Recent Activity</h2>
                        <a href="#" class="view-all" onclick="showLoadingSpinner()">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    <div class="activity-list">
                        <div class="activity-item">
                            <div class="activity-icon success pulse">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <h3>Attendance Marked</h3>
                                    <span class="activity-time">2h ago</span>
                                </div>
                                <p>Class 10A - Mathematics (32 students present)</p>
                                <div class="activity-progress">
                                    <div class="progress-bar" style="width: 92%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon primary pulse">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <h3>Lesson Notes Uploaded</h3>
                                    <span class="activity-time">1d ago</span>
                                </div>
                                <p>Week 5 materials for all classes</p>
                                <div class="activity-progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon info pulse">
                                <i class="fas fa-comment-alt"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <h3>New Message</h3>
                                    <span class="activity-time">2d ago</span>
                                </div>
                                <p>From Parent: Jane Doe<p>
                                                            <p>Regarding: Term Project</p>
                                <div class="activity-progress">
                                    <div class="progress-bar" style="width: 75%"></div>
                                </div>
                            </div>
                        </div>
                        <div class="activity-item">
                            <div class="activity-icon warning pulse">
                                <i class="fas fa-exclamation-circle"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-header">
                                    <h3>Assignment Due</h3>
                                    <span class="activity-time">3d ago</span>
                                </div>
                                <p>Algebra II assignment due tomorrow</p>
                                <div class="activity-progress">
                                    <div class="progress-bar" style="width: 65%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Report Cards Modal -->
<div class="modal fade" id="GenerateReportCardsModal" tabindex="-1" role="dialog" aria-labelledby="GenerateReportCardsModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    
      <form id="reportForm" method="POST" action="report_cards.php" target="_blank">
        <div class="modal-header">
          <h5 class="modal-title" id="GenerateReportCardsModalLabel">Generate Student Reports</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <!-- Select Class -->
          <div class="form-group">
            <label for="askclass">Select Class</label>
            <select name="askclass" id="askclass" class="form-control" required>
              <option value="">-- Choose Class --</option>
              <?php
              require "config.php";
              $stmt = $conn->prepare("SELECT DISTINCT class FROM marks ORDER BY class ASC");
              $stmt->execute();
              $classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
              foreach ($classes as $row) {
                  echo "<option value='" . htmlspecialchars($row['class']) . "'>" . htmlspecialchars($row['class']) . "</option>";
              }
              ?>
            </select>
          </div>

          <!-- Select Exam -->
          <div class="form-group">
            <label for="exam">Select Exam</label>
            <select name="exam" id="exam" class="form-control" required>
              <option value="">-- Choose Exam --</option>
              <?php
              $stmt = $conn->prepare("SELECT DISTINCT examname FROM marks ORDER BY examname ASC");
              $stmt->execute();
              $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
              foreach ($exams as $row) {
                  echo "<option value='" . htmlspecialchars($row['examname']) . "'>" . htmlspecialchars($row['examname']) . "</option>";
              }
              ?>
            </select>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="generateReportBtn">Generate Report</button>
        </div>
      </form>

    </div>
  </div>
</div>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/3.10.2/mdb.min.js"></script>

<script>
    // Sidebar toggle
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
    });

    // Slider functionality
    let currentSlide = 0;
    const totalSlides = 3;
    const sliderContainer = document.getElementById('sliderContainer');
    const indicators = document.querySelectorAll('.slider-indicator');
    let slideInterval;

    function goToSlide(index) {
        currentSlide = index;
        sliderContainer.style.transform = `translateX(-${currentSlide * 33.333}%)`;
        
        // Update indicators
        indicators.forEach((indicator, i) => {
            indicator.classList.toggle('active', i === currentSlide);
        });
        
        // Reset auto-slide timer
        resetAutoSlide();
    }

    function nextSlide() {
        currentSlide = (currentSlide + 1) % totalSlides;
        goToSlide(currentSlide);
    }

    function prevSlide() {
        currentSlide = (currentSlide - 1 + totalSlides) % totalSlides;
        goToSlide(currentSlide);
    }

    function startAutoSlide() {
        slideInterval = setInterval(nextSlide, 5000);
    }

    function resetAutoSlide() {
        clearInterval(slideInterval);
        startAutoSlide();
    }

    // Initialize slider
    document.getElementById('nextSlide').addEventListener('click', nextSlide);
    document.getElementById('prevSlide').addEventListener('click', prevSlide);
    
    indicators.forEach((indicator, index) => {
        indicator.addEventListener('click', () => goToSlide(index));
    });
    
    // Start auto-sliding
    startAutoSlide();

    // Loading Overlay
    $(document).ready(function() {
        function showLoadingOverlay() {
            const overlay = $('#loading-overlay');
            overlay.addClass('active');
            setTimeout(function() {
                overlay.removeClass('active');
            }, 1000);
        }

        $('a.nav-link').on('click', function(e) {
            const href = $(this).attr('href');
            const isExternalLink = href.startsWith('http') || href.startsWith('#');

            if (!isExternalLink) {
                e.preventDefault();
                showLoadingOverlay();
                setTimeout(function() {
                    window.location.href = href;
                }, 1000);
            }
        });

        $(window).on('load', function() {
            $('#loading-overlay').removeClass('active');
        });

        // Initialize Calendar
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            events: [
                {
                    title: 'Parent-Teacher Meeting',
                    start: new Date(),
                    backgroundColor: '#4f46e5',
                    borderColor: '#4f46e5',
                    extendedProps: {
                        description: 'Quarterly parent-teacher conference',
                        location: 'School Conference Room',
                        attendees: 'All faculty'
                    }
                },
                {
                    title: 'Term Assessment',
                    start: new Date(new Date().setDate(new Date().getDate() + 5)),
                    backgroundColor: '#10b981',
                    borderColor: '#10b981',
                    extendedProps: {
                        description: 'Mid-term examinations',
                        location: 'Classrooms',
                        attendees: 'All students'
                    }
                },
                {
                    title: 'Staff Development Day',
                    start: new Date(new Date().setDate(new Date().getDate() + 10)),
                    end: new Date(new Date().setDate(new Date().getDate() + 11)),
                    backgroundColor: '#f59e0b',
                    borderColor: '#f59e0b',
                    allDay: true,
                    extendedProps: {
                        description: 'Professional development workshops',
                        location: 'School Auditorium',
                        attendees: 'Teaching staff only'
                    }
                }
            ],
            eventClick: function(info) {
                // Create a custom modal for event details
                const modal = `
                    <div class="event-modal">
                        <div class="modal-header">
                            <h3>${info.event.title}</h3>
                            <button class="close-modal">&times;</button>
                        </div>
                        <div class="modal-body">
                            <p><i class="fas fa-calendar-day"></i> ${info.event.start.toLocaleDateString()}</p>
                            ${info.event.end ? `<p><i class="fas fa-clock"></i> ${info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})} - ${info.event.end.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</p>` : ''}
                            ${info.event.extendedProps.description ? `<p><i class="fas fa-align-left"></i> ${info.event.extendedProps.description}</p>` : ''}
                            ${info.event.extendedProps.location ? `<p><i class="fas fa-map-marker-alt"></i> ${info.event.extendedProps.location}</p>` : ''}
                            ${info.event.extendedProps.attendees ? `<p><i class="fas fa-users"></i> ${info.event.extendedProps.attendees}</p>` : ''}
                            <div class="modal-actions">
                                <button class="btn btn-outline">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button class="btn btn-primary">
                                    <i class="fas fa-share"></i> Share
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="modal-overlay"></div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modal);
                
                document.querySelector('.close-modal').addEventListener('click', function() {
                    document.querySelector('.event-modal').remove();
                    document.querySelector('.modal-overlay').remove();
                });
                
                document.querySelector('.modal-overlay').addEventListener('click', function() {
                    document.querySelector('.event-modal').remove();
                    document.querySelector('.modal-overlay').remove();
                });
            },
            dayHeaderContent: function(arg) {
                return arg.text.replace('day', '').charAt(0).toUpperCase() + arg.text.replace('day', '').slice(1);
            },
            height: 'auto',
            eventDisplay: 'block',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: 'short'
            }
        });
        calendar.render();

        // Report form submission handler
        $('#reportForm').on('submit', function(e) {
            $('#generateReportBtn').prop('disabled', true);
            $('#GenerateReportCardsModal').addClass('form-loading');
            
            // Optional: Add a small delay to show the loading state
            setTimeout(() => {
                $('#GenerateReportCardsModal').removeClass('form-loading');
                $('#GenerateReportCardsModal').modal('hide');
                $('#generateReportBtn').prop('disabled', false);
            }, 2000);
        });
    });

    function showUnderDevelopmentMessage() {
        alert("This page is currently under development. Thank you for your patience!");
    }

    function showLoadingSpinner() {
        alert("Loading all activities...");
    }
</script>
</body>
</html>
