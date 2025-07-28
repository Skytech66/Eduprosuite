<?php
$host = "dpg-d20bls6mcj7s73avna10-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "school_523q";
$user = "school_523q_user";
$password = "05A4cQnogC1qETghafnFsKNYUxYIRwrv";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
