<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

function sendOtp($email, $otp) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'divineema388@gmail.com'; // Your SMTP username
        $mail->Password = 'jcft yqps nmsn hcmh'; // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your-email@yourmail.com', 'PupChat');
        $mail->addAddress($email);

        $mail->Subject = 'PupChat Login Verification';
        $mail->Body    = "Your verification code is: $otp";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>