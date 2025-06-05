<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";
include "send_otp.php"; // Include the external OTP mail function

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username_email"], $_POST["password"], $_POST["device_info"], $_POST["location"])) {
    $username_email = trim($_POST["username_email"]);
    $password = trim($_POST["password"]);
    $device_info = trim($_POST["device_info"]);
    $location = trim($_POST["location"]);

    // Query user details
    $stmt1 = $conn->prepare("SELECT id, password, email, status FROM users WHERE username = ? OR email = ?");
    $stmt1->bind_param("ss", $username_email, $username_email);
    $stmt1->execute();
    $stmt1->store_result();

    if ($stmt1->num_rows > 0) {
        $stmt1->bind_result($user_id, $hashed_password, $user_email, $status);
        $stmt1->fetch();
        $stmt1->close(); // Close after fetching results

        // Check if the user is deleted
        if ($status == 'deleted') {
            header("Location: delete.html");
            exit();
        }

        // Check if the user is suspended
        $stmtSuspended = $conn->prepare("SELECT COUNT(*) FROM suspended_users WHERE user_id = ?");
        $stmtSuspended->bind_param("i", $user_id);
        $stmtSuspended->execute();
        $stmtSuspended->bind_result($isSuspended);
        $stmtSuspended->fetch();
        $stmtSuspended->close();

        if ($isSuspended) {
            // User is suspended
            header("Location: sus.html");
            exit();
        }

        // Verify password
        if (password_verify($password, $hashed_password)) {
            // Check if login is already verified
            $stmt2 = $conn->prepare("SELECT verified FROM logins WHERE user_id = ? AND device_info = ?");
            $stmt2->bind_param("is", $user_id, $device_info);
            $stmt2->execute();
            $stmt2->bind_result($verified);
            $stmt2->fetch();
            $stmt2->close(); // Close second statement

            if ($verified == 1) {
                // Grant access
                $_SESSION["user_id"] = $user_id;
                header("Location: home.php");
                exit();
            } else {
                // Generate OTP for verification
                $otp = rand(100000, 999999);
                $expires = time() + 300; // 5 minutes expiration

                // Store OTP (prevent duplicate entries)
                $stmt3 = $conn->prepare("INSERT INTO otp_verification (user_id, otp, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE otp=?, expires=?");
                $stmt3->bind_param("isisi", $user_id, $otp, $expires, $otp, $expires);
                $stmt3->execute();
                $stmt3->close();

                // Store login attempt (unverified)
                $stmt4 = $conn->prepare("INSERT INTO logins (user_id, device_info, location, verified, timestamp) VALUES (?, ?, ?, 0, NOW())");
                $stmt4->bind_param("iss", $user_id, $device_info, $location);
                $stmt4->execute();
                $stmt4->close();

                // Send OTP via PHPMailer using external function
                if (sendOtp($user_email, $otp)) {
                    $_SESSION["pending_verification"] = $user_id;
                    header("Location: verify.php");
                    exit();
                } else {
                    $error = "Failed to send OTP.";
                }
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Login</title>
    
    <!-- Favicon -->
    <link rel="icon" href="media/favicon.ico" type="image/x-icon">
    
    <!-- External CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #6C63FF;
            --primary-dark: #5A52E0;
            --accent-color: #FF6584;
            --light-gray: #F5F7FA;
            --dark-gray: #333333;
            --medium-gray: #777777;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-gray);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
            color: var(--dark-gray);
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 25px;
            text-align: center;
        }

        .login-header img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .login-body {
            padding: 30px;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            font-size: 15px;
            border: 1px solid #e0e0e0;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            background-color: #f9f9f9;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.1);
            background-color: white;
        }

        .input-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--medium-gray);
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .login-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .login-footer a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            background-color: #fdecea;
            padding: 12px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .error-message i {
            font-size: 18px;
        }

        @media (max-width: 480px) {
            .login-card {
                max-width: 100%;
            }
            
            .login-header {
                padding: 20px;
            }
            
            .login-body {
                padding: 25px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <img src="media/logo-white.png" alt="PupChat Logo">
            <h2>Welcome Back</h2>
        </div>
        
        <div class="login-body">
            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="login.php" method="POST">
                <div class="form-group">
                    <input type="text" name="username_email" class="form-control" placeholder="Username or Email" required>
                    <i class="fas fa-user input-icon"></i>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" class="form-control" placeholder="Password" required>
                    <i class="fas fa-lock input-icon"></i>
                </div>
                
                <input type="hidden" name="device_info" id="device_info">
                <input type="hidden" name="location" id="location">
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i> Log In
                </button>
            </form>
            
            <div class="login-footer">
                <a href="recover.php">Forgot your password?</a>
                <span> · </span>
                <a href="signup.php">Create an account</a>
            </div>
        </div>
    </div>

    <!-- External JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function getDeviceAndLocation() {
            var deviceInfo = navigator.userAgent;
            document.getElementById("device_info").value = deviceInfo;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    document.getElementById("location").value = position.coords.latitude + "," + position.coords.longitude;
                }, function(error) {
                    console.log("Location access denied.");
                });
            }
        }
        window.onload = getDeviceAndLocation;
    </script>
</body>
</html>