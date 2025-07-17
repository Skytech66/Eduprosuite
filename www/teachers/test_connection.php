<?php
require_once 'db_connection.php';

try {
    echo "Database connection successful!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
