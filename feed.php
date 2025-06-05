<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Fetch posts from user, friends, and public users
$posts_query = $conn->query("
    SELECT posts.id, posts.user_id, posts.content, posts.image, posts.created_at, users.username, users.profile_pic, users.verified
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    LEFT JOIN friends ON (friends.user1_id = '$user_id' AND friends.user2_id = posts.user_id)
                     OR (friends.user2_id = '$user_id' AND friends.user1_id = posts.user_id)
    WHERE posts.user_id = '$user_id' 
    OR (friends.status = 'accepted') 
    OR posts.public = 1 
    GROUP BY posts.id 
    ORDER BY posts.created_at DESC
");

// Handle new post submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_post"])) {
    $content = trim($_POST["content"]);
    $public = isset($_POST["public"]) ? 1 : 0;
    $image_path = null;

    // Handle image upload
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        $image_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $image_name;
        $image_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (in_array($image_type, ["jpg", "jpeg", "png", "gif"])) {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $image_path = $target_file;
            }
        }
    }

    if (!empty($content) || $image_path) {
        $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image, public) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $user_id, $content, $image_path, $public);
        $stmt->execute();
    }

    header("Location: feed.php");
    exit();
}

// Handle likes/unlikes
if (isset($_GET["like"])) {
    $post_id = intval($_GET["like"]);
    $conn->query("INSERT INTO likes (user_id, post_id) VALUES ('$user_id', '$post_id') ON DUPLICATE KEY UPDATE id=id");
    header("Location: feed.php");
    exit();
}

if (isset($_GET["unlike"])) {
    $post_id = intval($_GET["unlike"]);
    $conn->query("DELETE FROM likes WHERE user_id = '$user_id' AND post_id = '$post_id'");
    header("Location: feed.php");
    exit();
}

// Fetch user data for the navbar
$user_data = $conn->query("SELECT username, profile_pic FROM users WHERE id = '$user_id'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - News Feed</title>
    
    <!-- External CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
        :root {
            --primary: #6C63FF;
            --secondary: #FF6584;
            --light: #F8F9FA;
            --dark: #212529;
            --gray: #6C757D;
            --light-gray: #E9ECEF;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary);
        }
        
        .navbar-brand i {
            color: var(--secondary);
        }
        
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding-bottom: 80px;
        }
        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            display: flex;
            align-items: center;
            padding: 16px;
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .profile-pic {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .profile-pic:hover {
            transform: scale(1.1);
        }
        
        .user-info {
            margin-left: 12px;
        }
        
        .username {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0;
        }
        
        .post-time {
            font-size: 12px;
            color: var(--gray);
        }
        
        .verified-badge {
            width: 16px;
            height: 16px;
            margin-left: 4px;
        }
        
        .card-body {
            padding: 16px;
        }
        
        .post-content {
            font-size: 15px;
            line-height: 1.6;
            color: var(--dark);
            margin-bottom: 12px;
        }
        
        .post-image {
            width: 100%;
            border-radius: 12px;
            margin-top: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .post-image:hover {
            transform: scale(1.02);
        }
        
        .card-footer {
            background: white;
            padding: 12px 16px;
            border-top: 1px solid rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
        }
        
        .like-btn {
            background: none;
            border: none;
            color: var(--gray);
            font-size: 1.2em;
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 8px 12px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }
        
        .like-btn:hover {
            background: rgba(220, 53, 69, 0.1);
            color: var(--secondary);
        }
        
        .like-btn.liked {
            color: var(--secondary);
        }
        
        .like-count {
            margin-left: 6px;
            font-size: 14px;
        }
        
        .post-form {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            padding: 16px;
        }
        
        .post-form textarea {
            resize: none;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.1);
            padding: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
        }
        
        .post-form textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
        }
        
        .post-form button {
            width: 100%;
            border-radius: 50px;
            background: var(--primary);
            border: none;
            padding: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .post-form button:hover {
            background: #5a52e0;
            transform: translateY(-2px);
        }
        
        .custom-file-upload {
            display: inline-block;
            padding: 8px 12px;
            cursor: pointer;
            background: var(--light-gray);
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-top: 8px;
        }
        
        .custom-file-upload:hover {
            background: #dee2e6;
        }
        
        .custom-file-upload i {
            margin-right: 6px;
        }
        
        .footer {
            text-align: center;
            padding: 20px;
            font-size: 14px;
            color: var(--gray);
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        }
        
        .nav-tabs {
            border-bottom: none;
            justify-content: center;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: var(--gray);
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: transparent;
            border-bottom: 2px solid var(--primary);
        }
        
        .nav-tabs .nav-link:hover {
            color: var(--primary);
            border-color: transparent;
        }
        
        /* Animations */
        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Floating action button */
        .fab {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(108, 99, 255, 0.3);
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .fab:hover {
            transform: translateY(-5px) rotate(10deg);
            box-shadow: 0 8px 16px rgba(108, 99, 255, 0.4);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="#">
            <i class="fas fa-paw"></i> PupChat
        </a>
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($user_data['profile_pic'] ?? 'media/default-profile.png'); ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                    <li><a class="dropdown-item" href="settings.php">Settings</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="container animate__animated animate__fadeIn">
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="#">For You</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Following</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#">Popular</a>
        </li>
    </ul>

    <!-- Create Post Form -->
    <div class="post-form animate__animated animate__fadeInUp">
        <form action="" method="POST" enctype="multipart/form-data">
            <textarea class="form-control mb-2" name="content" rows="3" placeholder="What's on your mind, pup?" required></textarea>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <label for="file-upload" class="custom-file-upload">
                        <i class="fas fa-camera"></i> Photo
                    </label>
                    <input id="file-upload" type="file" name="image" accept="image/*" style="display:none;">
                    
                    <div class="form-check form-check-inline ms-2">
                        <input class="form-check-input" type="checkbox" name="public" id="publicCheck">
                        <label class="form-check-label" for="publicCheck">Public</label>
                    </div>
                </div>
                <button class="btn btn-primary px-4" type="submit" name="new_post">Post</button>
            </div>
        </form>
    </div>

    <!-- Recent Posts -->
    <?php while ($post = $posts_query->fetch_assoc()) { 
        $post_id = $post["id"];
        $likes_count = $conn->query("SELECT COUNT(*) AS count FROM likes WHERE post_id = '$post_id'")->fetch_assoc()["count"];
        $liked_by_user = $conn->query("SELECT * FROM likes WHERE user_id = '$user_id' AND post_id = '$post_id'")->num_rows > 0;
    ?>
    <div class="card fade-in">
        <div class="card-header">
            <img src="<?php echo htmlspecialchars($post["profile_pic"]); ?>" alt="Profile Picture" class="profile-pic">
            <div class="user-info">
                <h6 class="username mb-0">
                    <?php echo htmlspecialchars($post["username"]); ?>
                    <?php if ($post["verified"] == 1) { ?>
                        <img src="media/veri.png" alt="Verified" class="verified-badge">
                    <?php } ?>
                </h6>
                <small class="post-time"><?php echo htmlspecialchars($post["created_at"]); ?></small>
            </div>
        </div>
        <div class="card-body">
            <p class="post-content"><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>
            <?php if (!empty($post["image"])): ?>
                <img src="<?php echo htmlspecialchars($post["image"]); ?>" class="post-image img-fluid">
            <?php endif; ?>
        </div>
        <div class="card-footer">
            <a href="feed.php?<?php echo $liked_by_user ? 'unlike' : 'like'; ?>=<?php echo $post_id; ?>" class="like-btn <?php echo $liked_by_user ? 'liked' : ''; ?>">
                <i class="fa <?php echo $liked_by_user ? 'fa-heart' : 'fa-heart-o'; ?>"></i>
                <span class="like-count"><?php echo $likes_count; ?></span>
            </a>
        </div>
    </div>
    <?php } ?>
</div>

<div class="fab animate__animated animate__bounceIn">
    <i class="fas fa-paw"></i>
</div>

<div class="footer">
    © 2025 PupChat - Connecting pups around the world
</div>

<script>
    // Add animation to like button
    document.querySelectorAll('.like-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!this.classList.contains('liked')) {
                this.classList.add('animate__animated', 'animate__pulse');
                setTimeout(() => {
                    this.classList.remove('animate__animated', 'animate__pulse');
                }, 1000);
            }
        });
    });
    
    // Floating action button animation
    const fab = document.querySelector('.fab');
    fab.addEventListener('mouseenter', () => {
        fab.classList.add('animate__animated', 'animate__pulse');
    });
    fab.addEventListener('mouseleave', () => {
        fab.classList.remove('animate__animated', 'animate__pulse');
    });
    fab.addEventListener('click', () => {
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
    
    // Animate cards when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                observer.unobserve(entry.target);
            }
        });
    }, {threshold: 0.1});
    
    document.querySelectorAll('.card').forEach(card => {
        observer.observe(card);
    });
</script>

</body>
</html>