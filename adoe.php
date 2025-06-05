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

if (!isset($_GET["user_id"])) {
    echo "No user selected.";
    exit();
}

$receiver_id = intval($_GET["user_id"]);

// Get receiver's name
$receiver_query = $conn->query("SELECT username FROM users WHERE id = '$receiver_id'");
if ($receiver_query->num_rows == 0) {
    echo "User not found.";
    exit();
}
$receiver = $receiver_query->fetch_assoc();
$receiver_name = htmlspecialchars($receiver["username"]);

// Fetch chat messages
$messages_query = $conn->query("
    SELECT * FROM messages 
    WHERE (sender_id = '$user_id' AND receiver_id = '$receiver_id') 
       OR (sender_id = '$receiver_id' AND receiver_id = '$user_id') 
    ORDER BY sent_at ASC
");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = trim($_POST["message"]);

    if (!empty($message)) {
        $safe_message = $conn->real_escape_string($message); // Escape message input
        $conn->query("INSERT INTO messages (sender_id, receiver_id, message, sent_at) 
                      VALUES ('$user_id', '$receiver_id', '$safe_message', NOW())");

        header("Location: adoe.php?user_id=$receiver_id");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Chat with <?php echo $receiver_name; ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f9ff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 90%;
            max-width: 500px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 100, 255, 0.1);
            border: 1px solid #e0e9ff;
        }
        .chat-box {
            max-height: 400px;
            overflow-y: auto;
            padding: 10px;
            border-radius: 5px;
            background: #f8fbff;
            border: 1px solid #e0e9ff;
            margin-bottom: 15px;
        }
        .message {
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 18px;
            display: inline-block;
            max-width: 80%;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.4;
        }
        .sent {
            background: #2d8cff;
            color: white;
            float: right;
            clear: both;
            border-bottom-right-radius: 5px;
        }
        .received {
            background: #e9f2ff;
            color: #333;
            float: left;
            clear: both;
            border-bottom-left-radius: 5px;
        }
        .input-area {
            display: flex;
            margin-top: 10px;
            gap: 10px;
        }
        textarea {
            flex: 1;
            padding: 12px;
            border-radius: 20px;
            border: 1px solid #cce0ff;
            background: white;
            color: #333;
            resize: none;
            font-family: Arial, sans-serif;
        }
        textarea:focus {
            outline: none;
            border-color: #2d8cff;
        }
        button {
            padding: 12px 20px;
            background: #2d8cff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 20px;
            font-weight: bold;
        }
        button:hover {
            background: #1a7ae6;
        }
        .back-btn {
            display: inline-block;
            margin-bottom: 15px;
            background: #e9f2ff;
            padding: 8px 15px;
            color: #2d8cff;
            text-decoration: none;
            border-radius: 20px;
            font-size: 14px;
            border: 1px solid #cce0ff;
        }
        .back-btn:hover {
            background: #d9e7ff;
        }
        h2 {
            color: #2d8cff;
            margin-top: 0;
            font-size: 20px;
        }
        #chat-box::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>

    <div class="container">
        <a href="messages.php" class="back-btn">⬅ Back to Messages</a>
        <h2>Chat with <?php echo $receiver_name; ?></h2>
        
        <div class="chat-box" id="chat-box">
            <?php while ($message = $messages_query->fetch_assoc()) { ?>
                <div class="message <?php echo $message["sender_id"] == $user_id ? 'sent' : 'received'; ?>">
                    <?php echo htmlspecialchars($message["message"]); ?>
                </div>
            <?php } ?>
        </div>

        <form method="POST" class="input-area">
            <textarea name="message" id="message" rows="2" required placeholder="Type your message..."></textarea>
            <button type="submit">Send</button>
        </form>
    </div>

   <script>
function fetchMessages() {
    fetch("fetch_messages.php?user_id=<?php echo $receiver_id; ?>")
        .then(response => response.json())
        .then(messages => {
            let chatBox = document.getElementById("chat-box");
            chatBox.innerHTML = "";

            messages.forEach(msg => {
                let messageDiv = document.createElement("div");
                messageDiv.className = `message ${msg.type}`;
                messageDiv.textContent = msg.text;
                chatBox.appendChild(messageDiv);
            });

            chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll to latest message
        })
        .catch(error => console.error("Error fetching messages:", error));
}

// Auto-refresh messages every 5 seconds
setInterval(fetchMessages, 5000);
fetchMessages();
   </script>

</body>
</html>