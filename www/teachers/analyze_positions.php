<?php
// analyze_positions.php

// Function to convert a number to its ordinal representation
function ordinal($number) {
    $suffix = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
    if ($number % 100 >= 11 && $number % 100 <= 13) {
        return $number . 'th';
    }
    return $number . $suffix[$number % 10];
}

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);

// Check if data is received
if (empty($data)) {
    echo json_encode(['error' => 'No data received']);
    exit;
}

// Initialize an array to hold student scores
$scores = [];

// Process the incoming data
foreach ($data as $entry) {
    $admno = $entry['admno'];
    $totalScore = $entry['totalScore'];

    // Store the scores in an associative array
    $scores[$admno] = $totalScore;
}

// Sort the scores in descending order
arsort($scores);
$positions = [];
$rank = 1;
$previousScore = null;
$previousRank = 0;

foreach ($scores as $admno => $totalScore) {
    // If the score is the same as the previous score, assign the same rank
    if ($totalScore === $previousScore) {
        // Use the previous rank for this student
        $positions[] = [
            'admno' => $admno,
            'position' => $previousRank, // Assign the same position
            'ordinal' => ordinal($previousRank) // Store ordinal representation
        ];
    } else {
        // Assign the current rank and update the previous score and rank
        $positions[] = [
            'admno' => $admno,
            'position' => $rank, // Assign the current position
            'ordinal' => ordinal($rank) // Store ordinal representation
        ];
        $previousScore = $totalScore;
        $previousRank = $rank;
    }
    $rank++; // Increment the rank for the next student
}

// Return the positions as a JSON response with a success message
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Data processed successfully',
    'positions' => $positions
]);
?>