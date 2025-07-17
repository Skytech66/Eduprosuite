<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses Management</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"> <!-- Google Fonts -->
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #EAEDED; /* Light Gray Background */
            color: #2C3E50; /* Darker Gray Text */
        }

        header {
            background-color: #2980B9; /* Professional Blue */
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .filter-section {
            margin: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .filter-section input,
        .filter-section select,
        .filter-section button {
            padding: 10px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            transition: border-color 0.3s;
        }

        .filter-section input:focus,
        .filter-section select:focus,
        .filter-section button:focus {
            border-color: #2980B9; /* Professional Blue */
            outline: none;
        }

        .filter-section button {
            background-color: #2980B9; /* Professional Blue */
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .filter-section button:hover {
            background-color: #1A5276; /* Darker Blue on hover */
        }

        .overview {
            margin: 20px;
            text-align: center;
        }

        .cards-section {
            display: flex;
            justify-content: space-around;
            margin: 20px;
            flex-wrap: wrap;
        }

        .card {
            background-color: #FFFFFF; /* White for cards */
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            width: 18%; /* Reduced width for smaller cards */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            margin: 10px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .card i {
            font-size: 2em; /* Slightly smaller icon size */
            color: #2980B9; /* Professional Blue for icons */
        }

        .card h3 {
            margin: 10px 0;
            font-weight: 500;
        }

        .expenses-table {
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden; /* For rounded corners */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #2980B9; /* Professional Blue for table header */
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9; /* Light Gray for even rows */
        }

        tr:hover {
            background-color: #e9ecef; /* Slightly darker gray on hover */
        }

        @media (max-width: 768px) {
            .cards-section {
				                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 90%; /* Full width on smaller screens */
                margin-bottom: 20px;
            }

            .filter-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-section input,
            .filter-section select,
            .filter-section button {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <main>
        <section class="filter-section">
            <input type="text" placeholder="Search Expenses..." id="search-bar" aria-label="Search Expenses">
            <label for="date-range">Date Range:</label>
            <input type="date" id="start-date" aria-label="Start Date">
            <input type="date" id="end-date" aria-label="End Date">
            <select id="category-filter" aria-label="Category Filter">
                <option value="">All Categories</option>
                <option value="supplies">Supplies</option>
                <option value="maintenance">Maintenance</option>
                <option value="salaries">Salaries</option>
                <option value="events">Events</option>
            </select>
            <button id="filter-button" aria-label="Filter Expenses">Filter</button>
        </section>

        <section class="overview">
            <h2>Total Expenses: ₵<span id="total-expenses">0.00</span></h2>
            <div id="monthly-breakdown-chart"></div>
        </section>

        <section class="cards-section">
            <div class="card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Total Expenses</h3>
                <p>₵<span id="total-expenses-card">0.00</span></p>
            </div>
            <div class="card">
                <i class="fas fa-calendar-alt"></i>
                <h3>Expenses This Month</h3>
                <p>₵<span id="monthly-expenses">0.00</span></p>
            </div>
            <div class="card">
                <i class="fas fa-tags"></i>
                <h3>Categories</h3>
                <p><span id="category-count">0</span> Categories</p>
            </div>
            <div class="card">
                <i class="fas fa-check-circle"></i>
                <h3>Paid Expenses</h3>
                <p>₵<span id="paid-expenses">0.00</span></p>
            </div>
        </section>

        <section class="expenses-table">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Amount (₵)</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="expenses-list">
                    <!-- Dynamic rows will be populated here -->
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>