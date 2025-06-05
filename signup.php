<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $otp = rand(100000, 999999); // Generate OTP

    // Check if username or email already exists
    $check_user = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check_user->bind_param("ss", $username, $email);
    $check_user->execute();
    $check_user->store_result();

    if ($check_user->num_rows > 0) {
        $error = "Username or Email already exists.";
    } else {
        $check_user->close();

        // Insert new user (status = 0 means not verified)
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, otp, status) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("sssi", $username, $email, $password, $otp);

        if ($stmt->execute()) {
            $_SESSION["user_id"] = $stmt->insert_id;
            
            // Send verification email
            include "send_email.php";
            sendVerificationEmail($email, $otp);

            header("Location: ven.php?email=" . urlencode($email));
            exit();
        } else {
            $error = "Registration failed. Please try again.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Sign Up</title>
    
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

        .register-card {
            width: 100%;
            max-width: 420px;
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
            color: white;
            padding: 25px;
            text-align: center;
        }

        .register-header img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 15px;
        }

        .register-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 1.8rem;
        }

        .register-body {
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

        .btn-register {
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

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 99, 255, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .register-footer {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .register-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .register-footer a:hover {
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

        .password-strength {
            margin-top: 5px;
            font-size: 12px;
            color: var(--medium-gray);
        }

        .strength-weak { color: #e74c3c; }
        .strength-medium { color: #f39c12; }
        .strength-strong { color: #2ecc71; }

        @media (max-width: 480px) {
            .register-card {
                max-width: 100%;
            }
            
            .register-header {
                padding: 20px;
            }
            
            .register-body {
                padding: 25px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .register-card {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <img src="media/logo-white.png" alt="PupChat Logo">
            <h2>Create Account</h2>
        </div>
        
        <div class="register-body">
            <?php if (isset($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo $error; ?></span>
                </div>
            <?php endif; ?>
            
            <form action="" method="POST">
                <div class="form-group">
                    <input type="text" name="username" class="form-control" placeholder="Username" required>
                    <i class="fas fa-user input-icon"></i>
                </div>
                
                <div class="form-group">
                    <input type="email" name="email" class="form-control" placeholder="Email" required>
                    <i class="fas fa-envelope input-icon"></i>
                </div>
                
                <div class="form-group">
                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>
                    <i class="fas fa-lock input-icon"></i>
                    <div id="password-strength" class="password-strength"></div>
                </div>
                
                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="register-footer">
                Already have an account? <a href="login.php">Sign in</a>
            </div>
        </div>
    </div>

    <!-- External JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password strength indicator
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthText = document.getElementById('password-strength');
            
            if (password.length === 0) {
                strengthText.textContent = '';
                return;
            }
            
            let strength = 0;
            
            // Check length
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            
            // Check for uppercase, lowercase, numbers, special chars
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Update display
            if (strength <= 2) {
                strengthText.textContent = 'Weak password';
                strengthText.className = 'password-strength strength-weak';
            } else if (strength <= 4) {
                strengthText.textContent = 'Medium password';
                strengthText.className = 'password-strength strength-medium';
            } else {
                strengthText.textContent = 'Strong password';
                strengthText.className = 'password-strength strength-strong';
            }
        });
    </script>
</body>
</html>