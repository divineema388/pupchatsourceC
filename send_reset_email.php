<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (isset($_GET['email']) && isset($_GET['temp_password'])) {
    $email = $_GET['email'];
    $temp_password = $_GET['temp_password'];

    $mail = new PHPMailer(true);
    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Change for other providers
        $mail->SMTPAuth = true;
        $mail->Username = 'divineema388@gmail.com'; // Replace with your email
        $mail->Password = 'jcft yqps nmsn hcmh'; // Replace with your app password (not your real password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Content
        $mail->setFrom('your-email@gmail.com', 'PupChat Support'); // Sender
        $mail->addAddress($email); // Recipient
        $mail->Subject = "Your PupChat Temporary Password";
        $mail->Body = "Hello,\n\nYour temporary password is: $temp_password\n\nPlease log in and change it immediately.";

        // Send Email
        if ($mail->send()) {
            echo "<script>alert('A temporary password has been sent to your email.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Failed to send email.'); window.location='recover.php';</script>";
        }
    } catch (Exception $e) {
        echo "<script>alert('Mailer Error: " . $mail->ErrorInfo . "'); window.location='recover.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location='recover.php';</script>";
}
?>