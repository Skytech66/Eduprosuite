<?php
include 'config.php'; // Include the config file

try {
    $pdo = new PDO("sqlite:" . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['class'])) {
        $class = $_GET['class'];
        $stmt = $pdo->prepare("SELECT * FROM students WHERE class = :class");
        $stmt->bindParam(':class', $class);
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($students);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>