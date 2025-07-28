<?php
session_start();
include 'config.php';

// Database connection error handling
if (!$conn || !($conn instanceof PDO)) {
    die("Database connection failed");
}

$message = '';
$error = '';
$has_error = false;

function handleDatabaseError($errorMsg) {
    $_SESSION['error'] = $errorMsg;
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $names = $_POST['name'] ?? [];
    $classes = $_POST['class'] ?? [];
    $years = $_POST['year'] ?? [];

    if (count($names) === 0) {
        $_SESSION['error'] = "No students to add.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    try {
        $conn->beginTransaction();
        $insertQuery = "INSERT INTO student_entries (name, class, admission_number, year) VALUES (?, ?, ?, ?)";
        
        foreach ($names as $index => $name) {
            $name = trim($name);
            $class = $classes[$index] ?? '';
            $year = (int)($years[$index] ?? 0);

            if (empty($name) || empty($class) || empty($year)) {
                throw new Exception("All fields are required for each student.");
            }

            // Generate unique admission number
            $admission_number = '';
            $attempt = 0;
            do {
                $baseAdmission = strtoupper(substr(preg_replace('/\s+/', '', $name), 0, 3));
                $baseAdmission = $baseAdmission ?: 'ADM';
                $randomNumber = rand(100, 999);
                $admission_number = $baseAdmission . $randomNumber;

                $checkQuery = "SELECT COUNT(*) FROM student_entries WHERE admission_number = ?";
                $stmt = $conn->prepare($checkQuery);
                $stmt->execute([$admission_number]);
                $count = $stmt->fetchColumn();
                $attempt++;
                
                if ($attempt > 10) {
                    throw new Exception("Failed to generate unique admission number for student: $name");
                }
            } while ($count > 0);

            $stmt = $conn->prepare($insertQuery);
            if (!$stmt->execute([$name, $class, $admission_number, $year])) {
                throw new Exception("Failed to insert student");
            }
        }

        $conn->commit();
        $_SESSION['message'] = "Students added successfully!";
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
    
    $message = $_SESSION['message'] ?? '';
    $error = $_SESSION['error'] ?? '';
    $has_error = !empty($error);
    unset($_SESSION['message'], $_SESSION['error']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Enrollment | Academic Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio,line-clamp"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #6366f1;
            --secondary: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
        }
        
        .toast {
            animation: slideIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55), fadeOut 0.5s 4.5s forwards;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-radius: 0.5rem;
            transform: translateY(0);
            transition: all 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-100px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        .form-input-focus:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
            border-color: var(--primary-light);
        }
        
        .select-custom {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        
        .table-row-hover:hover {
            background-color: #f8fafc;
        }
        
        .btn-primary {
            background-color: var(--primary);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .btn-success {
            background-color: var(--secondary);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-success:hover {
            background-color: #0d9f6e;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .btn-danger {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .smooth-transition {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .card-shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .text-primary {
            color: var(--primary);
        }
        
        .text-secondary {
            color: var(--secondary);
        }
        
        .text-danger {
            color: var(--danger);
        }
        
        .text-warning {
            color: var(--warning);
        }
        
        .text-info {
            color: var(--info);
        }
        
        .text-dark {
            color: var(--dark);
        }
        
        .text-light {
            color: var(--light);
        }
        
        .bg-primary {
            background-color: var(--primary);
        }
        
        .bg-secondary {
            background-color: var(--secondary);
        }
        
        .bg-danger {
            background-color: var(--danger);
        }
        
        .bg-warning {
            background-color: var(--warning);
        }
        
        .bg-info {
            background-color: var(--info);
        }
        
        .bg-dark {
            background-color: var(--dark);
        }
        
        .bg-light {
            background-color: var(--light);
        }
        
        @media (max-width: 640px) {
            .responsive-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            
            .mobile-stack {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white rounded-xl card-shadow-lg overflow-hidden p-6 space-y-6">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex items-center space-x-3">
                    <a href="dashboard.php" class="flex items-center text-primary hover:text-primary-dark transition-colors smooth-transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        <span class="ml-2 font-medium text-sm">Dashboard</span>
                    </a>
                    <span class="text-gray-400">/</span>
                    <h1 class="text-lg font-bold text-gray-900">Student Enrollment</h1>
                </div>
                <div class="flex items-center space-x-2 bg-blue-50 px-3 py-1.5 rounded-full">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span id="row-counter" class="text-xs font-semibold text-blue-800">1 student</span>
                </div>
            </div>
            
            <!-- Divider -->
            <div class="border-b border-gray-200 pb-4">
                <div class="flex flex-col md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Add New Students</h2>
                        <p class="mt-2 text-base text-gray-600 max-w-2xl">Enroll new students into the academic system. All fields are required for each student record.</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <button type="button" onclick="addRow()" 
                            class="btn-primary inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Student
                        </button>
                    </div>
                </div>
            </div>

            <!-- Error Alert -->
            <?php if ($has_error): ?>
                <div class="rounded-md bg-red-50 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-red-800"><?php echo htmlspecialchars($error); ?></h3>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex rounded-md bg-red-50 p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-600 focus:ring-offset-2 focus:ring-offset-red-50">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Success Toast -->
            <?php if (!empty($message)): ?>
                <div id="successToast" class="fixed top-6 right-6 z-50 toast">
                    <div class="rounded-md bg-green-50 p-4 w-80">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-semibold text-green-800"><?php echo htmlspecialchars($message); ?></p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button type="button" onclick="document.getElementById('successToast').style.display='none'" class="inline-flex rounded-md bg-green-50 p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-green-600 focus:ring-offset-2 focus:ring-offset-green-50">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Enrollment Form -->
            <form method="POST" action="" class="space-y-4" id="studentForm">
                <div class="responsive-table overflow-hidden shadow ring-1 ring-black ring-opacity-5 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300" id="studentTable">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Student Name</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Class</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Year</th>
                                <th scope="col" class="px-6 py-3.5 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr class="table-row-hover">
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <input type="text" name="name[]" required 
                                            class="form-input-focus pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2.5 border bg-white text-gray-700 placeholder-gray-400 text-base"
                                            placeholder="Full name" autocomplete="off">
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                            </svg>
                                        </div>
                                        <select name="class[]" required class="select-custom pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2.5 border bg-white text-gray-700 text-base">
                                            <option value="" disabled selected hidden>Select Class</option>
                                            <option value="Basic 1">Basic One B</option>
                            <option value="Basic 6">Basic Six A</option>
                            <option value="Basic 3A">Basic Three A</option>
                            <option value="Basic 3B">Basic Three B</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </div>
                                        <select name="year[]" required class="select-custom pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2.5 border bg-white text-gray-700 text-base">
                                            <option value="" disabled selected hidden>Select Year</option>
                                            <option value="2025">2025</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                    <button type="button" onclick="removeRow(this)" 
                                        class="btn-danger inline-flex items-center px-3.5 py-2.5 border border-red-300 shadow-sm text-sm font-semibold rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0 sm:space-x-4 mobile-stack">
                    <div class="w-full sm:w-auto">
                        <button type="button" onclick="addRow()" 
                            class="btn-primary w-full sm:w-auto inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Add Another Student
                        </button>
                    </div>
                    
                    <div class="w-full sm:w-auto">
                        <button type="submit" 
                            class="btn-success w-full sm:w-auto inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-semibold rounded-md shadow-sm text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            Save All Students
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Update row counter when adding/removing rows
        function updateRowCounter() {
            const rowCount = document.querySelectorAll('#studentTable tbody tr').length;
            const counterText = rowCount === 1 ? '1 student' : `${rowCount} students`;
            document.getElementById('row-counter').textContent = counterText;
        }
        
        // Add new row to the table
        function addRow() {
            const table = document.getElementById("studentTable").getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();
            newRow.className = "table-row-hover";
            
            newRow.innerHTML = `
                <td class="whitespace-nowrap px-6 py-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input type="text" name="name[]" required 
                            class="form-input-focus pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2.5 border bg-white text-gray-700 placeholder-gray-400 text-base"
                            placeholder="Full name" autocomplete="off">
                    </div>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <select name="class[]" required class="select-custom pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2.5 border bg-white text-gray-700 text-base">
                            <option value="" disabled selected hidden>Select Class</option>
                            <option value="Basic 1">Basic One B</option>
                            <option value="Basic 6">Basic Six A</option>
                            <option value="Basic 3A">Basic Three A</option>
                            <option value="Basic 3B">Basic Three B</option>

                        </select>
                    </div>
                </td>
                <td class="whitespace-nowrap px-6 py-4">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <select name="year[]" required class="select-custom pl-10 w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 px-3 py-2.5 border bg-white text-gray-700 text-base">
                            <option value="" disabled selected hidden>Select Year</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                        </select>
                    </div>
                </td>
                <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                    <button type="button" onclick="removeRow(this)" 
                        class="btn-danger inline-flex items-center px-3.5 py-2.5 border border-red-300 shadow-sm text-sm font-semibold rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Remove
                    </button>
                </td>
            `;
            
            // Add animation to new row
            newRow.style.opacity = '0';
            newRow.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                newRow.style.transition = 'all 0.3s ease';
                newRow.style.opacity = '1';
                newRow.style.transform = 'translateY(0)';
            }, 10);
            
            // Focus on the new input field
            setTimeout(() => {
                const input = newRow.querySelector('input');
                if (input) {
                    input.focus();
                }
            }, 350);
            
            updateRowCounter();
        }

        // Remove row from table
        function removeRow(btn) {
            const rows = document.querySelectorAll('#studentTable tbody tr');
            if (rows.length <= 1) {
                // Show a more professional alert/notification
                const alertDiv = document.createElement('div');
                alertDiv.className = 'rounded-md bg-yellow-50 p-4 mb-4';
                alertDiv.innerHTML = `
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-semibold text-yellow-800">You must have at least one student record.</h3>
                        </div>
                        <div class="ml-auto pl-3">
                            <div class="-mx-1.5 -my-1.5">
                                <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'" class="inline-flex rounded-md bg-yellow-50 p-1.5 text-yellow-500 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:ring-offset-2 focus:ring-offset-yellow-50">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
                document.getElementById('studentForm').prepend(alertDiv);
                return;
            }
            
            const row = btn.closest('tr');
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-10px)';
            
            setTimeout(() => {
                row.remove();
                updateRowCounter();
            }, 300);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateRowCounter();
            
            // Auto-close success toast after 5 seconds
            const toast = document.getElementById('successToast');
            if (toast) {
                setTimeout(() => {
                    toast.style.opacity = '0';
                    setTimeout(() => {
                        toast.style.display = 'none';
                    }, 500);
                }, 5000);
            }
            
            // Focus on first input field
            const firstInput = document.querySelector('input[name="name[]"]');
            if (firstInput) {
                setTimeout(() => {
                    firstInput.focus();
                }, 100);
            }
        });
    </script>
</body>
</html>
