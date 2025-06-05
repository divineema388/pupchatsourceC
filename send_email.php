<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendVerificationEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'divineema388@gmail.com'; // Replace with your email
        $mail->Password = 'jcft yqps nmsn hcmh'; // Replace with your password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@example.com', 'PupChat');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'PupChat Email Verification';
        $mail->Body = "Your OTP for verification is: <b>$otp</b>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent: {$mail->ErrorInfo}");
    }
}
?>