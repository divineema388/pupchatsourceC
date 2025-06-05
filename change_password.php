<?php  
session_start();  
include "db_connect.php";  

if (!isset($_SESSION["user_id"])) {  
    header("Location: login.php");  
    exit();  
}  

$user_id = $_SESSION["user_id"];  
$message = "";  

// Fetch user details  
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");  
$stmt->bind_param("i", $user_id);  
$stmt->execute();  
$stmt->bind_result($username, $email);  
$stmt->fetch();  
$stmt->close();  

// Handle password change  
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {  
    $current_password = trim($_POST["current_password"]);  
    $new_password = trim($_POST["new_password"]);  
    $confirm_password = trim($_POST["confirm_password"]);  

    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {  
        if ($new_password === $confirm_password) {  
            // Fetch current password hash  
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");  
            $stmt->bind_param("i", $user_id);  
            $stmt->execute();  
            $stmt->bind_result($db_password);  
            $stmt->fetch();  
            $stmt->close();  

            if (password_verify($current_password, $db_password)) {  
                // Hash new password and update  
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);  
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");  
                $update_stmt->bind_param("si", $new_hashed_password, $user_id);  
                if ($update_stmt->execute()) {  
                    $message = "<div class='alert alert-success'>Password changed successfully.</div>";  
                } else {  
                    $message = "<div class='alert alert-danger'>Error updating password.</div>";  
                }  
                $update_stmt->close();  
            } else {  
                $message = "<div class='alert alert-danger'>Current password is incorrect.</div>";  
            }  
        } else {  
            $message = "<div class='alert alert-danger'>New passwords do not match.</div>";  
        }  
    } else {  
        $message = "<div class='alert alert-danger'>All fields are required.</div>";  
    }  
}  
?>  

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Change Password - PupChat</title>  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">  
</head>  
<body class="bg-light">  

<div class="container mt-5">  
    <h3 class="mb-4 text-center">Change Password</h3>  
    <?php echo $message; ?>  

    <!-- User Info -->
    <div class="card p-3 mb-3">
        <h5>Username: <?php echo htmlspecialchars($username); ?></h5>
        <h6>Email: <?php echo htmlspecialchars($email); ?></h6>
    </div>  

    <div class="card p-4">  
        <form action="change_password.php" method="POST">  
            <div class="mb-3">  
                <label class="form-label">Current Password</label>  
                <input type="password" class="form-control" name="current_password" required>  
            </div>  
            <div class="mb-3">  
                <label class="form-label">New Password</label>  
                <input type="password" class="form-control" name="new_password" required>  
            </div>  
            <div class="mb-3">  
                <label class="form-label">Confirm New Password</label>  
                <input type="password" class="form-control" name="confirm_password" required>  
            </div>  
            <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>  
        </form>  
    </div>  

    <div class="text-center mt-3">
        <a href="settings.php" class="btn btn-secondary">Back to Settings</a>
    </div>
</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>  
</body>  
</html>