<?php  
session_start();  
include "db_connect.php";  

if (!isset($_SESSION["user_id"])) {  
    header("Location: login.php");  
    exit();  
}  

$user_id = $_SESSION["user_id"];  

// Fetch user details  
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");  
$stmt->bind_param("i", $user_id);  
$stmt->execute();  
$stmt->bind_result($username, $email);  
$stmt->fetch();  
$stmt->close();  
?>  

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Settings - PupChat</title>  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">  
</head>  
<body class="bg-light">  

<div class="container mt-5">  
    <h3 class="mb-4 text-center">Account Settings</h3>  

    <!-- User Info -->
    <div class="card p-3 mb-3">
        <h5>Username: <?php echo htmlspecialchars($username); ?></h5>
        <h6>Email: <?php echo htmlspecialchars($email); ?></h6>
    </div>  

    <!-- Settings List -->
    <div class="list-group">
        <a href="change_password.php" class="list-group-item list-group-item-action">Change Password</a>
<a href="change_email.php" class="list-group-item list-group-item-action">Change Email</a>
        <a href="set/privacy.php" class="list-group-item list-group-item-action">Privacy Settings </a>
        <a href="ref/someone.php" class="list-group-item list-group-item-action">Invite Friends </a>
<a href="set/support.php" class="list-group-item list-group-item-action"> Support</a>
    </div>  
</div>  

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>  
</body>  
</html>