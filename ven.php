<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $otp = trim($_POST["otp"]);

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND otp = ?");
    $stmt->bind_param("si", $email, $otp);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update user status to verified
        $stmt = $conn->prepare("UPDATE users SET status = 1 WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        $_SESSION["user_id"] = $stmt->insert_id;
        header("Location: home.php");
        exit();
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
    <title>PupChat - Verify Email</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; background: #f4f4f4; padding: 20px; }
        .form-container { width: 300px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0px 0px 10px #ccc; }
        input { width: 100%; padding: 10px; margin: 5px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px; background: #28a745; color: white; border: none; width: 100%; cursor: pointer; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Email Verification</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="POST">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>">
            <input type="text" name="otp" placeholder="Enter OTP" required>
            <button type="submit">Verify</button>
        </form>
    </div>
</body>
</html>