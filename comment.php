<?php  
session_start();  
include "db_connect.php";  

if (!isset($_SESSION["user_id"])) {  
    header("Location: login.php");  
    exit();  
}  

$user_id = $_SESSION["user_id"];  
$post_id = isset($_GET['post_id']) ? intval($_GET['post_id']) : 0;

// Fetch post details
$post_query = $conn->prepare("
    SELECT posts.*, users.username, users.profile_pic, users.verified,
           (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count,
           (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS user_liked,
           (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count
    FROM posts
    JOIN users ON posts.user_id = users.id
    WHERE posts.id = ?
");
$post_query->bind_param("ii", $user_id, $post_id);
$post_query->execute();
$post = $post_query->get_result()->fetch_assoc();

if (!$post) {
    header("Location: home.php");
    exit();
}

// Handle new comment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_comment"])) {
    $content = trim($_POST["content"]);
    $parent_id = isset($_POST["parent_id"]) ? intval($_POST["parent_id"]) : null;
    
    if (!empty($content)) {
        $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, parent_id, content) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $post_id, $user_id, $parent_id, $content);
        $stmt->execute();
    }
    
    header("Location: comment.php?post_id=" . $post_id);
    exit();
}

// Handle comment deletion
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_comment"])) {
    $comment_id = intval($_POST["comment_id"]);
    
    // Verify the user owns the comment before deleting
    $verify_owner = $conn->prepare("SELECT user_id FROM comments WHERE id = ?");
    $verify_owner->bind_param("i", $comment_id);
    $verify_owner->execute();
    $owner_result = $verify_owner->get_result()->fetch_assoc();
    
    if ($owner_result && $owner_result['user_id'] == $user_id) {
        $delete_stmt = $conn->prepare("DELETE FROM comments WHERE id = ?");
        $delete_stmt->bind_param("i", $comment_id);
        $delete_stmt->execute();
    }
    
    header("Location: comment.php?post_id=" . $post_id);
    exit();
}

// Handle likes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["like_post"])) {  
    $post_id = intval($_POST["post_id"]);  
    $check_like = $conn->prepare("SELECT * FROM likes WHERE user_id = ? AND post_id = ?");  
    $check_like->bind_param("ii", $user_id, $post_id);  
    $check_like->execute();  
    $result = $check_like->get_result();  

    if ($result->num_rows > 0) {  
        $unlike = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");  
        $unlike->bind_param("ii", $user_id, $post_id);  
        $unlike->execute();  
    } else {  
        $like = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");  
        $like->bind_param("ii", $user_id, $post_id);  
        $like->execute();  
    }  

    header("Location: comment.php?post_id=" . $post_id);  
    exit();  
}

// Fetch comments with user details
$comments_query = $conn->prepare("
    SELECT c.*, u.username, u.profile_pic, u.verified
    FROM comments c
    JOIN users u ON c.user_id = u.id
    WHERE c.post_id = ? AND c.parent_id IS NULL
    ORDER BY c.created_at DESC
");
$comments_query->bind_param("i", $post_id);
$comments_query->execute();
$comments = $comments_query->get_result();

// Fetch user details for header
$user_query = $conn->prepare("SELECT username, profile_pic, verified FROM users WHERE id = ?");  
$user_query->bind_param("i", $user_id);  
$user_query->execute();  
$user = $user_query->get_result()->fetch_assoc();  
$username = htmlspecialchars($user["username"]);  
$profile_pic = !empty($user["profile_pic"]) ? htmlspecialchars($user["profile_pic"]) . "?t=" . time() : "media/default_profile.png";  
$verified = $user["verified"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - PupChat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #0056b3;
            --accent-color: #ff6b6b;
            --text-color: #333;
            --light-bg: #f8f9fa;
            --card-bg: #ffffff;
        }
        
        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            padding-bottom: 60px;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
        }
        
        .post-preview {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .post-content {
            padding: 15px;
            font-size: 15px;
            line-height: 1.5;
        }
        
        .post-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }
        
        .post-actions {
            display: flex;
            padding: 10px 15px;
            border-top: 1px solid #eee;
        }
        
        .action-btn {
            flex: 1;
            text-align: center;
            padding: 8px 0;
            color: #666;
            font-size: 14px;
            border-radius: 5px;
            transition: all 0.2s;
        }
        
        .action-btn:hover {
            background: #f0f0f0;
            color: var(--primary-color);
        }
        
        .action-btn.active {
            color: var(--accent-color);
        }
        
        /* Comment delete sidebar styles */
        .comment-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            margin-bottom: 10px;
        }
        
        .comment-content {
            position: relative;
            background: var(--card-bg);
            transition: transform 0.3s ease;
            z-index: 1;
        }
        
        .comment-card {
            padding: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .delete-sidebar {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 80px;
            background: #ff6b6b;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            z-index: 0;
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        
        .comment-container.swiped .comment-content {
            transform: translateX(-80px);
        }
        
        .comment-container.swiped .delete-sidebar {
            transform: translateX(0);
        }
        
        .delete-btn {
            background: none;
            border: none;
            color: white;
            font-size: 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            cursor: pointer;
        }
        
        .delete-btn i {
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .comment-profile-img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .comment-username {
            font-weight: 600;
            font-size: 14px;
        }
        
        .comment-time {
            font-size: 12px;
            color: #999;
            margin-left: 8px;
        }
        
        .comment-text {
            font-size: 14px;
            line-height: 1.4;
            margin-left: 46px;
        }
        
        .comment-actions {
            display: flex;
            margin-left: 46px;
            margin-top: 8px;
        }
        
        .comment-action {
            font-size: 12px;
            color: #666;
            margin-right: 15px;
            cursor: pointer;
        }
        
        .comment-action:hover {
            color: var(--primary-color);
        }
        
        .reply-card {
            margin-left: 15px;
            padding-left: 15px;
            border-left: 3px solid #eee;
        }
        
        .fixed-comment-box {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 10px;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            z-index: 1000;
        }
        
        .comment-input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 10px 15px;
            font-size: 14px;
            outline: none;
            resize: none;
            max-height: 100px;
        }
        
        .send-comment-btn {
            margin-left: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .post-preview {
                border-radius: 0;
                margin-bottom: 10px;
            }
            
            .profile-img {
                width: 36px;
                height: 36px;
            }
            
            .comment-profile-img {
                width: 32px;
                height: 32px;
            }
            
            .comment-text {
                margin-left: 42px;
                font-size: 13px;
            }
            
            .comment-actions {
                margin-left: 42px;
            }
            
            .reply-card {
                margin-left: 10px;
                padding-left: 10px;
            }
            
            .delete-sidebar {
                width: 70px;
            }
            
            .comment-container.swiped .comment-content {
                transform: translateX(-70px);
            }
        }
        
        /* Touch device styles */
        @media (hover: none) {
            .comment-container {
                touch-action: pan-y;
            }
            
            .comment-content {
                cursor: grab;
            }
            
            .comment-content:active {
                cursor: grabbing;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container-fluid">
            <a href="home.php" class="navbar-brand d-flex align-items-center">
                <i class="fas fa-arrow-left me-2"></i>
                <span>Comments</span>
            </a>
            <div class="d-flex align-items-center">
                <a href="profile.php?id=<?php echo $user_id; ?>" class="text-decoration-none">
                    <img src="<?php echo $profile_pic; ?>" class="profile-img">
                </a>
            </div>
        </div>
    </nav>

    <div class="container mt-3">
        <!-- Post Preview (simplified) -->
        <div class="post-preview">
            <?php if (!empty($post["image"])): ?>
                <img src="<?php echo htmlspecialchars($post["image"]); ?>" class="post-image w-100">
            <?php endif; ?>
            
            <div class="post-content">
                <div class="d-flex align-items-center mb-2">
                    <img src="<?php echo htmlspecialchars($post["profile_pic"]); ?>" class="profile-img me-2">
                    <div>
                        <strong class="d-block">
                            <?php echo htmlspecialchars($post["username"]); ?>
                            <?php if ($post["verified"] == 1): ?>
                                <img src="media/veri.png" width="16" height="16" alt="Verified">
                            <?php endif; ?>
                        </strong>
                        <small class="text-muted"><?php echo date('M j, Y', strtotime($post["created_at"])); ?></small>
                    </div>
                </div>
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($post["content"])); ?></p>
            </div>
            
            <div class="post-actions">
                <form method="POST" class="d-flex flex-grow-1">
                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                    <button type="submit" name="like_post" class="action-btn <?php echo $post["user_liked"] ? 'active' : ''; ?>">
                        <i class="fas fa-heart"></i> <?php echo $post["like_count"]; ?>
                    </button>
                </form>
                <div class="action-btn">
                    <i class="fas fa-comment"></i> <?php echo $post["comment_count"]; ?>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments-section mt-3">
            <?php if ($comments->num_rows > 0): ?>
                <h6 class="px-2 mb-3 text-muted">Comments</h6>
                <?php while ($comment = $comments->fetch_assoc()): ?>
                    <?php include 'comment_item.php'; ?>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-comment-slash fa-2x mb-2"></i>
                    <p>No comments yet</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Fixed Comment Box at Bottom -->
    <form method="POST" class="fixed-comment-box">
        <img src="<?php echo $profile_pic; ?>" class="comment-profile-img d-none d-sm-block">
        <textarea name="content" class="comment-input" placeholder="Write a comment..." rows="1" required></textarea>
        <input type="hidden" name="new_comment" value="1">
        <button type="submit" class="send-comment-btn">
            <i class="fas fa-paper-plane"></i>
        </button>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-resize textarea as user types
        const textarea = document.querySelector('.comment-input');
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Focus the textarea when page loads
        window.addEventListener('load', function() {
            textarea.focus();
        });
        
        // Function to show/hide reply forms
        function toggleReplyForm(commentId) {
            const form = document.getElementById('reply-form-' + commentId);
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
            if (form.style.display === 'block') {
                form.querySelector('textarea').focus();
            }
        }
        
        // Swipe to delete functionality
        document.addEventListener('DOMContentLoaded', function() {
            const commentContainers = document.querySelectorAll('.comment-container');
            let startX, currentX;
            let activeComment = null;
            
            commentContainers.forEach(container => {
                const commentContent = container.querySelector('.comment-content');
                const deleteSidebar = container.querySelector('.delete-sidebar');
                
                if (!deleteSidebar) return; // Skip if no delete sidebar (not owner)
                
                commentContent.addEventListener('touchstart', handleTouchStart, { passive: false });
                commentContent.addEventListener('touchmove', handleTouchMove, { passive: false });
                commentContent.addEventListener('touchend', handleTouchEnd);
                commentContent.addEventListener('click', handleClick);
            });
            
            function handleTouchStart(e) {
                startX = e.touches[0].clientX;
                activeComment = e.currentTarget.parentElement;
            }
            
            function handleTouchMove(e) {
                if (!activeComment) return;
                
                currentX = e.touches[0].clientX;
                const diff = startX - currentX;
                
                // Only allow right-to-left swipe
                if (diff > 0) {
                    e.preventDefault();
                    const maxSwipe = window.innerWidth < 768 ? 70 : 80;
                    const transformValue = Math.min(diff, maxSwipe);
                    e.currentTarget.style.transform = `translateX(-${transformValue}px)`;
                    
                    if (transformValue === maxSwipe) {
                        activeComment.classList.add('swiped');
                    } else {
                        activeComment.classList.remove('swiped');
                    }
                }
            }
            
            function handleTouchEnd() {
                if (!activeComment) return;
                
                const currentTransform = window.getComputedStyle(activeComment.querySelector('.comment-content')).transform;
                const matrix = new DOMMatrix(currentTransform);
                const currentTranslateX = matrix.m41;
                const threshold = window.innerWidth < 768 ? 35 : 40;
                
                if (currentTranslateX < -threshold) {
                    activeComment.classList.add('swiped');
                } else {
                    activeComment.classList.remove('swiped');
                    activeComment.querySelector('.comment-content').style.transform = 'translateX(0)';
                }
                
                activeComment = null;
            }
            
            function handleClick(e) {
                // If the comment is swiped out, clicking it should reset it
                const container = e.currentTarget.parentElement;
                if (container.classList.contains('swiped')) {
                    e.preventDefault();
                    container.classList.remove('swiped');
                    e.currentTarget.style.transform = 'translateX(0)';
                }
            }
            
            // Close all swiped comments when clicking elsewhere
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.comment-container')) {
                    document.querySelectorAll('.comment-container.swiped').forEach(container => {
                        container.classList.remove('swiped');
                        container.querySelector('.comment-content').style.transform = 'translateX(0)';
                    });
                }
            });
        });
    </script>
</body>
</html>