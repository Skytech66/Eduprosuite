<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduPro Suite 2.0 | Premium School Management System</title>
    
    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700&display=swap" rel="stylesheet">
    <!-- MDB Pro -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <!-- Lottie Animations -->
    <script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

    <style>
        :root {
            --primary-900: #1a1a3a;
            --primary-800: #2d2d5a;
            --primary-700: #3e3e7a;
            --primary-600: #4e54c8;
            --primary-500: #6a6ddf;
            --primary-400: #8f94fb;
            --primary-300: #b5b8ff;
            --primary-200: #d9daff;
            --primary-100: #f0f1ff;
            
            --accent-600: #d4af37;
            --accent-500: #e8c252;
            --accent-400: #f5d97e;
            --accent-300: #f9e6a8;
            --accent-200: #fcf2d3;
            
            --neutral-900: #121212;
            --neutral-800: #1e1e1e;
            --neutral-700: #2d2d2d;
            --neutral-600: #424242;
            --neutral-500: #686868;
            --neutral-400: #9e9e9e;
            --neutral-300: #c2c2c2;
            --neutral-200: #e0e0e0;
            --neutral-100: #f5f5f5;
            --neutral-50: #fafafa;
            
            --success-500: #4caf50;
            --warning-500: #ff9800;
            --error-500: #f44336;
            
            --border-radius-sm: 8px;
            --border-radius-md: 12px;
            --border-radius-lg: 16px;
            --border-radius-xl: 24px;
            
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --shadow-xl: 0 20px 25px rgba(0,0,0,0.1);
            --shadow-xxl: 0 25px 50px -12px rgba(0,0,0,0.25);
            
            --transition-fast: 0.15s ease;
            --transition-medium: 0.3s ease;
            --transition-slow: 0.45s ease;
            
            --max-width-sm: 480px;
            --max-width-md: 768px;
            --max-width-lg: 1024px;
            
            --font-display: 'Plus Jakarta Sans', sans-serif;
            --font-body: 'Inter', sans-serif;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: var(--font-body);
            color: var(--neutral-800);
            background-color: var(--neutral-50);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Splash Screen */
        #splash-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            background: linear-gradient(135deg, var(--primary-900) 0%, var(--primary-700) 100%);
            opacity: 1;
            transition: opacity 0.8s var(--transition-slow), transform 0.8s var(--transition-slow);
        }

        .splash-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            max-width: 90%;
            text-align: center;
        }

        .splash-logo-container {
            margin-bottom: 2.5rem;
            position: relative;
        }

        #splash-image {
            width: 120px;
            height: 120px;
            object-fit: contain;
            filter: drop-shadow(0 0 20px rgba(143, 148, 251, 0.6));
            transform: translateZ(0);
            transition: transform 0.4s var(--transition-medium);
        }

        .splash-logo-text {
            font-family: var(--font-display);
            font-weight: 700;
            font-size: 2.25rem;
            color: white;
            margin-top: 1.5rem;
            letter-spacing: 0.5px;
            line-height: 1.2;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .splash-tagline {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.8);
            letter-spacing: 0.1em;
            text-transform: uppercase;
            margin-top: 0.75rem;
        }

        .loading-container {
            width: 100%;
            max-width: 320px;
            margin-top: 2.5rem;
        }

        .loading-bar {
            width: 100%;
            height: 4px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .loading-progress {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, var(--primary-600), var(--accent-600));
            transition: width 0.6s var(--transition-medium);
            border-radius: 2px;
        }

        .loading-text {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            letter-spacing: 0.5px;
            text-align: center;
        }

        /* Main Content */
        #main-content {
            display: none;
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .auth-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1635070041078-e363dbe005cb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80') no-repeat center center;
            background-size: cover;
            z-index: 0;
        }

        .auth-background::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 12, 41, 0.92) 0%, rgba(48, 43, 99, 0.92) 100%);
            z-index: 1;
        }

        .auth-container {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 100vh;
            padding: 2rem;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: var(--border-radius-xl);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            box-shadow: var(--shadow-xxl);
            overflow: hidden;
            transform: translateZ(0);
            transition: transform var(--transition-medium), box-shadow var(--transition-medium);
        }

        .login-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 30px 60px -10px rgba(0,0,0,0.3);
        }

        .card-header {
            padding: 2.5rem 2.5rem 1.5rem;
            text-align: center;
        }

        .logo-container {
            margin-bottom: 1.5rem;
        }

        .logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin: 0 auto 1.25rem;
            filter: drop-shadow(0 4px 12px rgba(143, 148, 251, 0.4));
        }

        .logo-text {
            font-family: var(--font-display);
            font-size: 1.75rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }

        .logo-subtext {
            font-size: 0.8125rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .card-body {
            padding: 0 2.5rem 2.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.75rem;
            letter-spacing: 0.5px;
        }

        .input-group {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 0.9375rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--border-radius-md);
            color: white;
            transition: all var(--transition-medium);
            padding-right: 3rem;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--accent-600);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.2);
            background: rgba(255, 255, 255, 0.15);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
            font-weight: 400;
        }

        .input-icon {
            position: absolute;
            right: 1.25rem;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: none;
            padding: 0.9375rem 1.5rem;
            font-size: 0.9375rem;
            line-height: 1.5;
            border-radius: var(--border-radius-md);
            transition: all var(--transition-medium);
            cursor: pointer;
            position: relative;
            overflow: hidden;
            z-index: 1;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-600) 0%, var(--primary-700) 100%);
            color: white;
            width: 100%;
            box-shadow: 0 8px 20px rgba(78, 84, 200, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(78, 84, 200, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, var(--primary-700) 0%, var(--primary-800) 100%);
            opacity: 0;
            transition: opacity var(--transition-medium);
            z-index: -1;
        }

        .btn-primary:hover::before {
            opacity: 1;
        }

        .btn-icon {
            margin-right: 0.75rem;
            font-size: 1rem;
        }

        .link-secondary {
            display: inline-flex;
            align-items: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.8125rem;
            font-weight: 500;
            text-decoration: none;
            transition: color var(--transition-fast);
            margin-top: 1.5rem;
        }

        .link-secondary:hover {
            color: var(--accent-500);
            text-decoration: none;
        }

        .link-icon {
            margin-right: 0.5rem;
            font-size: 0.875rem;
        }

        .security-notice {
            font-size: 0.75rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.6);
            text-align: center;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .security-icon {
            color: var(--accent-600);
            margin-right: 0.5rem;
        }

        .ai-badge {
            position: fixed;
            bottom: 1.5rem;
            left: 50%;
            transform: translateX(-50%);
            font-size: 0.8125rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.9);
            background: rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            padding: 0.625rem 1.25rem;
            border-radius: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            z-index: 10;
            display: inline-flex;
            align-items: center;
        }

        .ai-icon {
            color: var(--accent-600);
            margin-right: 0.5rem;
            font-size: 0.9375rem;
        }

        .hidden-button {
            position: fixed;
            bottom: 20px;
            right: 900px;
            width: 10px;
            height: 20px;
            background: rgba(214, 199, 199, 0.5);
            cursor: pointer;
            z-index: 1000;
            border-radius: 10px;
        }

        .hidden-button:hover {
            cursor: pointer;
            background: rgba(194, 181, 181, 0.5);
        }

        /* Spinner Overlay */
        .spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 12, 41, 0.95);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity var(--transition-medium);
        }

        .spinner-content {
            text-align: center;
            max-width: 90%;
        }

        .spinner-logo {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 1.5rem;
            animation: spin 1.5s linear infinite;
            filter: drop-shadow(0 0 15px rgba(143, 148, 251, 0.7));
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .spinner-text {
            font-size: 1.125rem;
            font-weight: 600;
            color: white;
            letter-spacing: 0.5px;
        }

        /* Floating Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .login-card {
                max-width: 400px;
            }
            
            .card-header,
            .card-body {
                padding: 2rem;
            }
            
            .logo {
                width: 72px;
                height: 72px;
            }
            
            .logo-text {
                font-size: 1.625rem;
            }
        }

        @media (max-width: 480px) {
            .auth-container {
                padding: 1.5rem;
            }
            
            .login-card {
                max-width: 100%;
            }
            
            .card-header,
            .card-body {
                padding: 1.75rem 1.5rem;
            }
            
            .logo {
                width: 64px;
                height: 64px;
                margin-bottom: 1rem;
            }
            
            .logo-text {
                font-size: 1.5rem;
            }
            
            .form-control {
                padding: 0.875rem 1rem;
                padding-right: 2.75rem;
            }
            
            .input-icon {
                right: 1rem;
                font-size: 0.9375rem;
            }
            
            .btn {
                padding: 0.875rem 1.25rem;
            }
            
            .ai-badge {
                font-size: 0.75rem;
                padding: 0.5rem 1rem;
            }
        }
    </style>
</head>
<body>

    <!-- Splash Screen -->
    <div id="splash-screen" class="animate__animated animate__fadeIn">
        <div class="splash-content">
            <div class="splash-logo-container">
                <img id="splash-image" src="logo.png" alt="EduPro Loading" class="animate__animated animate__pulse">
                <h1 class="splash-logo-text">EduPro Suite 2.0</h1>
                <p class="splash-tagline">Premium School Management System</p>
            </div>
            
            <div class="loading-container">
                <div class="loading-bar">
                    <div class="loading-progress" id="loading-progress"></div>
                </div>
                <p class="loading-text" id="loading-text">Initializing secure connection...</p>
            </div>
        </div>
        
        <lottie-player 
            src="https://assets4.lottiefiles.com/packages/lf20_pojzucga.json" 
            background="transparent" 
            speed="1" 
            style="position: absolute; bottom: 20px; right: 20px; width: 100px; height: 100px;" 
            loop autoplay>
        </lottie-player>
    </div>

    <!-- Main Content -->
    <div id="main-content">
        <div class="auth-background"></div>
        
        <div class="auth-container">
            <div class="login-card animate__animated animate__fadeInUp">
                <div class="card-header">
                    <div class="logo-container">
                        <img src="./images/logo.png" alt="EduPro Logo" class="logo">
                        <h2 class="logo-text">EduPro Suite 2.0</h2>
                        <p class="logo-subtext">Premium Education Portal</p>
                    </div>
                </div>
                
                <div class="card-body">
                    <form id="loginForm" action="www/include/action.php" method="POST" aria-label="Login Form">
                        <div class="form-group animate__animated animate__fadeIn animate__delay-1s">
                            <label for="uname" class="form-label">Username</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="uname" name="uname" placeholder="Enter your username" required aria-required="true">
                                <span class="input-icon"><i class="fas fa-user"></i></span>
                            </div>
                        </div>
                        
                        <div class="form-group animate__animated animate__fadeIn animate__delay-2s">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password" minlength="8" placeholder="Enter your password" required aria-required="true">
                                <span class="input-icon"><i class="fas fa-lock"></i></span>
                            </div>
                        </div>
                        
                        <button type="submit" name="submit" value="login" class="btn btn-primary animate__animated animate__fadeIn animate__delay-3s">
                            <i class="fas fa-sign-in-alt btn-icon"></i> Access Portal
                        </button>
                        
                        <a href="admin/qq/Parent_login.php" class="link-secondary animate__animated animate__fadeIn animate__delay-4s">
                            <i class="fas fa-user-friends link-icon"></i> Parent Access
                        </a>
                    </form>
                    
                    <p class="security-notice animate__animated animate__fadeIn animate__delay-5s">
                        <i class="fas fa-shield-alt security-icon"></i> Unauthorized access will be prohibited
                    </p>
                </div>
            </div>
        </div>
        
        <div class="ai-badge">
            <i class="fas fa-brain ai-icon"></i> AI-Powered Learning System
            <a id="devBtn" href="dev.php" class="hidden-button"></a>
        </div>
    </div>

    <!-- Spinner Overlay -->
    <div id="spinnerOverlay" class="spinner-overlay">
        <div class="spinner-content">
            <img src="./images/logo.png" alt="Loading" class="spinner-logo">
            <p class="spinner-text">Authenticating credentials...</p>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.js"></script>
    <script>
        // Enhanced loading sequence with progress tracking
        const loadingPhases = [
            { text: "Initializing secure connection...", duration: 800 },
            { text: "Loading system modules...", duration: 1200 },
            { text: "Authenticating environment...", duration: 1000 },
            { text: "Preparing dashboard...", duration: 1500 }
        ];
        
        const splashScreen = document.getElementById('splash-screen');
        const loadingText = document.getElementById('loading-text');
        const loadingProgress = document.getElementById('loading-progress');
        const spinnerOverlay = document.getElementById('spinnerOverlay');
        const loginForm = document.getElementById('loginForm');
        const mainContent = document.getElementById('main-content');

        // Simulate loading progress
        function simulateLoading() {
            let progress = 0;
            const totalDuration = loadingPhases.reduce((sum, phase) => sum + phase.duration, 0);
            let elapsed = 0;
            
            loadingPhases.forEach((phase, index) => {
                setTimeout(() => {
                    loadingText.textContent = phase.text;
                    
                    // Update progress bar smoothly during this phase
                    const phaseStart = elapsed;
                    const phaseEnd = elapsed + phase.duration;
                    const phaseProgressInterval = setInterval(() => {
                        elapsed += 50;
                        progress = Math.min((elapsed / totalDuration) * 100, 100);
                        loadingProgress.style.width = `${progress}%`;
                        
                        if (elapsed >= phaseEnd) {
                            clearInterval(phaseProgressInterval);
                            
                            // If this is the last phase, complete the loading
                            if (index === loadingPhases.length - 1) {
                                setTimeout(() => {
                                    splashScreen.style.opacity = '0';
                                    setTimeout(() => {
                                        splashScreen.style.display = 'none';
                                        mainContent.style.display = 'block';
                                    }, 800);
                                }, 300);
                            }
                        }
                    }, 50);
                }, elapsed);
                
                elapsed += phase.duration;
            });
        }

        // Start loading sequence after short delay
        setTimeout(simulateLoading, 500);

        // Add subtle animations to form elements when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const animatedElements = document.querySelectorAll('.animate__animated');
            animatedElements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 150 + 100);
            });
        });

        // Handle form submission to show spinner
        loginForm.addEventListener('submit', function(e) {
            // Show the spinner overlay
            spinnerOverlay.style.display = 'flex';
            setTimeout(() => {
                spinnerOverlay.style.opacity = '1';
            }, 10);
            
            // The form will continue with its normal submission
        });

        // Add hover effect to splash logo
        const splashImage = document.getElementById('splash-image');
        if (splashImage) {
            splashImage.addEventListener('mouseenter', () => {
                splashImage.style.transform = 'scale(1.05)';
            });
            
            splashImage.addEventListener('mouseleave', () => {
                splashImage.style.transform = 'scale(1)';
            });
        }
    </script>
</body>
</html>
