<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"]) || !isset($_POST["user_id"])) {
    echo json_encode(["success" => false, "message" => "Unauthorized request"]);
    exit();
}

$user_id = $_SESSION["user_id"];
$chat_partner_id = $_POST["user_id"];

// Delete chat messages
$stmt = $conn->prepare("DELETE FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?)");
$stmt->bind_param("iiii", $user_id, $chat_partner_id, $chat_partner_id, $user_id);
$success = $stmt->execute();
$stmt->close();

echo json_encode(["success" => $success]);
?>