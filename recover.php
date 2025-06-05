<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Generate a temporary password
        $temp_password = substr(md5(time()), 0, 8);
        $hashed_password = password_hash($temp_password, PASSWORD_BCRYPT);

        // Update the password in the database
        $update_sql = "UPDATE users SET password='$hashed_password' WHERE email='$email'";
        if ($conn->query($update_sql) === TRUE) {
            // Redirect to send email
            header("Location: send_reset_email.php?email=$email&temp_password=$temp_password");
            exit();
        } else {
            echo "<script>alert('Error updating password.');</script>";
        }
    } else {
        echo "<script>alert('Email not found.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Recover Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            text-align: center;
        }
        .recover-container {
            width: 300px;
            margin: 50px auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 5px 0;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="recover-container">
        <h2>Recover Password</h2>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit">Reset Password</button>
        </form>
        <p>Remembered? <a href="login.php">Login</a></p>
    </div>
</body>
</html>