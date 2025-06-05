<?php
session_start();
include "db_connect.php"; 

// Redirect if not logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $content = trim($_POST["content"] ?? "");
    $public = isset($_POST["public"]) ? 1 : 0;
    $image_name = "";

    // Handle image upload
    if (!empty($_FILES["image"]["name"])) {
        $image = $_FILES["image"];
        $allowed_types = ["image/jpeg", "image/png", "image/gif"];
        
        if (in_array($image["type"], $allowed_types) && $image["size"] <= 5 * 1024 * 1024) {
            $image_ext = pathinfo($image["name"], PATHINFO_EXTENSION);
            $image_name = "uploads/" . time() . "_$user_id.$image_ext";
            move_uploaded_file($image["tmp_name"], $image_name);
        }
    }

    // Insert post only if content or image is provided
    if (!empty($content) || !empty($image_name)) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, public) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $content, $image_name, $public);
        $stmt->execute();
    }

    header("Location: feed.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post - PupChat</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin: 0; padding: 20px; background: #f4f4f4; }
        .container { width: 50%; margin: auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        textarea, button, input[type="file"] { width: 100%; padding: 10px; margin: 10px 0; }
        button { background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>

<div class="container">
    <h2>Create a Post</h2>
    <form action="post.php" method="POST" enctype="multipart/form-data">
        <textarea name="content" placeholder="What's on your mind?" required></textarea>
        <input type="file" name="image" accept="image/*">
        <label><input type="checkbox" name="public"> Public Post</label>
        <button type="submit">Post</button>
    </form>
</div>

</body>
</html>