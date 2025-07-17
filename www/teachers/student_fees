<?php
include 'config.php'; // Include your database configuration

// Initialize variables
$action = '';
$fee_id = '';
$amount = '';
$student_id = '';
$fees_collected = [];
$total_fees = 0;
$total_balance = 0;

// Handle form submissions for adding, editing, and deleting fees
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_fee'])) {
        $student_id = $_POST['student_id'];
        $amount = $_POST['amount'];
        $sql = "INSERT INTO fees (student_id, amount) VALUES ('$student_id', '$amount')";
        $conn->query($sql);
    } elseif (isset($_POST['edit_fee'])) {
        $fee_id = $_POST['fee_id'];
        $amount = $_POST['amount'];
        $sql = "UPDATE fees SET amount='$amount' WHERE id='$fee_id'";
        $conn->query($sql);
    } elseif (isset($_POST['delete_fee'])) {
        $fee_id = $_POST['fee_id'];
        $sql = "DELETE FROM fees WHERE id='$fee_id'";
        $conn->query($sql);
    }
}

// Fetch all fees
$sql = "SELECT * FROM fees";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $fees_collected[] = $row;
        $total_fees += $row['amount'];
    }
}

// Calculate total balance (assuming total fees due is a fixed amount, e.g., 1000)
$total_due = 1000; // Example total due amount
$total_balance = $total_due - $total_fees;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Fees Management</title>
</head>
<body>
    <h1>Student Fees Management</h1>

    <h2>Add Fee</h2>
    <form method="POST">
        <input type="hidden" name="fee_id" value="<?php echo $fee_id; ?>">
        <label for="student_id">Student ID:</label>
        <input type="text" name="student_id" required>
        <label for="amount">Amount:</label>
        <input type="number" name="amount" required>
        <button type="submit" name="add_fee">Add Fee</button>
    </form>

    <h2>Fees Collected</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Student ID</th>
            <th>Amount</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($fees_collected as $fee): ?>
        <tr>
            <td><?php echo $fee['id']; ?></td>
            <td><?php echo $fee['student_id']; ?></td>
            <td><?php echo $fee['amount']; ?></td>
            <td>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                    <input type="number" name="amount" value="<?php echo $fee['amount']; ?>" required>
                    <button type="submit" name="edit_fee">Edit</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                    <button type="submit" name="delete_fee">Delete</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <h2>Total Fees Collected: <?php echo $total_fees; ?></h2>
    <h2>Total Balance: <?php echo $total_balance; ?></h2>
</body>
</html>

<?php
$conn->close(); // Close the database connection
?>
