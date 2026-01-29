<?php
require "db.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $subject = htmlspecialchars($_POST['subject']);
    $message = htmlspecialchars($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'ochiengfidel147@gmail.com';
            $mail->Password = 'tngqhomkvmyeecwg ';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('ochiengfidel147@gmail.com', 'Africa Balloons Fiesta Website');
            $mail->addReplyTo($email, $name);
            $mail->addAddress('ochiengfidel147@gmail.com');

            $mail->isHTML(true);
            $mail->Subject = "New Contact Form: $subject";

            $mail->Body = "
                <h2>New Website Inquiry</h2>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $email</p>
                <p><strong>Subject:</strong> $subject</p>
                <p><strong>Message:</strong><br>$message</p>
            ";

            $mail->AltBody = "Name: $name\nEmail: $email\nSubject: $subject\nMessage:\n$message";

            $mail->send();

            echo "Message saved and email sent successfully.";

        } catch (Exception $e) {
            echo "Saved in DB but email failed: {$mail->ErrorInfo}";
        }

    } else {
        echo "Database error.";
    }

    $stmt->close();
    $conn->close();
}
?>
