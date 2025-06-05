<?php
include "../db_connect.php";

// Fetch all messages (both user & admin)
$messages = $conn->query("SELECT * FROM support_messages ORDER BY created_at DESC");

// Handle admin replies
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["reply"])) {
    $reply = $_POST["reply"];
    $user_id = $_POST["user_id"]; 

    $stmt = $conn->prepare("INSERT INTO support_messages (user_id, username, message, sender) VALUES (?, 'ADMIN', ?, 'admin')");
    $stmt->bind_param("is", $user_id, $reply);
    $stmt->execute();
    
    // Refresh to show new message
    header("Location: admin_support.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Support Panel</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff6ec4, #7873f5);
            font-family: Arial, sans-serif;
            color: white;
        }
        .container {
            max-width: 700px;
            margin: auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            color: black;
        }
        .message-box {
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        .user-message {
            background: #ff6ec4;
            color: white;
            padding: 10px;
            border-radius: 8px;
        }
        .admin-message {
            background: #7873f5;
            color: white;
            padding: 10px;
            border-radius: 8px;
        }
        .image-preview {
            max-width: 100%;
            border-radius: 8px;
            display: block;
            margin-top: 5px;
        }
        .admin-tag {
            display: flex;
            align-items: center;
        }
        .admin-tag img {
            width: 20px;
            margin-left: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Admin Support Panel</h2>

    <?php while ($msg = $messages->fetch_assoc()) { ?>
        <div class="message-box <?php echo ($msg['sender'] == 'admin') ? 'admin-message' : 'user-message'; ?>">
            <p>
                <strong>
                    <?php if ($msg['sender'] == 'admin') { ?>
                        <span class="admin-tag">ADMIN <img src="media/veri.png" alt="Verified"></span>
                    <?php } else { ?>
                        <?php echo htmlspecialchars($msg["username"]); ?>
                    <?php } ?>
                </strong> 
                <?php echo nl2br(htmlspecialchars($msg["message"])); ?>
            </p>

            <!-- Show Image if Available -->
            <?php if (!empty($msg["image"])) { ?>
                <img src="<?php echo $msg["image"]; ?>" class="image-preview">
            <?php } ?>

            <small><?php echo date("F j, Y, g:i a", strtotime($msg["created_at"])); ?></small>

            <!-- Admin Reply Form (Only show for user messages) -->
            <?php if ($msg["sender"] == "user") { ?>
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?php echo $msg['user_id']; ?>">
                    <textarea name="reply" class="form-control mt-2" placeholder="Reply to this user..." required></textarea>
                    <button type="submit" class="btn btn-primary btn-block mt-2">Send Reply</button>
                </form>
            <?php } ?>
        </div>
    <?php } ?>

</div>

</body>
</html>