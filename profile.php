<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";

$profile_id = isset($_GET["user_id"]) ? $_GET["user_id"] : $_SESSION["user_id"];

$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $profile_id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found.";
    exit();
}

// Fetch friend count
$friend_query = $conn->prepare("SELECT COUNT(*) AS friend_count FROM friends WHERE (user1_id = ? OR user2_id = ?) AND status = 'accepted'");
$friend_query->bind_param("ii", $profile_id, $profile_id);
$friend_query->execute();
$friend_result = $friend_query->get_result();
$friend_data = $friend_result->fetch_assoc();
$friend_count = $friend_data["friend_count"] ?? 0; // Default to 0 if no friends

if (!$user) {
    echo "User not found.";
    exit();
}

// Default profile picture if none is set
$profile_pic = !empty($user["profile_pic"]) ? $user["profile_pic"] . "?t=" . time() : "media/default_profile.png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <title><?php echo htmlspecialchars($user["username"]); ?>'s Profile</title>
    
    <!-- External CSS Libraries -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            max-width: 800px;
            margin: auto;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #007bff;
            margin-bottom: 20px;
        }
        h2 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #007bff;
        }
        .bio-text {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }
        .button {
            padding: 12px 30px;
            margin: 10px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #0056b3;
        }
        .button:active {
            background: #004085;
        }
        .details {
            margin-top: 20px;
            color: #333;
        }
        .post-options {
            position: absolute;
            right: 10px;
            top: 10px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>
    <?php echo htmlspecialchars($user["username"]); ?>
    <?php if ($user["verified"] == 1) { ?>
        <img src="media/veri.png" alt="Verified" style="width: 20px; height: 20px; vertical-align: middle;">
    <?php } ?>
</h2>
    
    <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-pic">

    <div class="details">
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user["email"]); ?></p>
        <p><strong>Unique ID:</strong> <?php echo htmlspecialchars($user["id"]); ?></p>
        <p class="bio-text"><strong>Bio:</strong> <?php echo nl2br(htmlspecialchars($user["bio"] ?? "No bio yet.")); ?></p>
        <p class="bio-text"><strong>Friends:</strong> <?php echo $friend_count; ?></p>
    </div>

    <?php if ($profile_id == $_SESSION["user_id"]) { ?>
        <a href="edit_profile.php" class="button">Edit Profile</a>
    <?php } else { ?>
        <a href="send_message.php?receiver_id=<?php echo $user['id']; ?>" class="button">Send Message</a>
    <?php } ?>
</div>

<!-- Fetch and Display User Posts -->
<?php
$post_query = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$post_query->bind_param("i", $profile_id);
$post_query->execute();
$posts_result = $post_query->get_result();
?>

<div class="container mt-4">
    <h3><?php echo htmlspecialchars($user["username"]); ?>'s Posts</h3>

    <?php if ($posts_result->num_rows > 0) { ?>
        <?php while ($post = $posts_result->fetch_assoc()) { ?>
            <div class="card my-3 position-relative">
                <!-- Show dropdown only for the profile owner -->
                <?php if ($profile_id == $_SESSION["user_id"]) { ?>
                    <div class="post-options">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <a class="dropdown-item delete-post" href="#" data-post-id="<?php echo $post['id']; ?>">Delete Post</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <div class="card-body">
                    <p><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>
                    <?php if (!empty($post["image"])) { ?>
                        <img src="<?php echo htmlspecialchars($post["image"]); ?>" class="img-fluid rounded" alt="Post Image">
                    <?php } ?>
                    <p class="text-muted small"><?php echo date("F j, Y, g:i a", strtotime($post["created_at"])); ?></p>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p class="text-muted">No posts yet.</p>
    <?php } ?>
</div>

<!-- External JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Handle delete post
    $(".delete-post").click(function(e) {
        e.preventDefault();
        const postId = $(this).data("post-id");

        if (confirm("Are you sure you want to delete this post?")) {
            $.ajax({
                url: "delete_post.php",
                type: "POST",
                data: { post_id: postId },
                success: function(response) {
                    if (response === "success") {
                        location.reload(); // Reload the page to reflect changes
                    } else {
                        alert("Failed to delete the post.");
                    }
                },
                error: function() {
                    alert("An error occurred while deleting the post.");
                }
            });
        }
    });
});
</script>

</body>
</html>