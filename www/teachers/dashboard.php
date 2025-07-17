<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../require_developer.php";
require_once "header.php";
?>

<!-- Modern CSS Framework with Improved Typography -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

<!-- Enhanced Design System with Sophisticated Color Palette -->
<style>
    :root {
        /* Professional Color Scheme */
        --primary: #3f37c9;
            --primary-dark: #312ba5;
        --primary-light: rgba(37, 99, 235, 0.1);
        --primary-lighter: rgba(37, 99, 235, 0.05);
        --primary-dark: #1d4ed8;
        --primary-darker: #1e40af;
        --secondary: #7c3aed; /* Elegant purple */
        --secondary-light: rgba(124, 58, 237, 0.1);
        --success: #10b981; /* Fresh green */
        --success-light: rgba(16, 185, 129, 0.1);
        --info: #0ea5e9; /* Sky blue */
        --info-light: rgba(14, 165, 233, 0.1);
        --warning: #f59e0b; /* Warm amber */
        --warning-light: rgba(245, 158, 11, 0.1);
        --danger: #ef4444; /* Alert red */
        --danger-light: rgba(239, 68, 68, 0.1);
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
        
        /* Enhanced Depth System */
        --shadow-xs: 0 1px 2px 0 rgba(15, 23, 42, 0.03);
        --shadow-sm: 0 1px 3px 0 rgba(15, 23, 42, 0.05), 0 1px 2px 0 rgba(15, 23, 42, 0.03);
        --shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.05), 0 2px 4px -1px rgba(15, 23, 42, 0.03);
        --shadow-md: 0 10px 15px -3px rgba(15, 23, 42, 0.05), 0 4px 6px -2px rgba(15, 23, 42, 0.03);
        --shadow-lg: 0 20px 25px -5px rgba(15, 23, 42, 0.05), 0 10px 10px -5px rgba(15, 23, 42, 0.01);
        --shadow-xl: 0 25px 50px -12px rgba(15, 23, 42, 0.12);
        --shadow-2xl: 0 35px 60px -15px rgba(15, 23, 42, 0.15);
        --shadow-primary: 0 4px 14px 0 rgba(37, 99, 235, 0.25);
        --shadow-success: 0 4px 14px 0 rgba(16, 185, 129, 0.25);
        --shadow-warning: 0 4px 14px 0 rgba(245, 158, 11, 0.25);
        --shadow-danger: 0 4px 14px 0 rgba(239, 68, 68, 0.25);
        --shadow-info: 0 4px 14px 0 rgba(14, 165, 233, 0.25);
        
        /* Precise Border Radius */
        --rounded-xs: 2px;
        --rounded-sm: 4px;
        --rounded: 6px;
        --rounded-md: 8px;
        --rounded-lg: 12px;
        --rounded-xl: 16px;
        --rounded-2xl: 24px;
        --rounded-3xl: 32px;
        --rounded-full: 9999px;
        
        /* Smooth Transitions */
        --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-slow: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        --transition-bounce: all 0.5s cubic-bezier(0.68, -0.6, 0.32, 1.6);
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
        min-height: 100vh;
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

    /* School Header with Subtle Elevation */
    .school-header {
        display: flex;
        align-items: center;
        padding: 16px 32px;
        background: white;
        border-bottom: 1px solid var(--gray-200);
        box-shadow: var(--shadow-sm);
        position: relative;
        z-index: 10;
    }

    .school-logo {
        display: flex;
        align-items: center;
        gap: 16px;
        text-decoration: none;
        transition: var(--transition);
    }

    .school-logo:hover {
        opacity: 0.9;
    }

    .school-logo img {
        height: 72px;
        width: 72px;
        object-fit: contain;
        transition: var(--transition);
    }

    .school-name {
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 800;
        font-size: 28px;
        color: var(--darker);
        letter-spacing: -0.5px;
        line-height: 1.2;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    /* Main Dashboard Container */
    .dashboard-container {
        max-width: 1800px;
        margin: 0 auto;
        padding: 32px;
        position: relative;
    }

    /* Dashboard Header with Glassmorphism Effect */
    .dashboard-header {
        padding: 28px 32px;
        margin-bottom: 32px;
        position: relative;
        overflow: hidden;
        border-radius: var(--rounded-2xl);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: var(--shadow-lg);
        transition: var(--transition-slow);
    }

    .dashboard-header:hover {
        box-shadow: var(--shadow-xl);
        transform: translateY(-1px);
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
        font-family: 'Space Grotesk', sans-serif;
        font-weight: 800;
        margin: 0;
        font-size: 32px;
        display: flex;
        align-items: center;
        letter-spacing: -0.75px;
        line-height: 1.3;
        color: var(--darker);
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
        font-weight: 500;
    }

    .breadcrumb-item:hover {
        color: var(--primary-dark);
    }

    .breadcrumb-item.active {
        color: var(--primary);
        font-weight: 600;
    }

    .breadcrumb-divider {
        margin: 0 8px;
        color: var(--gray-400);
        font-size: 12px;
        opacity: 0.6;
    }

    /* User Profile Section */
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

    .notifications:hover, .notifications:focus {
        color: var(--dark);
        background: rgba(255, 255, 255, 0.95);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        outline: none;
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

    .profile-dropdown:hover, .profile-dropdown:focus {
        background: rgba(255, 255, 255, 0.95);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        outline: none;
    }

    .profile-avatar {
        width: 44px;
        height: 44px;
        border-radius: var(--rounded-full);
        overflow: hidden;
        border: 2px solid rgba(255, 255, 255, 0.8);
        box-shadow: var(--shadow-xs);
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

    /* Metrics Section with Enhanced Visual Hierarchy */
    .metrics-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 28px;
        margin-bottom: 40px;
    }

    .metric-card {
        background: white;
        border-radius: var(--rounded-2xl);
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
        width: 56px;
        height: 56px;
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

    /* Section Headers with Improved Typography */
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

    /* Quick Actions Section */
    .actions-section {
        background: white;
        border-radius: var(--rounded-2xl);
        padding: 32px;
        margin-bottom: 40px;
        box-shadow: var(--shadow);
        transition: var(--transition-slow);
    }

    .actions-section:hover {
        box-shadow: var(--shadow-lg);
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 24px;
    }

    .action-card {
        background: white;
        border-radius: var(--rounded-xl);
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

    .action-card.add-new:hover, .action-card.add-new:focus {
        background: linear-gradient(135deg, rgba(241, 245, 249, 0.7) 0%, rgba(248, 250, 252, 0.7) 100%);
        border-color: var(--primary);
    }

    .action-icon {
        width: 56px;
        height: 56px;
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

    .action-card:hover .action-hover, .action-card:focus .action-hover {
        opacity: 1;
        transform: translateY(-2px);
    }

    /* Bottom Section Layout */
    .bottom-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 32px;
    }

    @media (max-width: 1200px) {
        .bottom-section {
            grid-template-columns: 1fr;
        }
    }

    /* Calendar Section with Enhanced Styling */
    .calendar-section {
        background: white;
        border-radius: var(--rounded-2xl);
        padding: 32px;
        margin-bottom: 0;
        box-shadow: var(--shadow);
        transition: var(--transition-slow);
    }

    .calendar-section:hover {
        box-shadow: var(--shadow-lg);
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

    .fc .fc-button:hover, .fc .fc-button:focus {
        background-color: var(--gray-100);
        outline: none;
        box-shadow: var(--shadow);
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

    /* Activity Section with Refined Design */
    .activity-section {
        background: white;
        border-radius: var(--rounded-2xl);
        padding: 32px;
        box-shadow: var(--shadow);
        transition: var(--transition-slow);
    }

    .activity-section:hover {
        box-shadow: var(--shadow-lg);
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
        gap: 20px;
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

    /* Enhanced Button System */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 20px;
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
        padding: 10px 16px;
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
        transform: translateY(-1px);
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
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }

    .btn-outline:active {
        transform: translateY(0);
    }

    /* Event Modal with Enhanced Design */
    .event-modal {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        border-radius: var(--rounded-2xl);
        padding: 32px;
        width: 90%;
        max-width: 520px;
        z-index: 1001;
        box-shadow: var(--shadow-2xl);
        animation: modalFadeIn 0.3s ease-out;
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

    /* Focus styles for accessibility */
    a:focus, button:focus, [tabindex="0"]:focus {
        outline: 2px solid var(--primary);
        outline-offset: 4px;
        border-radius: var(--rounded-sm);
    }

    /* Responsive Design */
    @media (max-width: 1280px) {
        .dashboard-container {
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
        .dashboard-container {
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
        
        .dashboard-title {
            font-size: 28px;
        }
        
        .metric-value {
            font-size: 28px;
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
    });<?php require_once "header.php"; ?>

<!-- Modern CSS Framework -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">

<div class="dashboard-container">
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="header-left">
                <h1 class="dashboard-title">
                    <i class="fas fa-chalkboard-teacher mr-2"></i> Educator Dashboard
                </h1>
                <nav class="breadcrumb">
                    <span class="breadcrumb-item active">Dashboard</span>
                    <span class="breadcrumb-divider">/</span>
                    <span class="breadcrumb-item">Overview</span>
                </nav>
            </div>
            <div class="user-profile">
                <div class="notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </div>
                <div class="profile-dropdown">
                    <div class="profile-icon">
                        <i class="fas fa-user-circle"></i>
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

    <!-- Key Metrics Section -->
    <div class="metrics-section">
        <!-- Total Students Card -->
        <div class="metric-card student-card">
            <div class="metric-icon">
                <div class="icon-bg">
                    <i class="fas fa-user-graduate"></i>
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
        </div>

        <!-- Active Classes Card -->
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
        </div>

        <!-- Attendance Rate Card -->
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
        </div>
    </div>
        <!-- Quick Actions Section -->
        <div class="actions-section">
            <div class="section-header">
                <h2><i class="fas fa-bolt mr-2"></i>Quick Actions</h2>
            </div>
            <div class="action-grid">
                <a href="lesson_notes.php" class="action-card">
                    <div class="action-icon notes">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3>Lesson Notes</h3>
                    <p>Create and manage your teaching materials</p>
                </a>
                <a href="login.php" class="action-card">
                    <div class="action-icon attendance">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Attendance</h3>
                    <p>Mark and track student attendance</p>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon assignments">
                        <i class="fas fa-tasks"></i>
                    </div>
                    <h3>Assignments</h3>
                    <p>Create and grade student work</p>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon reports">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Reports</h3>
                    <p>Generate performance reports</p>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon messages">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h3>Messages</h3>
                    <p>Communicate with students/parents</p>
                </a>
                <a href="#" class="action-card">
                    <div class="action-icon resources">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h3>Resources</h3>
                    <p>Access teaching resources</p>
                    </a>
                    <a href="behaviour.php" class="action-card">
                    <div class="action-icon resources">
                    <i class="fas fa-clipboard-list"></i>
                    </div>
                    <h3>Skill Evaluation</h3>
                    <p>Evaluate behaviour and soft skills</p>
                </a>
            </div>
        </div>

        <!-- Calendar and Activity Section -->
        <div class="bottom-section">
            <!-- Calendar Section -->
            <div class="calendar-section">
                <div class="section-header">
                    <h2><i class="far fa-calendar-alt mr-2"></i>Academic Calendar</h2>
                    <div class="calendar-actions">
                        <button class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add Event
                        </button>
                    </div>
                </div>
                <div id="calendar"></div>
            </div>

            <!-- Recent Activity Section -->
            <div class="activity-section">
                <div class="section-header">
                    <h2><i class="fas fa-bell mr-2"></i>Recent Activity</h2>
                    <a href="#" class="view-all">View All</a>
                </div>
                <div class="activity-list">
                    <div class="activity-item">
                        <div class="activity-icon success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <h3>Attendance Marked</h3>
                                <span class="activity-time">2h ago</span>
                            </div>
                            <p>Class 10A - Mathematics (32 students present)</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon primary">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <h3>Lesson Notes Uploaded</h3>
                                <span class="activity-time">1d ago</span>
                            </div>
                            <p>Week 5 materials for all classes</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon info">
                            <i class="fas fa-comment-alt"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <h3>New Message</h3>
                                <span class="activity-time">2d ago</span>
                            </div>
                            <p>From Parent: Jane Doe (Regarding: Term Project)</p>
                        </div>
                    </div>
                    <div class="activity-item">
                        <div class="activity-icon warning">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-header">
                                <h3>Assignment Due</h3>
                                <span class="activity-time">3d ago</span>
                            </div>
                            <p>Algebra II assignment due tomorrow</p>
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

<script>
    $(document).ready(function () {
        // Initialize Student Distribution Chart
        const studentCtx = document.getElementById('studentDonutChart').getContext('2d');
        const studentDonutChart = new Chart(studentCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php
                        $classData = $conn->query("SELECT class, COUNT(*) as studentCount FROM student GROUP BY class");
                        $labels = [];
                        $data = [];
                        while ($row = $classData->fetchArray(SQLITE3_ASSOC)) {
                            $labels[] = "'" . $row['class'] . "'";
                            $data[] = $row['studentCount'];
                        }
                        echo implode(",", $labels);
                    ?>
                ],
                datasets: [{
                    data: [<?php echo implode(",", $data); ?>],
                    backgroundColor: [
                        '#6366F1', '#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#8B5CF6'
                    ],
                    borderWidth: 0,
                    cutout: '75%'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 10,
                            padding: 20,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: {
                                family: 'Inter',
                                size: 12
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        titleFont: { 
                            family: 'Inter',
                            size: 14,
                            weight: '600' 
                        },
                        bodyFont: { 
                            family: 'Inter',
                            size: 12 
                        },
                        padding: 12,
                        usePointStyle: true,
                        cornerRadius: 8,
                        displayColors: false
                    }
                }
            }
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
            themeSystem: 'standard',
            events: [
                {
                    title: 'Parent-Teacher Meeting',
                    start: new Date(),
                    backgroundColor: '#6366F1',
                    borderColor: '#6366F1'
                },
                {
                    title: 'Term Assessment',
                    start: new Date(new Date().setDate(new Date().getDate() + 5)),
                    backgroundColor: '#10B981',
                    borderColor: '#10B981'
                },
                {
                    title: 'Staff Development Day',
                    start: new Date(new Date().setDate(new Date().getDate() + 10)),
                    end: new Date(new Date().setDate(new Date().getDate() + 11)),
                    backgroundColor: '#F59E0B',
                    borderColor: '#F59E0B',
                    allDay: true
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
                            ${info.event.end ? `<p><i class="fas fa-clock"></i> ${info.event.start.toLocaleTimeString()} - ${info.event.end.toLocaleTimeString()}</p>` : ''}
                            <div class="modal-actions">
                                <button class="btn btn-secondary">Edit</button>
                                <button class="btn btn-primary">View Details</button>
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
            height: 'auto'
        });
        calendar.render();
    });
</script>

<style>
    :root {
        --primary: #6366F1;
        --primary-light: #C7D2FE;
        --primary-dark: #4F46E5;
        --secondary: #6B7280;
        --success: #10B981;
        --success-light: #D1FAE5;
        --info: #3B82F6;
        --info-light: #DBEAFE;
        --warning: #F59E0B;
        --warning-light: #FEF3C7;
        --danger: #EF4444;
        --danger-light: #FEE2E2;
        --light: #F9FAFB;
        --dark: #1F2937;
        --gray-100: #F3F4F6;
        --gray-200: #E5E7EB;
        --gray-300: #D1D5DB;
        --gray-700: #374151;
        --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        --rounded-sm: 0.125rem;
        --rounded: 0.25rem;
        --rounded-md: 0.375rem;
        --rounded-lg: 0.5rem;
        --rounded-xl: 0.75rem;
        --rounded-full: 9999px;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Inter', sans-serif;
        background-color: var(--light);
        color: var(--dark);
        line-height: 1.5;
    }

    .dashboard-container {
        max-width: 1800px;
        margin: 0 auto;
        padding: 24px;
    }

    /* Dashboard Header */
    .dashboard-header {
        background: white;
        border-radius: var(--rounded-xl);
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: var(--shadow-sm);
    }

    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .header-left {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .dashboard-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        font-size: 1.75rem;
        display: flex;
        align-items: center;
    }

    .breadcrumb {
        display: flex;
        align-items: center;
        font-size: 0.875rem;
        color: var(--secondary);
    }

    .breadcrumb-item {
        color: var(--secondary);
    }

    .breadcrumb-item.active {
        color: var(--primary);
        font-weight: 500;
    }

    .breadcrumb-divider {
        margin: 0 8px;
        color: var(--gray-300);
    }

    .user-profile {
        display: flex;
        align-items: center;
        gap: 20px;
    }

    .notifications {
        position: relative;
        font-size: 1.25rem;
        color: var(--secondary);
        cursor: pointer;
        transition: color 0.2s;
    }

    .notifications:hover {
        color: var(--dark);
    }

    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: var(--danger);
        color: white;
        border-radius: var(--rounded-full);
        width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.65rem;
        font-weight: 600;
    }

    .profile-dropdown {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 8px 12px;
        border-radius: var(--rounded-lg);
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .profile-dropdown:hover {
        background-color: var(--gray-100);
    }

    .profile-icon {
        font-size: 2rem;
        color: var(--primary);
    }

    .profile-info {
        display: flex;
        flex-direction: column;
    }

    .user-name {
        font-weight: 600;
        font-size: 0.9375rem;
        color: var(--dark);
    }

    .user-role {
        font-size: 0.8125rem;
        color: var(--secondary);
    }

    .dropdown-arrow {
        font-size: 0.75rem;
        color: var(--secondary);
        transition: transform 0.2s;
    }

    .profile-dropdown:hover .dropdown-arrow {
        transform: rotate(180deg);
    }

    /* Metrics Section */
    .metrics-section {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 20px;
        margin-bottom: 24px;
    }

    .metric-card {
        background: white;
        border-radius: var(--rounded-xl);
        padding: 24px;
        box-shadow: var(--shadow-sm);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        display: flex;
        gap: 16px;
        align-items: center;
    }

    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
    }

    .student-card {
        border-left: 4px solid var(--primary);
    }

    .classes-card {
        border-left: 4px solid var(--info);
    }

    .attendance-card {
        border-left: 4px solid var(--success);
    }

    .tasks-card {
        border-left: 4px solid var(--warning);
    }

    .metric-icon {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .icon-bg {
        width: 48px;
        height: 48px;
        border-radius: var(--rounded-lg);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .student-card .icon-bg {
        background-color: var(--primary-light);
        color: var(--primary-dark);
    }

    .classes-card .icon-bg {
        background-color: var(--info-light);
        color: var(--info);
    }

    .attendance-card .icon-bg {
        background-color: var(--success-light);
        color: var(--success);
    }

    .tasks-card .icon-bg {
        background-color: var(--warning-light);
        color: var(--warning);
    }

    .metric-icon i {
        font-size: 1.25rem;
    }

    .metric-content {
        flex: 1;
    }

    .metric-label {
        display: block;
        font-size: 0.875rem;
        color: var(--secondary);
        margin-bottom: 4px;
    }

    .metric-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 4px;
    }

    .metric-trend {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.8125rem;
        color: var(--success);
    }

    .metric-trend.negative {
        color: var(--danger);
    }

    .metric-trend i {
        font-size: 0.75rem;
    }

    /* Content Area */
    .content-area {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    /* Distribution Section */
    .distribution-section, .actions-section, .calendar-section, .activity-section {
        background: white;
        border-radius: var(--rounded-xl);
        padding: 24px;
        box-shadow: var(--shadow-sm);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .section-header h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        margin: 0;
        display: flex;
        align-items: center;
    }

    .section-header h2 i {
        margin-right: 10px;
        font-size: 1.1em;
    }

    .form-select {
        padding: 8px 12px;
        font-size: 0.875rem;
        border-radius: var(--rounded-md);
        border: 1px solid var(--gray-200);
        background-color: white;
        color: var(--dark);
        cursor: pointer;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }

    .chart-container {
        height: 300px;
        position: relative;
    }

    /* Actions Section */
    .action-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }

    .action-card {
        background: white;
        border-radius: var(--rounded-lg);
        padding: 20px;
        text-decoration: none;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: 1px solid var(--gray-200);
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-light);
    }

    .action-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--rounded-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        font-size: 1.25rem;
        color: white;
    }

    .action-icon.notes {
        background-color: var(--primary);
    }

    .action-icon.attendance {
        background-color: var(--success);
    }

    .action-icon.assignments {
        background-color: var(--warning);
    }

    .action-icon.reports {
        background-color: var(--info);
    }

    .action-icon.messages {
        background-color: #8B5CF6;
    }

    .action-icon.resources {
        background-color: #EC4899;
    }

    .action-card h3 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--dark);
        margin-bottom: 8px;
    }

    .action-card p {
        font-size: 0.875rem;
        color: var(--secondary);
        margin: 0;
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
    #calendar {
        margin-top: 16px;
    }

    .fc {
        font-family: 'Inter', sans-serif;
    }

    .fc .fc-toolbar-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark);
    }

    .fc .fc-button {
        background-color: white;
        border: 1px solid var(--gray-200);
        color: var(--dark);
        font-size: 0.875rem;
        font-weight: 500;
        padding: 6px 12px;
        border-radius: var(--rounded-md);
        transition: all 0.2s;
    }

    .fc .fc-button:hover {
        background-color: var(--gray-100);
    }

    .fc .fc-button-primary:not(:disabled).fc-button-active {
        background-color: var(--primary);
        border-color: var(--primary);
    }

    .fc .fc-daygrid-day-number {
        color: var(--dark);
        font-weight: 500;
        padding: 4px;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: var(--primary-light);
    }

    .fc .fc-daygrid-event {
        border-radius: var(--rounded-sm);
        padding: 2px 4px;
        font-size: 0.8125rem;
    }

    .fc .fc-daygrid-event-dot {
        display: none;
    }

    /* Activity Section */
    .view-all {
        font-size: 0.875rem;
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
        transition: color 0.2s;
    }

    .view-all:hover {
        color: var(--primary-dark);
        text-decoration: underline;
    }

    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .activity-item {
        display: flex;
        gap: 16px;
        padding: 16px;
        border-radius: var(--rounded-lg);
        background: var(--light);
        transition: all 0.3s ease;
        border: 1px solid var(--gray-200);
    }

    .activity-item:hover {
        background: white;
        transform: translateX(5px);
        box-shadow: var(--shadow-sm);
        border-color: var(--gray-300);
    }

    .activity-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--rounded-full);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: white;
    }

    .activity-icon.success {
        background-color: var(--success);
    }

    .activity-icon.primary {
        background-color: var(--primary);
    }

    .activity-icon.info {
        background-color: var(--info);
    }

    .activity-icon.warning {
        background-color: var(--warning);
    }

    .activity-content {
        flex: 1;
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 4px;
    }

    .activity-header h3 {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--dark);
    }

    .activity-time {
        font-size: 0.8125rem;
        color: var(--secondary);
    }

    .activity-content p {
        font-size: 0.875rem;
        color: var(--secondary);
        margin: 0;
    }

    /* Buttons */
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        border-radius: var(--rounded-md);
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.5;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid transparent;
        gap: 8px;
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8125rem;
    }

    .btn-primary {
        background-color: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .btn-primary:hover {
        background-color: var(--primary-dark);
        border-color: var(--primary-dark);
    }

    .btn-secondary {
        background-color: white;
        color: var(--dark);
        border-color: var(--gray-300);
    }

    .btn-secondary:hover {
        background-color: var(--gray-100);
    }

    .btn-outline-secondary {
        background-color: white;
        color: var(--secondary);
        border-color: var(--gray-300);
    }

    .btn-outline-secondary:hover {
        background-color: var(--gray-100);
        color: var(--dark);
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
        z-index: 1000;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
    }

    .modal-header h3 {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--dark);
    }

    .close-modal {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: var(--secondary);
        cursor: pointer;
        transition: color 0.2s;
    }

    .close-modal:hover {
        color: var(--dark);
    }

    .modal-body p {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        color: var(--dark);
    }

    .modal-body i {
        width: 20px;
        text-align: center;
    }

    .modal-actions {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    /* Responsive Design */
    @media (max-width: 1024px) {
        .dashboard-container {
            padding: 16px;
        }
        
        .metrics-section {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 768px) {
        .header-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 16px;
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
    }

    @media (max-width: 576px) {
        .action-grid {
            grid-template-columns: 1fr;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        
        .form-select {
            width: 100%;
        }
    }
</style>

</script>
</body>
</html>
