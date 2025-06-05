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
$username = "Unknown User"; // Default value in case fetching fails

// Fetch the username from the users table
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Ensure uploads folder exists
$upload_dir = "uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Ensure 'created_at' column exists in support_messages
$check_column = $conn->query("SHOW COLUMNS FROM support_messages LIKE 'created_at'");
if ($check_column->num_rows == 0) {
    $conn->query("ALTER TABLE support_messages ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
}

// Handle message submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["message"])) {
    $message = trim($_POST["message"]);
    $image = "";

    if (!empty($_FILES["image"]["name"])) {
        $target_file = $upload_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        } else {
            error_log("Image upload failed.");
        }
    }

    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO support_messages (user_id, username, message, image) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $username, $message, $image);
    $stmt->execute();
    $stmt->close();

    // Prevent duplicate message submissions on refresh
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit();
}

// Fetch messages
$messages = $conn->query("SELECT * FROM support_messages ORDER BY created_at DESC");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat Support Chat</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: #f0f0f0;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 600px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            padding-bottom: 10px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 10px;
        }
        .user-message {
            background: #ff6ec4;
            color: white;
            text-align: right;
        }
        .admin-message {
            background: #7873f5;
            color: white;
        }
        .image-preview {
            max-width: 100%;
            border-radius: 8px;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Support Chat</h2>
    
    <div class="chat-box">
        <?php while ($msg = $messages->fetch_assoc()) { ?>
            <div class="message <?php echo $msg['user_id'] == $user_id ? 'user-message' : 'admin-message'; ?>">
                <p>
                    <strong>
                        <?php if ($msg["username"] == "ADMIN") { ?>
                            ADMIN <img src="media/veri.png" width="20px" alt="Verified">
                        <?php } else { ?>
                            <?php echo htmlspecialchars(!empty($msg["username"]) ? $msg["username"] : "Unknown User"); ?>
                        <?php } ?>
                    </strong>
                    <?php 
                    $safe_message = !empty($msg["message"]) ? htmlspecialchars($msg["message"]) : "[No message]";
                    echo nl2br($safe_message);
                    ?>
                </p>

                <?php if (!empty($msg["image"])) { ?>
                    <img src="<?php echo htmlspecialchars($msg["image"]); ?>" class="image-preview">
                <?php } ?>

                <small><?php echo date("F j, Y, g:i a", strtotime($msg["created_at"])); ?></small>
            </div>
        <?php } ?>
    </div>

    <form method="POST" enctype="multipart/form-data">
        <textarea name="message" class="form-control mb-2" placeholder="Type your message..." required></textarea>
        <input type="file" name="image" class="form-control mb-2">
        <button type="submit" class="btn btn-primary btn-block">Send</button>
    </form>
</div>

</body>
</html>