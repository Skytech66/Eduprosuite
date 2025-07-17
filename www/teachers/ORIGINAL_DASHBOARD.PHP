<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../require_developer.php"; // Now correct path
require_once "header.php";
include 'footer.php';
?>

<!-- Modern CSS Framework -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Enhanced Glassmorphism and Neumorphism effects -->
<style>
    :root {
        /* School Color Scheme */
        --primary: #2C3E50; /* Deep blue */
        --primary-light: rgba(44, 62, 80, 0.08);
        --primary-lighter: rgba(44, 62, 80, 0.04);
        --primary-dark: #1A252F;
        --secondary: #E74C3C; /* Accent red */
        --secondary-light: rgba(231, 76, 60, 0.08);
        --success: #27AE60; /* Green */
        --success-light: rgba(39, 174, 96, 0.08);
        --info: #3498DB; /* Blue */
        --info-light: rgba(52, 152, 219, 0.08);
        --warning: #F39C12; /* Orange */
        --warning-light: rgba(243, 156, 18, 0.08);
        --danger: #E74C3C; /* Red */
        --danger-light: rgba(231, 76, 60, 0.08);
        --light: #F9FAFB;
        --dark: #2C3E50;
        --darker: #1A252F;
        --gray-50: #F9FAFB;
        --gray-100: #F3F4F6;
        --gray-200: #E5E7EB;
        --gray-300: #D1D5DB;
        --gray-400: #9CA3AF;
        --gray-500: #6B7280;
        --gray-600: #4B5563;
        --gray-700: #374151;
        --gray-800: #1F2937;
        --gray-900: #111827;
        
        /* Enhanced Shadows */
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.04);
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08), 0 1px 2px 0 rgba(0, 0, 0, 0.04);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.08), 0 2px 4px -1px rgba(0, 0, 0, 0.04);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.08), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        --shadow-primary: 0 4px 14px 0 rgba(44, 62, 80, 0.25);
        --shadow-success: 0 4px 14px 0 rgba(39, 174, 96, 0.25);
        --shadow-warning: 0 4px 14px 0 rgba(243, 156, 18, 0.25);
        --shadow-danger: 0 4px 14px 0 rgba(231, 76, 60, 0.25);
        --shadow-info: 0 4px 14px 0 rgba(52, 152, 219, 0.25);
        
        /* Enhanced Border Radius */
        --rounded-sm: 4px;
        --rounded: 6px;
        --rounded-md: 8px;
        --rounded-lg: 12px;
        --rounded-xl: 16px;
        --rounded-2xl: 20px;
        --rounded-full: 9999px;
        
        /* Enhanced Transitions */
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
        background-color: var(--gray-50);
        color: var(--dark);
        line-height: 1.6;
        font-size: 16px;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
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

    /* School Header */
    .school-header {
        display: flex;
        align-items: center;
        padding: 16px 24px;
        background: white;
        border-bottom: 1px solid var(--gray-200);
        box-shadow: var(--shadow-sm);
    }

    .school-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }

    .school-logo img {
        height: 65px;
        width: 60px;
        object-fit: contain;
    }

    .school-name {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 900;
        font-size: 36px;
        color: var(--primary);
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .dashboard-container {
        max-width: 1800px;
        margin: 0 auto;
        padding: 20px;
    }

    /* Enhanced Glassmorphism Effect */
    .glass-card {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-radius: var(--rounded-2xl);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: var(--shadow-lg);
        transition: var(--transition-slow);
    }

    .glass-card:hover {
        box-shadow: var(--shadow-xl);
        transform: translateY(-1px);
    }

    /* Gradient Text */
    .gradient-text {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
    }

    /* Dashboard Header */
    .dashboard-header {
        padding: 24px;
        margin-bottom: 24px;
        position: relative;
        overflow: hidden;
    }

    .dashboard-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, var(--primary-light) 0%, rgba(255,255,255,0) 70%);
        opacity: 0.15;
        z-index: 0;
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    .header-left {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .dashboard-title {
        font-family: 'Space Grotesk', 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        margin: 0;
        font-size: 28px;
        display: flex;
        align-items: center;
        letter-spacing: -0.5px;
        line-height: 1.3;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        font-size: 15px;
        color: var(--secondary);
    }

    .breadcrumb-item {
        color: var(--secondary);
        transition: var(--transition);
        text-decoration: none;
    }

    .breadcrumb-item:hover {
        color: var(--primary);
    }

    .breadcrumb-item.active {
        color: var(--primary);
        font-weight: 600;
    }

    .breadcrumb-divider {
        margin: 0 8px;
        color: var(--gray-400);
        font-size: 12px;
    }

    /* User Profile */
    .user-profile {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .notifications {
        position: relative;
        font-size: 18px;
        color: var(--gray-600);
        cursor: pointer;
        transition: var(--transition);
        width: 44px;
        height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: var(--rounded-full);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .notifications:hover, .notifications:focus {
        color: var(--dark);
        background: rgba(255, 255, 255, 0.95);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
        outline: none;
    }

    .notification-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background-color: var(--danger);
        color: white;
        border-radius: var(--rounded-full);
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
        border: 2px solid white;
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(231, 76, 60, 0.5);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(231, 76, 60, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(231, 76, 60, 0);
        }
    }

    .profile-dropdown {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px 8px 8px;
        border-radius: var(--rounded-xl);
        cursor: pointer;
        transition: var(--transition);
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .profile-dropdown:hover, .profile-dropdown:focus {
        background: rgba(255, 255, 255, 0.95);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
        outline: none;
    }

    .profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: var(--rounded-full);
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.8);
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
        font-weight: 600;
        font-size: 15px;
        color: var(--dark);
        line-height: 1.3;
    }

    .user-role {
        font-size: 13px;
        color: var(--secondary);
        line-height: 1.3;
    }

    .dropdown-arrow {
        font-size: 14px;
        color: var(--secondary);
        transition: transform 0.2s;
    }

    .profile-dropdown:hover .dropdown-arrow, .profile-dropdown:focus .dropdown-arrow {
        transform: rotate(180deg);
    }

    /* Metrics Section */
    .metrics-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .metric-card {
        background: white;
        border-radius: var(--rounded-xl);
        padding: 24px;
        transition: var(--transition-slow);
        display: flex;
        gap: 16px;
        align-items: center;
        position: relative;
        overflow: hidden;
        border: none;
        box-shadow: var(--shadow-md);
    }

    .metric-card:hover, .metric-card:focus-within {
        transform: translateY(-5px);
        box-shadow: var(--shadow-xl);
    }

    .student-card {
        background: linear-gradient(135deg, var(--primary-lighter) 0%, rgba(255,255,255,1) 100%);
        border-left: 4px solid var(--primary);
    }

    .classes-card {
        background: linear-gradient(135deg, var(--info-light) 0%, rgba(255,255,255,1) 100%);
        border-left: 4px solid var(--info);
    }

    .attendance-card {
        background: linear-gradient(135deg, var(--success-light) 0%, rgba(255,255,255,1) 100%);
        border-left: 4px solid var(--success);
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
        width: 52px;
        height: 52px;
        border-radius: var(--rounded-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
    }

    .student-card .icon-bg {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
    }

    .classes-card .icon-bg {
        background: linear-gradient(135deg, var(--info) 0%, #2563eb 100%);
        color: white;
    }

    .attendance-card .icon-bg {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
        color: white;
    }

    .metric-icon i {
        font-size: 22px;
    }

    .metric-content {
        flex: 1;
    }

    .metric-label {
        display: block;
        font-size: 15px;
        color: var(--secondary);
        margin-bottom: 6px;
        font-weight: 500;
    }

    .metric-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 6px;
        font-family: 'Space Grotesk', sans-serif;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .metric-trend {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 14px;
        color: var(--success);
        font-weight: 500;
    }

    .metric-trend.negative {
        color: var(--danger);
    }

    .metric-trend i {
        font-size: 14px;
    }

    /* Section Headers */
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .section-header h2 {
        font-family: 'Space Grotesk', 'Plus Jakarta Sans', sans-serif;
        font-size: 22px;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
        letter-spacing: -0.3px;
        line-height        : 1.3;
    }

    .section-header h2 i {
        margin-right: 12px;
        font-size: 1.1em;
        color: var(--primary);
    }

    .section-actions {
        display: flex;
        gap: 12px;
    }

    /* Actions Section */
    .actions-section {
        background: white;
        border-radius: var(--rounded-2xl);
        padding: 24px;
        margin-bottom: 32px;
        box-shadow: var(--shadow-sm);
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
    }

    .action-card {
        background: white;
        border-radius: var(--rounded-xl);
        padding: 24px;
        text-decoration: none;
        transition: var(--transition-slow);
        border: 1px solid var(--gray-200);
        position: relative;
        overflow: hidden;
    }

    .action-card:hover, .action-card:focus {
        border-color: var(--primary-light);
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
        outline: none;
    }

    .action-card.add-new {
        background: linear-gradient(135deg, rgba(243, 244, 246, 0.5) 0%, rgba(249, 250, 251, 0.5) 100%);
        border: 1px dashed var(--gray-300);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .action-card.add-new:hover, .action-card.add-new:focus {
        background: linear-gradient(135deg, rgba(243, 244, 246, 0.7) 0%, rgba(249, 250, 251, 0.7) 100%);
        border-color: var(--primary);
    }

    .action-icon {
        width: 52px;
        height: 52px;
        border-radius: var(--rounded-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        font-size: 22px;
        color: white;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .action-card:hover .action-icon, .action-card:focus .action-icon {
        transform: scale(1.1);
    }

    .action-icon.notes {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
    }

    .action-icon.attendance {
        background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
    }

    .action-icon.assignments {
        background: linear-gradient(135deg, var(--warning) 0%, #D97706 100%);
    }

    .action-icon.reports {
        background: linear-gradient(135deg, var(--info) 0%, #2563EB 100%);
    }

    .action-icon.emails {
        background: linear-gradient(135deg, #8B5CF6 0%, #7C3AED 100%);
    }

    .action-icon.subjects {
        background: linear-gradient(135deg, #EC4899 0%, #DB2777 100%);
    }

    .action-icon.scores {
        background: linear-gradient(135deg, #F97316 0%, #EA580C 100%);
    }

    .action-icon.behavior {
        background: linear-gradient(135deg, #14B8A6 0%, #0D9488 100%);
    }

    .action-icon.add {
        background: linear-gradient(135deg, var(--gray-300) 0%, var(--gray-400) 100%);
        color: var(--gray-700);
    }

    .action-card h3 {
        font-size: 18px;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 10px;
        font-family: 'Space Grotesk', sans-serif;
        line-height: 1.3;
    }

    .action-card p {
        font-size: 15px;
        color: var(--secondary);
        margin: 0;
        line-height: 1.5;
    }

    .action-hover {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 32px;
        height: 32px;
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

    .action-card:hover .action-hover, .action-card:focus .action-hover {
        opacity: 1;
    }

    /* Bottom Section */
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

    /* Calendar Section */
    .calendar-section {
        padding: 24px;
        margin-bottom: 0;
    }

    .fc {
        font-family: 'Inter', sans-serif;
    }

    .fc .fc-toolbar-title {
        font-size: 20px;
        font-weight: 600;
        color: var(--dark);
    }

    .fc .fc-button {
        background-color: white;
        border: 1px solid var(--gray-200);
        color: var(--dark);
        font-size: 15px;
        font-weight: 500;
        padding: 8px 14px;
        border-radius: var(--rounded-md);
        transition: var(--transition);
    }

    .fc .fc-button:hover, .fc .fc-button:focus {
        background-color: var(--gray-100);
        outline: none;
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: var(--primary);
        border-color: var(--primary);
        color: white;
    }

    .fc .fc-daygrid-day-number {
        color: var(--dark);
        font-weight: 500;
        padding: 6px;
        font-size: 15px;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: var(--primary-light);
    }

    .fc .fc-daygrid-event {
        border-radius: var(--rounded-sm);
        padding: 3px 6px;
        font-size: 14px;
    }

    .fc .fc-daygrid-event-dot {
        display: none;
    }

    /* Activity Section */
    .activity-section {
        background: white;
        border-radius: var(--rounded-2xl);
        padding: 24px;
        box-shadow: var(--shadow-sm);
    }

    .view-all {
        font-size: 16px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
        transition: color 0.2s;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .view-all:hover, .view-all:focus {
        color: var(--primary-dark);
        text-decoration: underline;
        outline: none;
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .activity-item {
        display: flex;
        gap: 16px;
        padding: 18px;
        border-radius: var(--rounded-lg);
        background: white;
        transition: var(--transition);
        border: 1px solid var(--gray-200);
    }

    .activity-item:hover, .activity-item:focus-within {
        background: var(--gray-100);
        transform: translateX(2px);
        box-shadow: var(--shadow-sm);
    }

    .activity-icon {
        width: 44px;
        height: 44px;
        min-width: 44px;
        border-radius: var(--rounded-full);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
    }

    .activity-icon.success {
        background-color: var(--success);
        box-shadow: var(--shadow-success);
    }

    .activity-icon.primary {
        background-color: var(--primary);
        box-shadow: var(--shadow-primary);
    }

    .activity-icon.info {
        background-color: var(--info);
        box-shadow: var(--shadow-info);
    }

    .activity-icon.warning {
        background-color: var(--warning);
        box-shadow: var(--shadow-warning);
    }

    .activity-content {
        flex: 1;
        min-width: 0;
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 6px;
    }

    .activity-header h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--dark);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    .activity-time {
        font-size: 14px;
        color: var(--secondary);
        white-space: nowrap;
        margin-left: 10px;
    }

    .activity-content p {
        font-size: 15px;
        color: var(--secondary);
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .activity-progress {
        height: 6px;
        background: var(--gray-200);
        border-radius: var(--rounded-full);
        margin-top: 10px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: var(--primary);
        border-radius: var(--rounded-full);
        transition: width 0.6s ease;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 10px 18px;
        border-radius: var(--rounded-md);
        font-size: 15px;
        font-weight: 600;
        line-height: 1.5;
        cursor: pointer;
        transition: var(--transition);
        border: 1px solid transparent;
        gap: 8px;
        min-height: 44px;
    }

    .btn-sm {
        padding: 8px 14px;
        font-size: 14px;
        min-height: 36px;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .btn-primary:hover, .btn-primary:focus {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
        box-shadow: var(--shadow-primary);
        outline: none;
    }

    .btn-outline {
        background-color: white;
        color: var(--gray-600);
        border-color: var(--gray-300);
    }

    .btn-outline:hover, .btn-outline:focus {
        background-color: var(--gray-100);
        color: var(--dark);
        outline: none;
    }

    /* Event Modal */
    .event-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: var(--rounded-xl);
        padding: 24px;
        width: 90%;
        max-width: 500px;
        z-index: 1001;
        box-shadow: var(--shadow-xl);
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 1000;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .modal-header h3 {
        font-size: 22px;
        font-weight: 700;
        color: var(--dark);
        line-height: 1.3;
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 24px;
        color: var(--secondary);
        cursor: pointer;
        transition: var(--transition);
        line-height: 1;
        padding: 8px;
    }

    .close-modal:hover, .close-modal:focus {
        color: var(--dark);
        outline: none;
    }

    .modal-body p {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        color: var(--dark);
        font-size: 16px;
        line-height: 1.5;
    }

    .modal-body i {
        width: 24px;
        text-align: center;
        color: var(--primary);
        font-size: 18px;
    }

    .modal-actions {
        display: flex;
        gap: 16px;
        margin-top: 24px;
    }

    /* Focus styles for accessibility */
    a:focus, button:focus, [tabindex="0"]:focus {
        outline: 2px solid var(--primary);
        outline-offset: 2px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .dashboard-container {
            padding: 20px;
        }
        
        .metrics-section {
            grid-template-columns: repeat(2, 1fr);
        }
        
        body {
            font-size: 16px;
        }
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 20px;
        }
        
        .user-profile {
            width: 100%;
            justify-content: space-between;
        }
        
        .metrics-section {
            grid-template-columns: 1fr;
        }
        
        .action-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        body {
            font-size: 15px;
        }
        
        .dashboard-title {
            font-size: 24px;
        }
        
        .school-name {
            font-size: 18px;
        }
        
        .school-logo img {
            height: 32px;
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
        
        .school-header {
            padding: 12px 16px;
        }
        
        .school-name {
            font-size: 16px;
        }
        
        .school-logo img {
            height: 28px;
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

<!-- Skip to content link for keyboard users -->
<a href="#main-content" class="skip-link">Skip to main content</a>

<!-- School Header with Logo -->
<div class="school-header">
    <a href="#" class="school-logo">
        <img src="adinkra.png" alt="Adinkra International School Crest">
        <span class="school-name">Adinkra International School</span>
    </a>
</div>

<div class="dashboard-container" id="main-content">
    <!-- Dashboard Header with Glassmorphism Effect -->
    <div class="dashboard-header glass-card">
        <div class="header-content">
            <div class="header-left">
                <h1 class="dashboard-title">
                    <span class="gradient-text"><i class="fas fa-chalkboard-teacher mr-2"></i> Educator Dashboard</span>
                </h1>
                <nav class="breadcrumb" aria-label="Breadcrumb">
                    <span class="breadcrumb-item active">Dashboard</span>
                    <span class="breadcrumb-divider">/</span>
                    <span class="breadcrumb-item">Overview</span>
                </nav>
            </div>
            <div class="user-profile">
                <div class="notifications" aria-label="Notifications" tabindex="0" aria-live="polite">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge pulse">3</span>
                </div>
                <div class="profile-dropdown" tabindex="0" aria-label="User  profile">
                    <div class="profile-avatar">
                                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username'] ?? 'User   '); ?>&background=random" alt="Profile picture">
                    </div>
                    <div class="profile-info">
                        <span class="user-name"><?php echo $_SESSION['username'] ?? 'User   '; ?></span>
                        <span class="user-role">Facilitator</span>
                    </div>
                    <i class="fas fa-chevron-down dropdown-arrow"></i>
                </div>
            </div>
        </div>
    </div>

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
                <?php
                    $stmt = $conn->query("SELECT COUNT(name) as 'tstudents' FROM student");
                    $row = $stmt->fetchArray(SQLITE3_ASSOC);
                    $totalStudents = $row['tstudents'] ?? 0;
                ?>
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
                <?php
                    $stmt = $conn->query("SELECT COUNT(DISTINCT class) as 'tclasses' FROM student");
                    $row = $stmt->fetchArray(SQLITE3_ASSOC);
                    $totalClasses = $row['tclasses'] ?? 0;
                ?>
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

            <!-- Emails Card -->
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

            <!-- Subjects Card -->
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

            <!-- Exam Scores Card -->
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
                        <p>From Parent: Jane Doe (Regarding: Term Project)</p>
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

<?php require_once "../include/footer.php"; ?>

<!-- JavaScript Libraries -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
    // Hide spinner when page is fully loaded
    window.addEventListener('load', function() {
        // No spinner to hide
    });
    
    function showUnderDevelopmentMessage() {
        alert("This page is currently under development. Thank you for your patience!");
    }

    $(document).ready(function () {
        // Initialize Calendar
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            themeSystem: 'standard',
            events: [
                {
                    title: 'Parent-Teacher Meeting',
                    start: new Date(),
                    backgroundColor: '#2C3E50',
                    borderColor: '#2C3E50',
                    extendedProps: {
                        description: 'Quarterly parent-teacher conference',
                        location: 'School Conference Room',
                        attendees: 'All faculty'
                    }
                },
                {
                    title: 'Term Assessment',
                    start: new Date(new Date().setDate(new Date().getDate() + 5)),
                    backgroundColor: '#27AE60',
                    borderColor: '#27AE60',
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
                    backgroundColor: '#F39C12',
                    borderColor: '#F39C12',
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
                
                $('body').append(modal);
                
                $('.close-modal, .modal-overlay').on('click', function() {
                    $('.event-modal, .modal-overlay').remove();
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
    });
</script>
</body>
</html>
