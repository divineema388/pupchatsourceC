<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "db_connect.php";


if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Fetch user data for navbar
$user_id = $_SESSION["user_id"];
$user_data = $conn->query("SELECT username, profile_pic FROM users WHERE id = '$user_id'")->fetch_assoc();

$search_results = [];
$search_query = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $search = trim($_POST["search"]);
    $search_query = htmlspecialchars($search);
    
    if (!empty($search)) {
       $query = "SELECT id, username, profile_pic, 'user' AS type FROM users WHERE username LIKE ? AND hide_profile = 0
          UNION 
          SELECT id, bot_name AS username, bot_avatar AS profile_pic, 'bot' AS type FROM bots WHERE bot_name LIKE ?";
        $stmt = $conn->prepare($query);
        $search_param = "%$search%";
        $stmt->bind_param("ss", $search_param, $search_param);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $search_results[] = $row;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Search</title>

    <!-- External CSS & JS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

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
        
        .search-container {
            max-width: 800px;
            margin: 20px auto;
            padding-bottom: 80px;
        }
        
        .search-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            border: none;
            overflow: hidden;
        }
        
        .search-header {
            padding: 24px;
            background: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            position: relative;
        }
        
        .search-input-container {
            position: relative;
        }
        
        .search-input {
            width: 100%;
            padding: 16px 24px 16px 50px;
            border-radius: 50px;
            border: 2px solid var(--light-gray);
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .search-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
        }
        
        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray);
        }
        
        .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .search-btn:hover {
            background: #5a52e0;
            transform: translateY(-50%) scale(1.05);
        }
        
        .result-item {
            display: flex;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease-out;
        }
        
        .result-item:hover {
            background-color: rgba(108, 99, 255, 0.05);
            transform: translateX(5px);
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
            margin-left: 16px;
            flex-grow: 1;
        }
        
        .username {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0;
        }
        
        .user-type {
            font-size: 12px;
            color: var(--gray);
            margin-top: 4px;
        }
        
        .action-btn {
            border-radius: 50px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #5a52e0;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: white;
            border: none;
        }
        
        .btn-secondary:hover {
            background: #e64c6c;
            color: white;
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
        
        .search-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .search-title i {
            font-size: 28px;
        }
        
        .results-count {
            color: var(--gray);
            font-size: 14px;
            margin-bottom: 16px;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .bounce-in {
            animation: bounceIn 0.5s;
        }
        
        @keyframes bounceIn {
            0% { transform: scale(0.9); opacity: 0; }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); opacity: 1; }
        }
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="feed.php">
            <i class="fas fa-paw"></i> PupChat
        </a>
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($user_data['profile_pic'] ?? 'media/default-profile.png'); ?>" alt="Profile" width="32" height="32" class="rounded-circle me-2">
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="friends.php"><i class="fas fa-users me-2"></i>Friends</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Sign out</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<div class="search-container animate__animated animate__fadeIn">
    <div class="search-card animate__animated animate__fadeInUp">
        <div class="search-header">
            <h2 class="search-title">
                <i class="fas fa-search floating" style="color: var(--secondary);"></i> 
                Find Pups & Bots
            </h2>
            
            <form action="" method="POST">
                <div class="search-input-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" name="search" placeholder="Search for users or bots..." 
                           class="search-input" value="<?php echo $search_query; ?>" required>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-paper-plane me-1"></i> Search
                    </button>
                </div>
            </form>
        </div>
        
        <?php if ($_SERVER["REQUEST_METHOD"] == "POST"): ?>
            <?php if (!empty($search_results)): ?>
                <div class="px-4 pt-2">
                    <p class="results-count">
                        Found <?php echo count($search_results); ?> result<?php echo count($search_results) !== 1 ? 's' : ''; ?> for "<?php echo $search_query; ?>"
                    </p>
                </div>
                
                <?php foreach ($search_results as $result): ?>
                    <div class="result-item animate__animated animate__fadeInUp">
                        <img src="<?php echo htmlspecialchars($result['profile_pic'] ?: ($result['type'] === 'user' ? 'media/default-profile.png' : 'media/bot-avatar.png')); ?>" 
                             class="profile-pic" 
                             alt="<?php echo htmlspecialchars($result['username']); ?>">
                        
                        <div class="user-info">
                            <h5 class="username">
                                <?php echo htmlspecialchars($result['username']); ?>
                                <?php if ($result['type'] === 'bot'): ?>
                                    <span class="badge bg-secondary ms-2">Bot</span>
                                <?php endif; ?>
                            </h5>
                            <p class="user-type">
                                <?php echo $result['type'] === 'user' ? 'PupChat User' : 'AI Companion'; ?>
                            </p>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <?php if ($result["type"] === "user"): ?>
                                <a href="profile.php?user_id=<?php echo htmlspecialchars($result['id']); ?>" 
                                   class="action-btn btn-primary">
                                    <i class="fas fa-user"></i> Profile
                                </a>
                            <?php endif; ?>
                            
                            <a href="send_message.php?<?php echo $result['type'] === 'user' ? 'receiver_id' : 'bot_id'; ?>=<?php echo htmlspecialchars($result['id']); ?>" 
                               class="action-btn btn-secondary">
                                <i class="fas fa-<?php echo $result['type'] === 'user' ? 'comment' : 'robot'; ?>"></i> 
                                Message
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state animate__animated animate__bounceIn">
                    <i class="fas fa-search fa-2x"></i>
                    <h5>No results found</h5>
                    <p class="text-muted">We couldn't find any matches for "<?php echo $search_query; ?>"</p>
                    <button onclick="history.back()" class="action-btn btn-primary mt-3">
                        <i class="fas fa-arrow-left"></i> Try again
                    </button>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="empty-state animate__animated animate__fadeIn">
                <i class="fas fa-search fa-2x floating" style="color: var(--primary);"></i>
                <h5>Search PupChat</h5>
                <p class="text-muted">Find friends, bots, and more</p>
                <div class="mt-4">
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-primary p-2"><i class="fas fa-users me-1"></i> Friends</span>
                        <span class="badge bg-secondary p-2"><i class="fas fa-robot me-1"></i> Bots</span>
                        <span class="badge bg-success p-2"><i class="fas fa-comments me-1"></i> Messages</span>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    // Add animation to search button
    const searchBtn = document.querySelector('.search-btn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            this.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                this.classList.remove('animate__animated', 'animate__pulse');
            }, 1000);
        });
    }
    
    // Add hover animation to result items
    document.querySelectorAll('.result-item').forEach(item => {
        item.addEventListener('mouseenter', () => {
            item.classList.add('animate__animated', 'animate__pulse');
        });
        item.addEventListener('mouseleave', () => {
            item.classList.remove('animate__animated', 'animate__pulse');
        });
    });
    
    // Focus search input on page load
    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.querySelector('.search-input');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }
    });
</script>

</body>
</html>