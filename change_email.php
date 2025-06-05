<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";  
require 'send_email.php'; // Include the email sending script  

if (!isset($_SESSION["user_id"])) {  
    header("Location: login.php");  
    exit();  
}  

$user_id = $_SESSION["user_id"];  
$message = "";  

// Fetch user details  
$stmt = $conn->prepare("SELECT username, email, last_username_change FROM users WHERE id = ?");  
$stmt->bind_param("i", $user_id);  
$stmt->execute();  
$stmt->bind_result($username, $email, $last_username_change);  
$stmt->fetch();  
$stmt->close();  

// Handle OTP verification  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["verify_otp"])) {  
    $otp = trim($_POST["otp"]);  
    if ($otp === $_SESSION["otp"]) {  
        // OTP is correct, update email  
        $new_email = $_SESSION["new_email"];  
        $update_stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");  
        $update_stmt->bind_param("si", $new_email, $user_id);  
        if ($update_stmt->execute()) {  
            $message = "<div class='alert alert-success'>Email updated successfully.</div>";  
            unset($_SESSION["otp"]); // Clear OTP from session  
            unset($_SESSION["new_email"]); // Clear new email from session  
        } else {  
            $message = "<div class='alert alert-danger'>Error updating email.</div>";  
        }  
        $update_stmt->close();  
    } else {  
        $message = "<div class='alert alert-danger'>Invalid OTP.</div>";  
    }  
}  

// Handle email change request  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_email"])) {  
    $new_email = trim($_POST["new_email"]);  
    $confirm_email = trim($_POST["confirm_email"]);  

    if (!empty($new_email) && !empty($confirm_email)) {  
        if ($new_email === $confirm_email) {  
            // Validate email format  
            if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {  
                // Check if email already exists  
                $check_email_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");  
                $check_email_stmt->bind_param("s", $new_email);  
                $check_email_stmt->execute();  
                $check_email_stmt->store_result();  

                if ($check_email_stmt->num_rows > 0) {  
                    $message = "<div class='alert alert-danger'>Email already exists.</div>";  
                } else {  
                    // Generate OTP  
                    $otp = rand(100000, 999999);  
                    $_SESSION["otp"] = $otp;  
                    $_SESSION["new_email"] = $new_email;  

                    // Send OTP to the new email  
                    sendVerificationEmail($new_email, $otp);  

                    $message = "<div class='alert alert-info'>An OTP has been sent to your new email. Please verify.</div>";  
                }  
                $check_email_stmt->close();  
            } else {  
                $message = "<div class='alert alert-danger'>Invalid email format.</div>";  
            }  
        } else {  
            $message = "<div class='alert alert-danger'>Emails do not match.</div>";  
        }  
    } else {  
        $message = "<div class='alert alert-danger'>All fields are required.</div>";  
    }  
}  

// Handle username change  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_username"])) {  
    $new_username = trim($_POST["new_username"]);  

    if (!empty($new_username)) {  
        // Check if 60 days have passed since the last username change  
        $last_change = strtotime($last_username_change);  
        $current_time = time();  
        $days_since_last_change = floor(($current_time - $last_change) / (60 * 60 * 24));  

        if ($days_since_last_change >= 60) {  
            // Check if username already exists  
            $check_username_stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");  
            $check_username_stmt->bind_param("s", $new_username);  
            $check_username_stmt->execute();  
            $check_username_stmt->store_result();  

            if ($check_username_stmt->num_rows > 0) {  
                $message = "<div class='alert alert-danger'>Username already exists.</div>";  
            } else {  
                // Update username and last_username_change  
                $update_stmt = $conn->prepare("UPDATE users SET username = ?, last_username_change = NOW() WHERE id = ?");  
                $update_stmt->bind_param("si", $new_username, $user_id);  
                if ($update_stmt->execute()) {  
                    $message = "<div class='alert alert-success'>Username updated successfully.</div>";  
                } else {  
                    $message = "<div class='alert alert-danger'>Error updating username.</div>";  
                }  
                $update_stmt->close();  
            }  
            $check_username_stmt->close();  
        } else {  
            $days_remaining = 60 - $days_since_last_change;  
            $message = "<div class='alert alert-danger'>You can change your username again in $days_remaining days.</div>";  
        }  
    } else {  
        $message = "<div class='alert alert-danger'>Username cannot be empty.</div>";  
    }  
}  
?>  

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Change Email/Username - PupChat</title>  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">  
   <style>
    body {
        background: linear-gradient(135deg, #1e1e2f, #2a2a40);
        color: #fff; /* Default text color */
        font-family: 'Courier New', monospace;
    }
    .card {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        backdrop-filter: blur(10px);
        color: #fff; /* Ensure text inside cards is white */
    }
    .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        color: #fff; /* Text color for form inputs */
    }
    .form-control:focus {
        background: rgba(255, 255, 255, 0.2);
        border-color: #007bff;
        color: #fff; /* Text color for focused inputs */
    }
    .btn-primary {
        background: #007bff;
        border: none;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: #0056b3;
        transform: translateY(-2px);
    }
    .alert {
        border-radius: 10px;
        color: #000; /* Alert text color (black for better contrast) */
    }
    .form-label {
        color: #fff; /* Ensure labels are visible */
    }
    h3, h4, h5, h6 {
        color: #fff; /* Ensure headings are visible */
    }
    .text-center {
        color: #fff; /* Ensure centered text is visible */
    }
    .text-muted {
        color: #ccc !important; /* Ensure muted text is visible */
    }
</style>
</head>  
<body>  

<div class="container mt-5">  
    <h3 class="mb-4 text-center">Change Email/Username</h3>  
    <?php echo $message; ?>  

    <!-- User Info -->  
    <div class="card p-3 mb-3">  
        <h5>Username: <?php echo htmlspecialchars($username); ?></h5>  
        <h6>Email: <?php echo htmlspecialchars($email); ?></h6>  
    </div>  

    <!-- Change Email Form -->  
    <div class="card p-4 mb-4">  
        <h4 class="mb-3">Change Email</h4>  
        <form action="change_email.php" method="POST">  
            <div class="mb-3">  
                <label class="form-label">New Email</label>  
                <input type="email" class="form-control" name="new_email" required>  
            </div>  
            <div class="mb-3">  
                <label class="form-label">Confirm New Email</label>  
                <input type="email" class="form-control" name="confirm_email" required>  
            </div>  
            <button type="submit" name="change_email" class="btn btn-primary">Send OTP</button>  
        </form>  
    </div>  

    <!-- OTP Verification Form -->  
    <?php if (isset($_SESSION["otp"])): ?>  
        <div class="card p-4 mb-4">  
            <h4 class="mb-3">Verify OTP</h4>  
            <form action="change_email.php" method="POST">  
                <div class="mb-3">  
                    <label class="form-label">Enter OTP</label>  
                    <input type="text" class="form-control" name="otp" required>  
                </div>  
                <button type="submit" name="verify_otp" class="btn btn-primary">Verify</button>  
            </form>  
        </div>  
    <?php endif; ?>  

    <!-- Change Username Form -->  
    <div class="card p-4">  
        <h4 class="mb-3">Change Username</h4>  
        <form action="change_email.php" method="POST">  
            <div class="mb-3">  
                <label class="form-label">New Username</label>  
                <input type="text" class="form-control" name="new_username" required>  
            </div>  
            <button type="submit" name="change_username" class="btn btn-primary">Update Username</button>  
        </form>  
    </div>  

    <div class="text-center mt-3">  
        <a href="settings.php" class="btn btn-secondary">Back to Settings</a>  
    </div>  
</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>  
</body>  
</html>