<?php
include 'config.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required arrays
    $fields = ['student_name', 'critical_thinking', 'logical_reasoning', 'collaboration', 'creativity', 'communication', 'behavior', 'notes'];
    foreach ($fields as $field) {
        if (!isset($_POST[$field]) || !is_array($_POST[$field])) {
            die("Invalid form submission: missing $field.");
        }
    }

    // Begin transaction
    $conn->begin_transaction();

    try {
        // Prepare insert statement for behaviour table
        $stmt = $conn->prepare("
            INSERT INTO behaviour (
                student_name, critical_thinking, logical_reasoning, collaboration, creativity, communication, behavior, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        $count = count($_POST['student_name']);

        for ($i = 0; $i < $count; $i++) {
            $studentName = trim($_POST['student_name'][$i]);

            // Validate ratings; allow null if empty, else enforce 1-5
            $criticalThinking = isset($_POST['critical_thinking'][$i]) && $_POST['critical_thinking'][$i] !== '' ? max(1, min(5, (int)$_POST['critical_thinking'][$i])) : null;
            $logicalReasoning = isset($_POST['logical_reasoning'][$i]) && $_POST['logical_reasoning'][$i] !== '' ? max(1, min(5, (int)$_POST['logical_reasoning'][$i])) : null;
            $collaboration = isset($_POST['collaboration'][$i]) && $_POST['collaboration'][$i] !== '' ? max(1, min(5, (int)$_POST['collaboration'][$i])) : null;
            $creativity = isset($_POST['creativity'][$i]) && $_POST['creativity'][$i] !== '' ? max(1, min(5, (int)$_POST['creativity'][$i])) : null;
            $communication = isset($_POST['communication'][$i]) && $_POST['communication'][$i] !== '' ? max(1, min(5, (int)$_POST['communication'][$i])) : null;

            // Behavior and notes as strings trimmed
            $behavior = isset($_POST['behavior'][$i]) ? trim($_POST['behavior'][$i]) : '';
            $notes = isset($_POST['notes'][$i]) ? trim($_POST['notes'][$i]) : '';

            $stmt->bind_param(
                "siiiiiss",
                $studentName,
                $criticalThinking,
                $logicalReasoning,
                $collaboration,
                $creativity,
                $communication,
                $behavior,
                $notes
            );

            $stmt->execute();

            if ($stmt->error) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
        }

        $stmt->close();

        $conn->commit();

        // Redirect to index with success message
        header("Location: index.php?message=Ratings submitted successfully");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        die("Error submitting ratings: " . htmlspecialchars($e->getMessage()));
    }
} else {
    die("Invalid request method.");
}
?>

