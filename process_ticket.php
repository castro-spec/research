<?php
header('Content-Type: application/json');

// Include database connection
require 'db.php';

// Helper function to send JSON responses
function send_response($success, $message) {
    echo json_encode([
        'success' => $success,
        'message' => $message
    ]);
    exit;
}

// Check if POST data exists
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_response(false, 'Invalid request method.');
}

// Retrieve and sanitize input
$fullName   = trim($_POST['fullName'] ?? '');
$email      = trim($_POST['email'] ?? '');
$phone      = trim($_POST['phone'] ?? '');
$ticketType = trim($_POST['ticketType'] ?? '');
$amount     = floatval($_POST['amount'] ?? 0);

// === Validation ===

// Full name required
if (empty($fullName)) {
    send_response(false, 'Full Name is required.');
}

// Email required and valid
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    send_response(false, 'Valid Email is required.');
}

// Phone required, numeric, 10–15 digits
if (empty($phone) || !preg_match('/^\d{10,15}$/', $phone)) {
    send_response(false, 'Phone must be numeric and 10–15 digits.');
}

// Ticket type required
if (empty($ticketType)) {
    send_response(false, 'Ticket Type is required.');
}

// Amount must be positive
if ($amount <= 0) {
    send_response(false, 'Ticket amount must be greater than zero.');
}

// Generate account number (for M-Pesa) → using phone number
$accountNumber = $phone;

// === Insert into database ===
$stmt = $conn->prepare("
    INSERT INTO tickets
    (full_name, email, phone, ticket_type, amount, payment_status, account_number)
    VALUES (?, ?, ?, ?, ?, 'pending', ?)
");

if (!$stmt) {
    send_response(false, 'Database prepare failed: ' . $conn->error);
}

$stmt->bind_param('ssssds', $fullName, $email, $phone, $ticketType, $amount, $accountNumber);

if ($stmt->execute()) {
    send_response(true, [
        'message' => 'Ticket booked successfully.',
        'accountNumber' => $accountNumber,
        'amount' => number_format($amount, 2)
    ]);
} else {
    send_response(false, 'Database execute failed: ' . $stmt->error);
}
