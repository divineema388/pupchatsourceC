<?php
session_start();
include "db_connect.php";

$error = "";

if (!isset($_SESSION["pending_verification"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["pending_verification"];

// Fetch user email for display
$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["otp"])) {
    $otp = trim($_POST["otp"]);

    $stmt = $conn->prepare("SELECT expires FROM otp_verification WHERE user_id = ? AND otp = ?");
    $stmt->bind_param("is", $user_id, $otp);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($expires);
        $stmt->fetch();
        $stmt->close();

        if (time() < $expires) {
            // Delete OTP after verification
            $stmt = $conn->prepare("DELETE FROM otp_verification WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            // Mark login as verified
            $stmt = $conn->prepare("UPDATE logins SET verified = 1 WHERE user_id = ? ORDER BY timestamp DESC LIMIT 1");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            $_SESSION["user_id"] = $user_id;
            unset($_SESSION["pending_verification"]);
            header("Location: home.php");
            exit();
        } else {
            $error = "OTP expired.";
        }
    } else {
        $error = "Invalid OTP.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Identity</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .verify-container {
            width: 100%;
            max-width: 400px;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .info-text {
            font-size: 14px;
            color: #555;
            margin-bottom: 20px;
        }

        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 14px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .error {
            color: red;
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }

        @media (max-width: 600px) {
            .verify-container {
                margin: 20px;
                padding: 20px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <h2>Verify Your Identity</h2>
        <p class="info-text">An OTP has been sent to your email <strong><?php echo htmlspecialchars($email); ?></strong>. Please enter it below.</p>
        <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="verify.php" method="POST">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>