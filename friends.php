<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Handle sending friend request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["send_request"])) {
    $friend_id = intval($_POST["friend_id"]);

    // Prevent self-requests
    if ($friend_id == $user_id) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'You cannot send a friend request to yourself.'];
    } else {
        // Check if request already exists
        $check_request = $conn->query("SELECT * FROM friends WHERE 
            (user1_id = '$user_id' AND user2_id = '$friend_id') OR 
            (user1_id = '$friend_id' AND user2_id = '$user_id')");
        
        if ($check_request->num_rows == 0) {
            $conn->query("INSERT INTO friends (user1_id, user2_id, status) VALUES ('$user_id', '$friend_id', 'pending')");
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Friend request sent!'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Friend request already exists.'];
        }
    }
}

// Handle accepting friend request
if (isset($_GET["accept"])) {
    $request_id = intval($_GET["accept"]);
    $conn->query("UPDATE friends SET status = 'accepted' WHERE id = '$request_id' AND user2_id = '$user_id'");
    $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Friend request accepted!'];
}

// Handle declining friend request
if (isset($_GET["decline"])) {
    $request_id = intval($_GET["decline"]);
    $conn->query("DELETE FROM friends WHERE id = '$request_id' AND user2_id = '$user_id'");
    $_SESSION['flash_message'] = ['type' => 'info', 'message' => 'Friend request declined.'];
}

// Fetch pending friend requests
$requests_query = $conn->query("SELECT friends.id, users.id AS sender_id, users.username, users.profile_pic 
                                FROM friends 
                                JOIN users ON friends.user1_id = users.id 
                                WHERE friends.user2_id = '$user_id' AND friends.status = 'pending'");

// Fetch accepted friends
$friends_query = $conn->query("SELECT users.id, users.username, users.profile_pic, users.verified
    FROM friends 
    JOIN users ON users.id = IF(friends.user1_id = '$user_id', friends.user2_id, friends.user1_id)
    WHERE (friends.user1_id = '$user_id' OR friends.user2_id = '$user_id') 
    AND friends.status = 'accepted'");

// Fetch user data for the navbar
$user_data = $conn->query("SELECT username, profile_pic FROM users WHERE id = '$user_id'")->fetch_assoc();

// Fetch suggested friends (users not friends with)
$suggested_query = $conn->query("SELECT id, username, profile_pic FROM users 
    WHERE id != '$user_id' 
    AND id NOT IN (
        SELECT IF(user1_id = '$user_id', user2_id, user1_id) 
        FROM friends 
        WHERE user1_id = '$user_id' OR user2_id = '$user_id'
    ) 
    ORDER BY RAND() LIMIT 5");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Friends</title>
    
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
            --success: #28a745;
            --danger: #dc3545;
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
        
        .main-container {
            max-width: 800px;
            margin: 20px auto;
            padding-bottom: 100px;
        }
        
        .section-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .section-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            padding: 16px;
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .section-header i {
            color: var(--secondary);
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
        
        .friend-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .friend-item:hover {
            background-color: rgba(108, 99, 255, 0.05);
        }
        
        .friend-info {
            margin-left: 12px;
            flex-grow: 1;
        }
        
        .friend-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0;
            display: flex;
            align-items: center;
        }
        
        .verified-badge {
            width: 16px;
            height: 16px;
            margin-left: 4px;
        }
        
        .friend-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-accept {
            background-color: var(--success);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-accept:hover {
            background-color: #218838;
            transform: translateY(-2px);
        }
        
        .btn-decline {
            background-color: var(--danger);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-decline:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-view {
            background-color: var(--primary);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-view:hover {
            background-color: #5a52e0;
            transform: translateY(-2px);
        }
        
        .btn-add {
            background-color: var(--secondary);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-add:hover {
            background-color: #e64c6c;
            transform: translateY(-2px);
        }
        
        .empty-state {
            padding: 40px 20px;
            text-align: center;
            color: var(--gray);
        }
        
        .empty-state i {
            font-size: 48px;
            color: var(--light-gray);
            margin-bottom: 16px;
        }
        
        .search-form {
            padding: 16px;
            background: var(--light);
            border-radius: 12px;
            margin-bottom: 16px;
        }
        
        .search-form input {
            border-radius: 50px;
            padding: 12px 16px;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .search-form button {
            border-radius: 50px;
            padding: 12px 24px;
            font-weight: 500;
        }
        
        .flash-message {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1000;
            padding: 12px 24px;
            border-radius: 50px;
            color: white;
            font-weight: 500;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            animation: fadeInDown 0.5s, fadeOutUp 0.5s 2.5s forwards;
        }
        
        .flash-success {
            background-color: var(--success);
        }
        
        .flash-error {
            background-color: var(--danger);
        }
        
        .flash-info {
            background-color: var(--primary);
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translate(-50%, -20px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        
        @keyframes fadeOutUp {
            from { opacity: 1; transform: translate(-50%, 0); }
            to { opacity: 0; transform: translate(-50%, -20px); }
        }
        
        .pulse {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .shake {
            animation: shake 0.5s;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .tabs {
            display: flex;
            border-bottom: 2px solid rgba(108, 99, 255, 0.1);
            margin-bottom: 16px;
        }
        
        .tab {
            padding: 12px 24px;
            cursor: pointer;
            font-weight: 500;
            color: var(--gray);
            position: relative;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            color: var(--primary);
        }
        
        .tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--primary);
        }
        
        .tab:hover {
            color: var(--primary);
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

<div class="main-container animate__animated animate__fadeIn">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message flash-<?php echo $_SESSION['flash_message']['type']; ?> animate__animated animate__fadeInDown">
            <?php echo $_SESSION['flash_message']['message']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <div class="section-card animate__animated animate__fadeInUp">
        <div class="section-header">
            <span><i class="fas fa-user-plus me-2"></i> Add New Friends</span>
        </div>
        <div class="search-form">
            <form action="" method="POST" class="row g-2">
                <div class="col-md-8">
                    <input type="number" name="friend_id" class="form-control" placeholder="Enter User ID" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" name="send_request" class="btn btn-add w-100">
                        <i class="fas fa-paper-plane me-2"></i> Send Request
                    </button>
                </div>
            </form>
        </div>
        
        <div class="section-header">
            <span><i class="fas fa-users me-2"></i> Suggested Pups</span>
        </div>
        <?php if ($suggested_query->num_rows > 0): ?>
            <?php while ($suggested = $suggested_query->fetch_assoc()): ?>
                <div class="friend-item animate__animated animate__fadeIn">
                    <img src="<?php echo htmlspecialchars($suggested['profile_pic'] ?: 'media/default.jpg'); ?>" class="profile-pic">
                    <div class="friend-info">
                        <div class="friend-name">
                            <?php echo htmlspecialchars($suggested['username']); ?>
                        </div>
                    </div>
                    <form method="POST" class="friend-actions">
                        <input type="hidden" name="friend_id" value="<?php echo $suggested['id']; ?>">
                        <button type="submit" name="send_request" class="btn-add">
                            <i class="fas fa-user-plus me-1"></i> Add
                        </button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-user-friends"></i>
                <p>No suggestions available right now</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="section-card animate__animated animate__fadeInUp">
        <div class="tabs">
            <div class="tab active" onclick="showTab('requests')">
                <i class="fas fa-bell me-2"></i> Friend Requests
            </div>
            <div class="tab" onclick="showTab('friends')">
                <i class="fas fa-heart me-2"></i> My Friends
            </div>
        </div>
        
        <div id="requests-tab">
            <?php if ($requests_query->num_rows > 0): ?>
                <?php while ($request = $requests_query->fetch_assoc()): ?>
                    <div class="friend-item animate__animated animate__fadeIn">
                        <img src="<?php echo htmlspecialchars($request['profile_pic'] ?: 'media/default.jpg'); ?>" class="profile-pic">
                        <div class="friend-info">
                            <div class="friend-name">
                                <?php echo htmlspecialchars($request['username']); ?>
                            </div>
                            <small class="text-muted">Sent you a friend request</small>
                        </div>
                        <div class="friend-actions">
                            <a href="friends.php?accept=<?php echo $request['id']; ?>" class="btn-accept">
                                <i class="fas fa-check me-1"></i> Accept
                            </a>
                            <a href="friends.php?decline=<?php echo $request['id']; ?>" class="btn-decline">
                                <i class="fas fa-times me-1"></i> Decline
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>No pending friend requests</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="friends-tab" style="display: none;">
            <?php if ($friends_query->num_rows > 0): ?>
                <?php while ($friend = $friends_query->fetch_assoc()): ?>
                    <div class="friend-item animate__animated animate__fadeIn">
                        <img src="<?php echo htmlspecialchars($friend['profile_pic'] ?: 'media/default.jpg'); ?>" class="profile-pic">
                        <div class="friend-info">
                            <div class="friend-name">
                                <?php echo htmlspecialchars($friend['username']); ?>
                                <?php if ($friend['verified'] == 1): ?>
                                    <img src="media/veri.png" alt="Verified" class="verified-badge">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="friend-actions">
                            <a href="profile.php?user_id=<?php echo $friend['id']; ?>" class="btn-view">
                                <i class="fas fa-user me-1"></i> Profile
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-friends"></i>
                    <p>You haven't added any friends yet</p>
                    <p class="mt-2">Search for pups to connect with!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="footer">
    © 2025 PupChat - Connecting pups around the world
</div>

<script>
    // Show flash message animation
    const flashMessage = document.querySelector('.flash-message');
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.classList.add('animate__animated', 'animate__fadeOutUp');
            setTimeout(() => flashMessage.remove(), 500);
        }, 2500);
    }
    
    // Tab switching
    function showTab(tabName) {
        document.querySelectorAll('.tab').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll(`.tab`).forEach(tab => {
            if (tab.textContent.includes(tabName === 'requests' ? 'Requests' : 'Friends')) {
                tab.classList.add('active');
            }
        });
        
        document.getElementById('requests-tab').style.display = tabName === 'requests' ? 'block' : 'none';
        document.getElementById('friends-tab').style.display = tabName === 'friends' ? 'block' : 'none';
    }
    
    // Add animation to buttons
    document.querySelectorAll('.btn-accept, .btn-decline, .btn-view, .btn-add').forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        });
    });
    
    // Add shake animation to empty states when clicked (for fun)
    document.querySelectorAll('.empty-state').forEach(emptyState => {
        emptyState.addEventListener('click', function() {
            this.classList.add('animate__animated', 'animate__shakeX');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);
        });
    });
    
    // Animate friend items when they come into view
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeIn');
                observer.unobserve(entry.target);
            }
        });
    }, {threshold: 0.1});
    
    document.querySelectorAll('.friend-item').forEach(item => {
        observer.observe(item);
    });
</script>

</body>
</html>