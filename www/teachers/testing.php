<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Supabase connection details
$host = "db.mqtuzltstbshtjigzujz.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "Ernestbizz..123"; // reset if needed

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    $conn = new PDO($dsn, $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Connected successfully to Supabase!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}

