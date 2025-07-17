<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['teacher_id'])) {
    header('Location: login.php');
    exit();
}

// Get the assigned class from the URL
$class = isset($_GET['class']) ? urldecode($_GET['class']) : '';

if (empty($class)) {
    echo "Error: No class assigned.";
    exit();
}

echo "<h2>Welcome to the $class Dashboard!</h2>";
?>
