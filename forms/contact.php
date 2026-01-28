<?php
// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// SQLite database path
$dbPath = __DIR__ . "/../database/Africa.db";

try {
    // Connect to SQLite database
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}

// Sanitize input
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate required fields
if (empty($name) || empty($email) || empty($message)) {
    http_response_code(400);
    echo "Please fill in all required fields.";
    exit;
}

// Validate email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo "Invalid email address.";
    exit;
}

// Insert data into SQLite
$sql = "INSERT INTO contacts (name, email, subject, message)
        VALUES (:name, :email, :subject, :message)";

$stmt = $pdo->prepare($sql);

try {
    $stmt->execute([
        ':name'    => $name,
        ':email'   => $email,
        ':subject' => $subject,
        ':message' => $message
    ]);

    http_response_code(200);
    echo "Thank you! Your message has been received.";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Failed to save your message.";
}

// ======================
// SEND EMAIL
// ======================
$to = "rotichbravin13@gmail.com"; // receiving email

$emailSubject = "New Contact Message – Africa Balloon Fiesta";

$emailBody = "
You have received a new contact message from the website.

Name: $name
Email: $email
Subject: $subject

Message:
$message
";

// Use a valid sender address (important for delivery)
$headers = [
    "From: Africa Balloon Fiesta <no-reply@localhost>",
    "Reply-To: $email",
    "Content-Type: text/plain; charset=UTF-8"
];

// Send email
$mailSent = mail($to, $emailSubject, $emailBody, implode("\r\n", $headers));

// ======================
// RESPONSE
// ======================
if ($mailSent) {
    http_response_code(200);
    echo "Thank you! Your message has been sent successfully.";
} else {
    http_response_code(500);
    echo "Message saved, but email could not be sent.";
}

