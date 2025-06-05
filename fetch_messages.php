<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"]) || !isset($_GET["user_id"])) {
    exit();
}

$user_id = $_SESSION["user_id"];
$receiver_id = intval($_GET["user_id"]);

$messages_query = $conn->query("
    SELECT * FROM messages 
    WHERE (sender_id = '$user_id' AND receiver_id = '$receiver_id') 
       OR (sender_id = '$receiver_id' AND receiver_id = '$user_id') 
    ORDER BY sent_at ASC
");

$messages = [];
while ($message = $messages_query->fetch_assoc()) {
    $messages[] = [
        "text" => htmlspecialchars($message["message"]),
        "type" => $message["sender_id"] == $user_id ? "sent" : "received"
    ];
}

echo json_encode($messages);
?>