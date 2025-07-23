<?php
$host = "dpg-d20bls6mcj7s73avna10-a.oregon-postgres.render.com";
$port = "5432";
$dbname = "school_523q";
$user = "school_523q_user";
$password = "05A4cQnogC1qETghafnFsKNYUxYIRwrv";

$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");

if (!$conn) {
    die("Connection failed: " . pg_last_error());
}
?>

