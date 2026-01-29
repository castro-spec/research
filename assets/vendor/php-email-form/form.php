<?php
// Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo "Method Not Allowed";
    exit;
}

// Sanitize input
$name    = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_SPECIAL_CHARS));
$email   = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
$subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_SPECIAL_CHARS));
$message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_SPECIAL_CHARS));

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

// Email configuration
$to = "rotichbravin13@gmail.com";  
$headers = [
    "From: $name <$email>",
    "Reply-To: $email",
    "Content-Type: text/plain; charset=UTF-8"
];

// Email content
$emailBody = "
New Contact Message – Africa Balloon Fiesta

Name: $name
Email: $email
Subject: $subject

Message:
$message
";

// Send email
if (mail($to, $subject ?: "New Contact Message", $emailBody, implode("\r\n", $headers))) {
    http_response_code(200);
    echo "Thank you! Your message has been sent successfully.";
} else {
    http_response_code(500);
    echo "Sorry, something went wrong. Please try again later.";
}
