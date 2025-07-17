<?php
include 'config.php'; // Include the config file

// Get the JSON input
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['id'], $data['field'], $data['value'])) {
    $id = $data['id'];
    $field = $data['field'];
    $value = $data['value'];

    try {
        // Create a new PDO instance for SQLite
        $pdo = new PDO("sqlite:" . DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Prepare the update statement
        $stmt = $pdo->prepare("UPDATE marks SET $field = :value WHERE id = :id");
        $stmt->bindParam(':value', $value);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
}
?>