<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    echo "Unauthorized access.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["post_id"])) {
    $post_id = intval($_POST["post_id"]);
    $user_id = $_SESSION["user_id"];

    // Check if the post belongs to the logged-in user
    $check_query = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
    $check_query->bind_param("i", $post_id);
    $check_query->execute();
    $check_query->bind_result($post_user_id);
    $check_query->fetch();
    $check_query->close();

    if ($post_user_id == $user_id) {
        // Delete the post
        $delete_query = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $delete_query->bind_param("i", $post_id);
        if ($delete_query->execute()) {
            echo "success";
        } else {
            echo "error";
        }
        $delete_query->close();
    } else {
        echo "unauthorized";
    }
} else {
    echo "invalid_request";
}
?>