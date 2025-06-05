<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$receiver_id = isset($_GET["receiver_id"]) ? intval($_GET["receiver_id"]) : null;

if (!$receiver_id) {
    die("Invalid request.");
}

// Check if the user has already sent a "Pup"
$stmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ? AND receiver_id = ?");
$stmt->bind_param("ii", $user_id, $receiver_id);
$stmt->execute();
$stmt->bind_result($message_count);
$stmt->fetch();
$stmt->close();

$already_pup = ($message_count > 0);

// Handle sending a Pup
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$already_pup) {
    $message = "Pup!"; // Default Pup message

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();
    $stmt->close();

    header("Location: pup_box.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Pup Box</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: #f4f7fa;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .pup-box {
            width: 100%;
            max-width: 400px;
            margin: 100px auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }
        .pup-box img {
            width: 80px;
            margin-bottom: 10px;
        }
        .pup-message {
            font-size: 18px;
            color: #333;
            margin-bottom: 20px;
        }
        .pup-button {
            width: 100%;
            padding: 12px;
            background: #ff9800;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .pup-button:hover {
            background: #e68900;
        }
        .denied-message {
            font-size: 18px;
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="pup-box">
    <img src="media/logo.png" alt="PupChat Logo">
    <?php if ($already_pup): ?>
        <p class="denied-message">Denied: You already Pup'd this user.</p>
    <?php else: ?>
        <p class="pup-message">Send your first Pup to this user!</p>
        <form method="POST">
            <button type="submit" class="pup-button">Send Pup</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>