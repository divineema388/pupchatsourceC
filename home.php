
<?php  
session_start();  
include "db_connect.php";  
  
if (!isset($_SESSION["user_id"])) {  
    header("Location: login.php");  
    exit();  
}  
  
$user_id = $_SESSION["user_id"];  
  
// Fetch user details  
$user_query = $conn->prepare("SELECT username, profile_pic, verified FROM users WHERE id = ?");  
$user_query->bind_param("i", $user_id);  
$user_query->execute();  
$user = $user_query->get_result()->fetch_assoc();  
$username = htmlspecialchars($user["username"]);  
$profile_pic = !empty($user["profile_pic"]) ? htmlspecialchars($user["profile_pic"]) . "?t=" . time() : "media/default_profile.png";  
$verified = $user["verified"];

// Fetch posts
$posts_query = $conn->prepare("  
    SELECT posts.id, posts.user_id, posts.content, COALESCE(posts.image, '') AS image, posts.created_at,  
           users.username, users.profile_pic, users.verified,  
           (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id) AS like_count,  
           (SELECT COUNT(*) FROM likes WHERE likes.post_id = posts.id AND likes.user_id = ?) AS user_liked,
           (SELECT COUNT(*) FROM comments WHERE comments.post_id = posts.id) AS comment_count
    FROM posts   
    JOIN users ON posts.user_id = users.id   
    WHERE posts.public = 1 OR posts.user_id = ?   
    ORDER BY posts.created_at DESC   
    LIMIT 5  
");
$posts_query->bind_param("ii", $user_id, $user_id);  
$posts_query->execute();  
$posts = $posts_query->get_result();
  
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
  
    header("Location: home.php");  
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
  
    header("Location: home.php");  
    exit();  
}  
?>  

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat V2.0 - Home</title>
    <!-- External CSS Libraries -->
    <!-- Add this to your existing head section -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/hover.css/2.3.1/css/hover-min.css">

    <style>
    /* Side Menu Styles */
.side-menu {
    position: fixed;
    top: 0;
    left: -300px;
    width: 280px;
    height: 100%;
    background: white;
    z-index: 1050;
    box-shadow: 5px 0 15px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow-y: auto;
}

.side-menu-header {
    padding: 20px;
    background: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
    color: white;
    display: flex;
    align-items: center;
    position: relative;
}

.close-menu-btn {
    position: absolute;
    right: 15px;
    top: 15px;
    background: transparent;
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
}

.side-menu-items {
    padding: 10px 0;
}

.side-menu-item {
    display: flex;
    align-items: center;
    padding: 15px 20px;
    color: #2d3436;
    text-decoration: none;
    transition: all 0.2s;
}

.side-menu-item i {
    margin-right: 15px;
    color: #6c5ce7;
    width: 20px;
    text-align: center;
}

.side-menu-item:hover {
    background: #f5f6fa;
    transform: translateX(5px);
}

.side-menu-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s;
}

/* When menu is active */
.menu-active .side-menu {
    transform: translateX(300px);
}

.menu-active .side-menu-overlay {
    opacity: 1;
    visibility: visible;
}

/* Add this to prevent body scrolling when menu is open */
.menu-active body {
    overflow: hidden;
}

        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --dark-color: #2d3436;
            --light-color: #f5f6fa;
            --text-color: #2d3436;
            --card-bg: #ffffff;
            --nav-bg: linear-gradient(135deg, #6c5ce7 0%, #a29bfe 100%);
            --shadow: 0 4px 20px rgba(108, 92, 231, 0.15);
        }
        
        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-color);
            padding-bottom: 80px;
        }
        
        /* Modern Navbar */
        .navbar {
            background: var(--nav-bg);
            padding: 10px 0;
            box-shadow: 0 4px 12px rgba(108, 92, 231, 0.2);
            border-bottom: none;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .navbar-brand:hover {
            transform: translateX(3px);
        }
        
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
        }
        
        .profile-img:hover {
            border-color: white;
            transform: scale(1.05);
        }
        
        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            margin: 0 8px;
            padding: 8px 15px !important;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .nav-link i {
            margin-right: 8px;
            font-size: 1.1rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.15);
            color: white !important;
            transform: translateY(-2px);
        }
        
        /* Post Form */
        .post-form {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: none;
            margin-bottom: 25px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        
        .post-form:hover {
            transform: translateY(-3px);
        }
        
        .post-textarea {
            border: none;
            resize: none;
            font-size: 16px;
            background: #f0f2f5;
            border-radius: 20px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .post-textarea:focus {
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(108, 92, 231, 0.2);
        }
        
        .file-upload-btn {
            cursor: pointer;
            padding: 8px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            color: var(--primary-color);
            background: rgba(108, 92, 231, 0.1);
        }
        
        .file-upload-btn:hover {
            background: rgba(108, 92, 231, 0.2);
            transform: translateY(-2px);
        }
        
        .file-upload-btn i {
            margin-right: 5px;
        }
        
        /* Posts */
        .post-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            margin-bottom: 25px;
            overflow: hidden;
            border: none;
            transition: all 0.3s ease;
        }
        
        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(108, 92, 231, 0.2);
        }
        
        .post-content {
            padding: 15px;
            line-height: 1.6;
        }
        
        .post-actions {
            display: flex;
            padding: 10px 15px;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .action-btn {
            flex: 1;
            text-align: center;
            padding: 8px 0;
            color: #666;
            font-size: 14px;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .action-btn i {
            margin-right: 5px;
        }
        
        .action-btn:hover {
            background: rgba(108, 92, 231, 0.1);
            color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        .action-btn.active {
            color: var(--accent-color);
        }
        
        .read-more-btn {
            color: var(--primary-color);
            text-decoration: none;
            cursor: pointer;
            font-size: 0.9em;
            font-weight: 500;
            transition: all 0.2s;
            display: inline-block;
            margin-top: 5px;
        }
        
        .read-more-btn:hover {
            text-decoration: underline;
            color: var(--secondary-color);
        }
        
        /* Buttons */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        /* Badge for version */
        .version-badge {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--primary-color);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(108, 92, 231, 0.3);
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 1rem;
            }
            
            .profile-img {
                width: 36px;
                height: 36px;
            }
            
            .nav-link {
                padding: 8px 10px !important;
                margin: 0 5px;
                font-size: 0.9rem;
            }
            
            .nav-link i {
                margin-right: 5px;
                font-size: 1rem;
            }
            
            .post-textarea {
                font-size: 14px;
                padding: 10px;
            }
        }
    </style>
</head>
<body>
<!-- Swipeable Side Menu -->
<div class="side-menu">
    <div class="side-menu-header">
        <img src="<?= $profile_pic ?>" class="profile-img me-2">
        <span><?= $username ?></span>
        <button class="close-menu-btn">&times;</button>
    </div>
    <div class="side-menu-items">
        <a href="settings.php" class="side-menu-item"><i class="fas fa-cog"></i> Settings</a>
        <a href="help.php" class="side-menu-item"><i class="fas fa-question-circle"></i> Help</a>
        <a href="about.php" class="side-menu-item"><i class="fas fa-info-circle"></i> About</a>
    </div>
</div>
<div class="side-menu-overlay"></div>


    <!-- Navbar -->
<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container-fluid">
    <button class="navbar-toggler me-2" type="button" id="menuButton">
      <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="profile.php?id=<?php echo $user_id; ?>">
      <img src="<?= $profile_pic ?>" alt="Profile" class="profile-img me-2">
      <?= $username ?>
      <?= $verified ? ' <i class="fas fa-check-circle text-info"></i>' : '' ?>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarOptions" aria-controls="navbarOptions" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarOptions">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="friends.php"><i class="fas fa-user-friends"></i> Friends</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="messages.php"><i class="fas fa-envelope"></i> Messages</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="group.php"><i class="fas fa-users"></i> Group</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="search.php"><i class="fas fa-search"></i> Search</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
    <div class="container mt-4">
        <!-- Create Post Form -->
        <div class="card p-3 post-form mb-4 animate__animated animate__fadeIn">
            <form action="home.php" method="POST" enctype="multipart/form-data">
                <div class="d-flex align-items-center">
                    <img src="<?php echo $profile_pic; ?>" class="profile-img me-3">
                    <textarea class="form-control post-textarea" name="content" rows="2" placeholder="What's on your mind, <?php echo $username; ?>?" required></textarea>
                </div>
                <hr class="my-3">
                <div class="d-flex justify-content-between align-items-center px-2">
                    <label class="file-upload-btn">
                        <i class="fa fa-image"></i> Photo/Video
                        <input type="file" name="image" accept="image/*" class="d-none">
                    </label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" name="public" id="publicSwitch">
                        <label class="form-check-label" for="publicSwitch">Public</label>
                    </div>
                    <button class="btn btn-primary" type="submit" name="new_post">
                        <i class="fas fa-paper-plane me-1"></i> Post
                    </button>
                </div>
            </form>
        </div>

        <!-- Posts Section -->
        <h3 class="mb-4 text-center text-dark fw-bold">Welcome to PupChat</h3>
        
        <?php if ($posts->num_rows > 0): ?>
            <?php while ($post = $posts->fetch_assoc()): ?>
                <div class="card post-card mb-4 animate__animated animate__fadeInUp" data-aos="fade-up">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="<?php echo htmlspecialchars($post["profile_pic"]); ?>" class="profile-img me-3">
                            <div>
                                <strong class="d-block">
                                    <?php echo htmlspecialchars($post["username"]); ?>
                                    <?php if ($post["verified"] == 1): ?>
                                        <img src="media/veri.png" width="16" height="16" alt="Verified" class="ms-1">
                                    <?php endif; ?>
                                </strong>
                                <small class="text-muted"><?php echo date('M j, Y · g:i A', strtotime($post["created_at"])); ?></small>
                            </div>
                        </div>
                        
                        <!-- Post Content with Read More -->
                        <p class="mb-3 post-content">
                            <?php  
                            $content = nl2br(htmlspecialchars($post["content"]));  
                            $maxLength = 200;
                            if (strlen($content) > $maxLength) {  
                                $shortContent = substr($content, 0, $maxLength) . '...';  
                                echo '<span class="short-text">' . $shortContent . '</span>';  
                                echo '<span class="full-text" style="display: none;">' . $content . '</span>';  
                                echo '<button class="btn btn-link read-more-btn p-0" onclick="toggleReadMore(this)">Read More</button>';  
                            } else {  
                                echo $content;  
                            }  
                            ?>  
                        </p>
                        
                        <?php if (!empty($post["image"])): ?>
                            <img src="<?php echo htmlspecialchars($post["image"]); ?>" class="img-fluid rounded-3 mb-3 w-100" style="max-height: 500px; object-fit: cover;">
                        <?php endif; ?>
                        
                        <div class="post-actions">
                            <form method="POST" class="d-flex flex-grow-1">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="like_post" class="action-btn <?php echo $post["user_liked"] ? 'active' : ''; ?>">
                                    <i class="fas fa-heart"></i> <span><?php echo $post["like_count"]; ?></span>
                                </button>
                            </form>
                            <a href="comment.php?post_id=<?php echo $post['id']; ?>" class="action-btn">
                                <i class="fas fa-comment"></i> <span><?php echo $post["comment_count"]; ?></span>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5 animate__animated animate__fadeIn">
                <i class="fas fa-newspaper fa-3x mb-3" style="color: var(--primary-color);"></i>
                <h5 class="text-muted">No posts to show</h5>
                <p class="text-muted">Be the first to create a post!</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Version Badge -->
    <div class="version-badge animate__animated animate__fadeInRight">
        PupChat V2.0
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        // Initialize animations
        if (typeof AOS !== 'undefined') {
            AOS.init({
                duration: 800,
                once: true
            });
        }
        
        function toggleReadMore(button) {
            const postContent = button.closest('.post-content');
            const shortText = postContent.querySelector('.short-text');
            const fullText = postContent.querySelector('.full-text');
            const isExpanded = fullText.style.display === 'block';

            if (isExpanded) {
                fullText.style.display = 'none';
                shortText.style.display = 'inline';
                button.textContent = 'Read More';
            } else {
                fullText.style.display = 'block';
                shortText.style.display = 'none';
                button.textContent = 'Read Less';
            }
        }
        
        // Add hover effects for cards
        document.querySelectorAll('.post-card').forEach(card => {
            card.addEventListener('mouseenter', () => {
                card.style.transform = 'translateY(-5px)';
                card.style.boxShadow = '0 8px 25px rgba(108, 92, 231, 0.2)';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = '';
                card.style.boxShadow = '';
            });
        });
    </script>
    <!-- Add this after your existing scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js"></script>
<script>
    // Side menu functionality
    const body = document.body;
    const sideMenu = document.querySelector('.side-menu');
    const overlay = document.querySelector('.side-menu-overlay');
    const closeBtn = document.querySelector('.close-menu-btn');
    
    // Initialize Hammer.js for swipe gestures
    const hammer = new Hammer(body);
    hammer.on('swiperight', function() {
        body.classList.add('menu-active');
    });
    
    // Close menu when clicking overlay or close button
    overlay.addEventListener('click', () => {
        body.classList.remove('menu-active');
    });
    
    closeBtn.addEventListener('click', () => {
        body.classList.remove('menu-active');
    });
    
    // Close menu when clicking a menu item (optional)
    document.querySelectorAll('.side-menu-item').forEach(item => {
        item.addEventListener('click', () => {
            body.classList.remove('menu-active');
        });
    });
    
    // Prevent body scrolling when menu is open
    document.addEventListener('scroll', function() {
        if (body.classList.contains('menu-active')) {
            window.scrollTo(0, 0);
        }
    });
</script>
<script>
// Add this to your existing script section
document.getElementById('menuButton').addEventListener('click', function() {
    body.classList.add('menu-active');
});
</script>
</body>
</html>