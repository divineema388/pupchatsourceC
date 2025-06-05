<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["post_id"])) {
    header("Location: home.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$post_id = intval($_GET["post_id"]);

// Fetch post details
$post_query = $conn->prepare("SELECT user_id, content, image FROM posts WHERE id = ?");
$post_query->bind_param("i", $post_id);
$post_query->execute();
$post = $post_query->get_result()->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    exit();
}

// Fetch users who liked the post
$likes_query = $conn->prepare("
    SELECT users.id, users.username, users.profile_pic, likes.created_at 
    FROM likes 
    JOIN users ON likes.user_id = users.id 
    WHERE likes.post_id = ? 
    ORDER BY likes.created_at DESC
");
$likes_query->bind_param("i", $post_id);
$likes_query->execute();
$likes = $likes_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Likes</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: #f4f7f9;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 40px;
        }
        .like-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .profile-img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        .like-info {
            flex: 1;
        }
        .like-time {
            font-size: 12px;
            color: gray;
        }
        .back-btn {
            display: block;
            margin: 20px 0;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <h3 class="mb-3">Users who liked this post</h3>
    
    <?php while ($like = $likes->fetch_assoc()): ?>
        <div class="like-card">
            <img src="<?php echo !empty($like["profile_pic"]) ? htmlspecialchars($like["profile_pic"]) . "?t=" . time() : "media/default_profile.png"; ?>" class="profile-img">
            <div class="like-info">
                <a href="profile.php?id=<?php echo $like["id"]; ?>" class="text-dark">
                    <strong><?php echo htmlspecialchars($like["username"]); ?></strong>
                </a>
                <p class="like-time">Liked on <?php echo date("F j, Y, g:i A", strtotime($like["created_at"])); ?></p>
            </div>
        </div>
    <?php endwhile; ?>

    <?php if ($likes->num_rows === 0): ?>
        <p>No likes yet.</p>
    <?php endif; ?>

    <a href="home.php" class="btn btn-secondary back-btn">Back to Home</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>