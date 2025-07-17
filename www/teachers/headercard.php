<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'data.php';

// Fetch distinct classes for filtering
$classes = $database->query("SELECT DISTINCT class FROM payments ORDER BY class ASC");

// Fetch selected class
$selected_class = isset($_GET['class']) ? $_GET['class'] : "";

// Query payments based on class
$query = "SELECT * FROM payments";
if (!empty($selected_class)) {
    $query .= " WHERE class = :class";
}

$stmt = $database->prepare($query);
if (!empty($selected_class)) {
    $stmt->bindValue(':class', $selected_class, SQLITE3_TEXT);
}
$results = $stmt->execute();

// Calculate total amount paid
$total_amount_query = "SELECT SUM(amount_paid) as total_amount FROM payments";
$total_amount_result = $database->query($total_amount_query);
$total_amount_row = $total_amount_result->fetchArray(SQLITE3_ASSOC);
$total_amount = $total_amount_row['total_amount'] ? $total_amount_row['total_amount'] : 0; // Default to 0 if NULL

// Calculate total balance
$total_balance_query = "SELECT SUM(balance) as total_balance FROM payments";
$total_balance_result = $database->query($total_balance_query);
$total_balance_row = $total_balance_result->fetchArray(SQLITE3_ASSOC);
$total_balance = $total_balance_row['total_balance'] ? $total_balance_row['total_balance'] : 0; // Default to 0 if NULL

// Calculate total last term arrears
$total_arrears_query = "SELECT SUM(last_term_arrears) as total_arrears FROM payments";
$total_arrears_result = $database->query($total_arrears_query);
$total_arrears_row = $total_arrears_result->fetchArray(SQLITE3_ASSOC);
$total_arrears = $total_arrears_row['total_arrears'] ? $total_arrears_row['total_arrears'] : 0; // Default to 0 if NULL

// Query for students with balances to settle
$balances_query = "SELECT student_name, balance FROM payments WHERE balance > 0";
$balances_result = $database->query($balances_query);

// Query for students with last term arrears
$arrears_query = "SELECT student_name, last_term_arrears FROM payments WHERE last_term_arrears > 0";
$arrears_result = $database->query($arrears_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Fees Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f9f9f9;
        }
        .ai-header {
            background: linear-gradient(135deg, #3B3F5C 0%, #4F46E5 100%); /* Deeper gradient */
            color: white; /* Set text color to white for better contrast */
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Add shadow for depth */
        }
        .subject-title {
            font-size: 2.5em; /* Increase font size */
            font-weight: bold; /* Make the text bold */
            margin: 0; /* Remove default margin */
        }
        .ai-header i {
            margin-right: 8px;
            font-size: 1.5em; /* Increase icon size */
        }
        .table thead th {
            background-color: #3498db;
        }
        footer {
            background-color: #f9f9f9;
        }
        .sidebar {
            height: 100vh;
            background-color: #f9f9f9;
            padding: 20px;
            border-right: 1px solid #ddd;
        }
        .sidebar a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #e2e6ea;
        }
        .main-content {
            padding: 20px;
        }
        .money-card {
            background: #ffffff;
            border: 1px solid #ddd;
            border-radius: 15px;
            padding: 15px;
            text-align: center;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .money-card:hover {
            transform: scale(1.05);
        }
        .money-card h4 {
            margin: 0;
            color: #333;
        }
        .money-card h2 {
            color: #28a745;
            font-weight: bold;
            font-size: 2rem;
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="ai-header">
    <h2 class="subject-title"><i class="fas fa-book"></i> School Fees Management</h2>
    <p class="mb-0"><i class="fas fa-pencil-alt"></i> Manage student payments efficiently!</p>
</div>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 sidebar">
            <h4>Navigation</h4>
            <a href="print_page.php">Print Page</a>
            <a href="students.php">Students Records</a>
            <a href="../admin/index.php?dashboard">Dashboard</a>
            <a href="contact.php">Contact</a>
        </div>

        <!-- Main Content -->
        <div class="col-md-10 main-content">
            <h2 class="mb-4">Manage Student Payments</h2>

            <!-- Display Total Amounts in a Horizontal Layout -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="money-card" data-bs-toggle="modal" data-bs-target="#totalAmountModal">
                        <h4>Total Amount Paid:</h4>
                        <h2>Ghc <?= number_format($total_amount, 2) ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="money-card" data-bs-toggle="modal" data-bs-target="#totalBalanceModal">
                        <h4>Total Balance:</h4>
                        <h2>Ghc <?= number_format($total_balance, 2) ?></h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="money-card" data-bs-toggle="modal" data-bs-target="#totalArrearsModal">
                        <h4>Total Last Term Arrears:</h4>
                        <h2>Ghc <?= number_format($total_arrears, 2) ?></h2>
                    </div>
                </div>
            </div>

            <!-- Display Success/Error Message -->
            <div id="message" class="alert" style="display: none;"></div>

            <!-- Filter by Class -->
            <form method="GET" class="mb-3">
                <label for="class">Filter by Class:</label>
                <select name="class" class="form-control" onchange="this.form.submit()">
                    <option value="">All Classes</option>
                    <?php while ($row = $classes->fetchArray(SQLITE3_ASSOC)) { ?>
                        <option value="<?= $row['class'] ?>" <?= ($selected_class == $row['class']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['class']) ?>
                        </option>
                    <?php } ?>
                </select>
            </form>

            <!-- Add Payment Button -->
            <button class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#addPaymentModal">Add Payment</button>

            <!-- Payments Table -->
            <table class="table table-striped">
                <thead class="bg-dark text-white">
                    <tr>
                        <th>Student Name</th>
                        <th>Class</th>
                        <th>Amount Paid</th>
                        <th>Date Paid</th>
                        <th>Balance</th>
                        <th>Last Term Arrears</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($row['student_name']) ?></td>
                            <td><?= htmlspecialchars($row['class']) ?></td>
                            <td><?= 'Ghc ' . htmlspecialchars($row['amount_paid']) ?></td>
                            <td><?= htmlspecialchars($row['date_paid']) ?></td>
                            <td><?= 'Ghc ' . htmlspecialchars($row['balance']) ?></td>
                            <td><?= 'Ghc ' . htmlspecialchars($row['last_term_arrears']) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-btn"
                                    data-id="<?= $row['id'] ?>"
                                    data-name="<?= htmlspecialchars($row['student_name']) ?>"
                                    data-class="<?= htmlspecialchars($row['class']) ?>"
                                    data-amount="<?= htmlspecialchars($row['amount_paid']) ?>"
                                    data-date="<?= htmlspecialchars($row['date_paid']) ?>"
                                    data-balance="<?= htmlspecialchars($row['balance']) ?>"
                                    data-arrears="<?= htmlspecialchars($row['last_term_arrears']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#editPaymentModal">Edit</button>
                                <a href="#" class="btn btn-danger btn-sm delete-btn" data-id="<?= $row['id']; ?>">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addPaymentForm">
                    <input type="text" name="name" class="form-control mb-2" placeholder="Student Name" required>
                    <input type="text" name="class" class="form-control mb-2" placeholder="Class" required>
                    <div class="input-group mb-2">
                        <input type="text" id="add-amount" name="amount_paid" class="form-control" placeholder="Amount Paid" required>
                        <span class="input-group-text">Ghc</span>
                    </div>
                    <input type="date" name="date_paid" class="form-control mb-2" required>
                    <div class="input-group mb-2">
                        <input type="text" id="add-balance" name="balance" class="form-control" placeholder="Balance" required>
                        <span class="input-group-text">Ghc</span>
                    </div>
                    <div class="input-group mb-2">
                        <input type="text" id="add-arrears" name="last_term_arrears" class="form-control" placeholder="Last Term Arrears" required>
                        <span class="input-group-text">Ghc</span>
                    </div>
                    <button type="submit" class="btn btn-success">Add Payment</button>
                </form>
            </div>
			            </div>
        </div>
    </div>
</div>

<!-- Total Balance Modal -->
<div class="modal fade" id="totalBalanceModal" tabindex="-1" aria-labelledby="totalBalanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Students with Balances</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $balances_result->fetchArray(SQLITE3_ASSOC)) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= 'Ghc ' . htmlspecialchars($row['balance']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="mb-3">
                    <button class="btn btn-primary" onclick="window.print()">Print</button>
                    <a href="export_to_csv.php" class="btn btn-secondary">Export to CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Total Last Term Arrears Modal -->
<div class="modal fade" id="totalArrearsModal" tabindex="-1" aria-labelledby="totalArrearsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Students with Last Term Arrears</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Last Term Arrears</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $arrears_result->fetchArray(SQLITE3_ASSOC)) { ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= 'Ghc ' . htmlspecialchars($row['last_term_arrears']) ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <div class="mb-3">
                    <button class="btn btn-primary" onclick="window.print()">Print</button>
                    <a href="export_to_csv.php" class="btn btn-secondary">Export to CSV</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Format amount input to prepend "Ghc"
    $('#add-amount, #add-balance, #add-arrears').on('input', function() {
        let value = $(this).val().replace(/Ghc\s*/, ''); // Remove existing "Ghc" if present
        if (!isNaN(value) && value !== '') {
            $(this).val('Ghc ' + value); // Prepend "Ghc"
        } else {
            $(this).val(''); // Clear if not a number
        }
    });

    // Handle form submission for adding payment
    $('#addPaymentForm').on('submit', function(e) {
        e.preventDefault(); // Prevent the default form submission

        // Extract the numeric values from the inputs
        let amount = $('#add-amount').val().replace(/Ghc\s*/, '').trim();
        let balance = $('#add-balance').val().replace(/Ghc\s*/, '').trim();
        let arrears = $('#add-arrears').val().replace(/Ghc\s*/, '').trim();

        $.ajax({
            type: 'POST',
            url: 'add_payment.php', // URL to your PHP script that handles the form submission
            data: $(this).serialize() + '&amount_paid=' + amount + '&balance=' + balance + '&last_term_arrears=' + arrears, // Serialize form data and add the numeric amounts
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Payment added successfully!',
                    showCloseButton: true, // Show close button
                    confirmButtonText: 'OK', // Custom confirm button text
                    timer: 3000, // Show for 3 seconds
                    timerProgressBar: true // Show progress bar
                });
                $('#addPaymentModal').modal('hide'); // Hide the modal
                $('#addPaymentForm')[0].reset(); // Reset the form

                // Refresh the payments table or the entire page
                setTimeout(function() {
                    window.location.reload(); // Refresh the page after 3 seconds
                }, 3000);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: 'Failed to add payment.',
                });
            }
        });
    });

    // Handle delete button click
    $(document).on('click', '.delete-btn', function(e) {
        e.preventDefault(); // Prevent default anchor behavior
        let id = $(this).data('id'); // Get the ID from data attribute

        // Show SweetAlert2 confirmation dialog
        Swal.fire({
            title: 'Are you sure?',
            text: "This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `delete_payment.php?id=${id}`,
                    type: 'GET',
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: 'Record deleted successfully!',
                            icon: 'success',
                            showCloseButton: true, // Show close button
                            confirmButtonText: 'OK', // Custom confirm button text
                            timer: 3000, // Show for 3 seconds
                            timerProgressBar: true // Show progress bar
                        });
                        setTimeout(function() {
                            location.reload(); // Refresh the page after 3 seconds
                        }, 3000);
                    },
                    error: function() {
                        Swal.fire(
                            'Error!',
                            'Error deleting record. Please try again.',
                            'error'
                        );
                    }
                });
            }
        });
    });

    // Handle form submission for editing payment
    $('#editPaymentForm').on('submit', function(e) {
        e.preventDefault(); // Prevent default form submission

        let formData = $(this).serialize(); // Get form data

        $.ajax({
            type: 'POST',
            url: 'edit_payment.php', // Ensure this file exists
            data: formData,
            dataType: 'json', // Expect JSON response
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Payment updated successfully!',
                        showCloseButton: true, // Show close button
                        confirmButtonText: 'OK', // Custom confirm button text
                        timer: 3000, // Show for 3 seconds
                        timerProgressBar: true // Show progress bar
                    });
                    $('#editPaymentModal').modal('hide'); // Hide modal
                    setTimeout(function() {
                        location.reload(); // Refresh page after 3 seconds
                    }, 3000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: response.message,
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error updating payment. Check console for details.',
                });
                console.log("AJAX Error:", xhr.responseText); // Debugging
            }
        });
    });

    // Handle edit button click (populate modal fields)
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('edit-id').value = this.getAttribute('data-id') || '';
            document.getElementById('edit-name').value = this.getAttribute('data-name') || '';
            document.getElementById('edit-class').value = this.getAttribute('data-class') || '';
            document.getElementById('edit-amount').value = this.getAttribute('data-amount') || '';
            document.getElementById('edit-date').value = this.getAttribute('data-date') || '';
            document.getElementById('edit-balance').value = this.getAttribute('data-balance') || '';
            document.getElementById('edit-arrears').value = this.getAttribute('data-arrears') || '';
        });
    });
});
</script>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="edit_payment.php" id="editPaymentForm">
                    <input type="hidden" name="id" id="edit-id" autocomplete="off">
                    <label for="edit-name">Student Name</label>
                    <input type="text" name="student_name" id="edit-name" class="form-control mb-2" required autocomplete="name">
                    <label for="edit-class">Class</label>
                    <input type="text" name="class" id="edit-class" class="form-control mb-2" required autocomplete="off">
                    <label for="edit-amount">Amount Paid</label>
                    <div class="input-group mb-2">
                        <input type="text" id="edit-amount" name="amount_paid" class="form-control" required autocomplete="off">
                        <span class="input-group-text">Ghc</span>
                    </div>
                    <label for="edit-date">Date Paid</label>
                                       <input type="date" name="date_paid" id="edit-date" class="form-control mb-2" required autocomplete="bday">
                    <label for="edit-balance">Balance</label>
                    <div class="input-group mb-2">
                        <input type="text" id="edit-balance" name="balance" class="form-control" required autocomplete="off">
                        <span class="input-group-text">Ghc</span>
                    </div>
                    <label for="edit-arrears">Last Term Arrears</label>
                    <div class="input-group mb-2">
                        <input type="text" id="edit-arrears" name="last_term_arrears" class="form-control" required autocomplete="off">
                        <span class="input-group-text">Ghc</span>
                    </div>
                    <button type="submit" class="btn btn-success">Update Payment</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Footer -->
<footer class="bg-light text-center text-lg-start mt-5">
    <div class="text-center p-3">
        &copy; 2025 School Fees Management
    </div>
</footer>

</body>
</html>