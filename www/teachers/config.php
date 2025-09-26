<?php
$host = "db.mqtuzltstbshtjigzujz.supabase.co";  // Supabase host
$port = "5432";                                // Supabase port
$dbname = "postgres";                          // Default Supabase DB name
$user = "postgres";                            // Supabase user
$password = "Ernestbizz..123";                 // Your Supabase password

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ Connected successfully"; // Uncomment to test
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>
