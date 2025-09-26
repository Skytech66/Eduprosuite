<?php
$host = "aws-1-eu-north-1.pooler.supabase.com";  // Supabase connection pooler host
$port = "6543";                                  // Pooler port (NOT 5432)
$dbname = "postgres";                            // Default DB name
$user = "postgres.mqtuzltstbshtjigzujz";         // Supabase user with project suffix
$password = "Ernestbizz..123";                   // Your Supabase password

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Connected successfully";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>
