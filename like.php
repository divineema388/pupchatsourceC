<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_id"])) {
    $post_id = intval($_POST["post_id"]);

    // Check if user already liked the post
    $check_like = $conn->query("SELECT * FROM likes WHERE user_id = '$user_id' AND post_id = '$post_id'");

    if ($check_like->num_rows > 0) {
        // Unlike the post
        $conn->query("DELETE FROM likes WHERE user_id = '$user_id' AND post_id = '$post_id'");
        echo json_encode(["status" => "unliked"]);
    } else {
        // Like the post
        $conn->query("INSERT INTO likes (user_id, post_id) VALUES ('$user_id', '$post_id')");
        echo json_encode(["status" => "liked"]);
    }
}
?>