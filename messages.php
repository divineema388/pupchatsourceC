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

// Fetch unique chat heads (people you've chatted with)
$chat_heads_query = $conn->query("
    SELECT DISTINCT 
        CASE 
            WHEN sender_id = '$user_id' THEN receiver_id
            ELSE sender_id 
        END AS chat_partner_id
    FROM messages
    WHERE sender_id = '$user_id' OR receiver_id = '$user_id'
");

// Fetch groups the user is in
$groups_query = $conn->query("
    SELECT g.id, g.group_name 
    FROM group_members gm
    JOIN groups g ON gm.group_id = g.id
    WHERE gm.user_id = '$user_id'
");

// Fetch all users for starting a new message
$users_query = $conn->query("SELECT id, username FROM users WHERE id != '$user_id'");

// Fetch usernames for chat partners
$chat_partners = [];
while ($chat = $chat_heads_query->fetch_assoc()) {
    $partner_id = $chat['chat_partner_id'];
    $partner_query = $conn->query("SELECT username FROM users WHERE id = '$partner_id'");
    $partner = $partner_query->fetch_assoc();
    if ($partner) {
        $chat_partners[] = [
            'id' => $partner_id,
            'username' => $partner['username']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Messages</title>
    <!-- External CSS Libraries -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --accent: #fd79a8;
            --light: #f8f9fa;
            --dark: #2d3436;
            --success: #00b894;
            --info: #0984e3;
            --bubble-size: min(10vw, 50px);
        }
        
        body { 
            background: linear-gradient(135deg, var(--light) 0%, #dfe6e9 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        .container { 
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            padding: 0;
            background: white; 
            border-radius: 0;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }
        
        @media (min-width: 768px) {
            .container {
                margin: 20px auto;
                border-radius: 20px;
                min-height: auto;
                max-height: 90vh;
                animation: float 6s ease-in-out infinite;
            }
            
            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-10px); }
            }
        }
        
        .header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 15px 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 15s linear infinite;
        }
        
        @keyframes pulse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .header h2 {
            font-weight: 700;
            margin: 0;
            position: relative;
            text-shadow: 0 2px 5px rgba(0,0,0,0.2);
            font-size: 1.5rem;
        }
        
        .header i {
            animation: bounce 2s infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-5px); }
        }
        
        .nav-tabs {
            border-bottom: none;
            padding: 0 10px;
            display: flex;
            justify-content: space-around;
            background: white;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: var(--dark);
            font-weight: 600;
            padding: 12px 0;
            margin: 0 5px;
            position: relative;
            transition: all 0.3s;
            font-size: 0.9rem;
            background: transparent;
            flex: 1;
            text-align: center;
        }
        
        .nav-tabs .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--accent);
            transition: all 0.3s;
            transform: translateX(-50%);
        }
        
        .nav-tabs .nav-link:hover::after,
        .nav-tabs .nav-link.active::after {
            width: 100%;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary);
        }
        
        .tab-content {
            padding: 15px;
            padding-bottom: 70px; /* Space for mobile bottom nav */
        }
        
        .chat-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .chat-head {
            display: flex;
            align-items: center;
            padding: 15px;
            background: white;
            color: var(--dark);
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            border-left: 4px solid var(--primary);
            animation: fadeInUp 0.5s ease-out, subtleGlow 4s infinite ease-in-out;
            position: relative;
            overflow: hidden;
            will-change: transform;
        }
        
        .chat-head::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(108, 92, 231, 0.1), transparent);
            transition: all 0.6s;
        }
        
        .chat-head:hover::before,
        .chat-head:focus::before {
            left: 100%;
        }
        
        .chat-head:active {
            transform: scale(0.98);
        }
        
        .chat-head i {
            margin-right: 12px;
            font-size: 1.2rem;
            color: var(--primary);
            transition: all 0.3s;
            min-width: 24px;
            text-align: center;
        }
        
        .chat-head:hover i,
        .chat-head:focus i {
            transform: scale(1.2);
            color: var(--accent);
        }
        
        .chat-head .badge {
            margin-left: auto;
            background: var(--accent);
            animation: pulse 1.5s infinite;
        }
        
        .no-chats {
            text-align: center;
            padding: 40px 20px;
            color: var(--secondary);
            animation: pulseText 2s infinite;
        }
        
        @keyframes pulseText {
            0%, 100% { opacity: 0.7; }
            50% { opacity: 1; }
        }
        
        h3 {
            color: var(--primary);
            margin: 15px 0 20px;
            position: relative;
            display: inline-block;
            font-size: 1.3rem;
        }
        
        h3::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 3px;
            background: var(--accent);
            border-radius: 3px;
        }
        
        /* Continuous animations */
        @keyframes subtleGlow {
            0%, 100% { box-shadow: 0 2px 8px rgba(108, 92, 231, 0.1); }
            50% { box-shadow: 0 2px 12px rgba(108, 92, 231, 0.2); }
        }
        
        /* Floating bubbles */
        .bubble {
            position: absolute;
            background: rgba(108, 92, 231, 0.1);
            border-radius: 50%;
            animation: floatBubble 15s infinite linear;
            z-index: -1;
            width: var(--bubble-size);
            height: var(--bubble-size);
        }
        
        @keyframes floatBubble {
            0% { transform: translateY(100vh) translateX(0); opacity: 0; }
            10% { opacity: 0.2; }
            90% { opacity: 0.2; }
            100% { transform: translateY(-100px) translateX(50px); opacity: 0; }
        }
        
        /* Mobile bottom nav */
        .mobile-nav {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 10px 0;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .mobile-nav a {
            display: flex;
            flex-direction: column;
            align-items: center;
            color: var(--dark);
            text-decoration: none;
            font-size: 0.8rem;
            transition: all 0.3s;
            padding: 5px 10px;
            border-radius: 10px;
        }
        
        .mobile-nav a i {
            font-size: 1.2rem;
            margin-bottom: 3px;
            transition: all 0.3s;
        }
        
        .mobile-nav a.active,
        .mobile-nav a:hover {
            color: var(--primary);
        }
        
        .mobile-nav a.active i,
        .mobile-nav a:hover i {
            transform: translateY(-3px);
            color: var(--accent);
        }
        
        /* Ripple effect for buttons */
        .ripple {
            position: relative;
            overflow: hidden;
        }
        
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
        
        /* Typing indicator animation */
        .typing-indicator {
            display: inline-flex;
            align-items: center;
            margin-left: auto;
        }
        
        .typing-dot {
            width: 6px;
            height: 6px;
            background: var(--accent);
            border-radius: 50%;
            margin: 0 2px;
            animation: typingAnimation 1.4s infinite ease-in-out;
        }
        
        .typing-dot:nth-child(1) { animation-delay: 0s; }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        
        @keyframes typingAnimation {
            0%, 60%, 100% { transform: translateY(0); }
            30% { transform: translateY(-5px); }
        }
        
        /* Message preview animation */
        .message-preview {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 70%;
            color: #666;
            font-size: 0.9rem;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(10px); }
            to { opacity: 1; transform: translateX(0); }
        }
    </style>
</head>
<body>
    <!-- Background bubbles -->
    <div class="bubble" style="left: 10%; animation-delay: 0s;"></div>
    <div class="bubble" style="left: 30%; animation-delay: 2s;"></div>
    <div class="bubble" style="left: 70%; animation-delay: 4s;"></div>
    <div class="bubble" style="left: 85%; animation-delay: 6s;"></div>
    
    <div class="container animate__animated animate__fadeIn">
        <div class="header">
            <h2><i class="fas fa-comment-dots"></i> PupChat</h2>
        </div>

        <div class="new-message">
            <!-- Bootstrap Tabs -->
            <ul class="nav nav-tabs" id="messageTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="chats-tab" data-bs-toggle="tab" data-bs-target="#chats" type="button" role="tab">
                        <i class="fas fa-comment-alt me-1"></i> Chats
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="groups-tab" data-bs-toggle="tab" data-bs-target="#groups" type="button" role="tab">
                        <i class="fas fa-users me-1"></i> Groups
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="messageTabsContent">
                <!-- Chats Tab -->
                <div class="tab-pane fade show active" id="chats" role="tabpanel">
                    <h3 class="animate__animated animate__fadeIn">Recent Chats</h3>
                    <div class="chat-list">
                        <?php foreach ($chat_partners as $chat) { ?>
                            <a href="adoe.php?user_id=<?php echo $chat['id']; ?>" class="chat-head ripple animate__animated animate__fadeInUp">
                                <i class="fas fa-user-circle"></i>
                                <div style="flex: 1;">
                                    <div><?php echo htmlspecialchars($chat['username']); ?></div>
                                    <div class="message-preview">Last message preview...</div>
                                </div>
                                <div class="typing-indicator">
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                    <div class="typing-dot"></div>
                                </div>
                                <span class="badge rounded-pill">3</span>
                            </a>
                        <?php } ?>
                        <?php if (empty($chat_partners)) { ?>
                            <div class="no-chats animate__animated animate__fadeIn">
                                <i class="fas fa-comment-slash fa-3x mb-3"></i>
                                <p>No conversations yet</p>
                                <button class="btn btn-primary mt-2 ripple">Start Chatting</button>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <!-- Groups Tab -->
                <div class="tab-pane fade" id="groups" role="tabpanel">
                    <h3 class="animate__animated animate__fadeIn">Your Groups</h3>
                    <div class="chat-list">
                        <?php
                        // Re-run the query because previous result set was consumed
                        $groups_query = $conn->query("
                            SELECT g.id, g.group_name 
                            FROM group_members gm
                            JOIN groups g ON gm.group_id = g.id
                            WHERE gm.user_id = '$user_id'
                        ");
                        while ($group = $groups_query->fetch_assoc()) { ?>
                            <a href="join_group.php?group_id=<?php echo $group['id']; ?>" class="chat-head ripple animate__animated animate__fadeInUp">
                                <i class="fas fa-users"></i>
                                <div style="flex: 1;">
                                    <div><?php echo htmlspecialchars($group['group_name']); ?></div>
                                    <div class="message-preview">5 new messages</div>
                                </div>
                                <span class="badge rounded-pill">5</span>
                            </a>
                        <?php }
                        if ($groups_query->num_rows == 0) { ?>
                            <div class="no-chats animate__animated animate__fadeIn">
                                <i class="fas fa-users-slash fa-3x mb-3"></i>
                                <p>You're not in any groups</p>
                                <button class="btn btn-primary mt-2 ripple">Join a Group</button>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Bottom Navigation -->
        <div class="mobile-nav d-lg-none">
            <a href="#" class="active">
                <i class="fas fa-comment-dots"></i>
                <span>Chats</span>
            </a>
            <a href="#">
                <i class="fas fa-users"></i>
                <span>Groups</span>
            </a>
            <a href="#">
                <i class="fas fa-search"></i>
                <span>Discover</span>
            </a>
            <a href="#">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>

    <!-- External JS Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Enhanced mobile animations
        document.addEventListener('DOMContentLoaded', function() {
            // Create more bubbles dynamically
            function createBubbles() {
                const colors = ['rgba(108, 92, 231, 0.1)', 'rgba(253, 121, 168, 0.1)', 'rgba(0, 184, 148, 0.1)'];
                for (let i = 0; i < 8; i++) {
                    const bubble = document.createElement('div');
                    bubble.className = 'bubble';
                    const size = Math.random() * 80 + 30;
                    bubble.style.width = `${size}px`;
                    bubble.style.height = `${size}px`;
                    bubble.style.left = `${Math.random() * 100}%`;
                    bubble.style.animationDelay = `${Math.random() * 8}s`;
                    bubble.style.animationDuration = `${10 + Math.random() * 20}s`;
                    bubble.style.background = colors[Math.floor(Math.random() * colors.length)];
                    document.body.appendChild(bubble);
                }
            }
            
            createBubbles();
            
            // Ripple effect for buttons
            document.querySelectorAll('.ripple').forEach(button => {
                button.addEventListener('click', function(e) {
                    const rect = this.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const ripple = document.createElement('span');
                    ripple.className = 'ripple-effect';
                    ripple.style.left = `${x}px`;
                    ripple.style.top = `${y}px`;
                    
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Mobile nav animation
            const mobileNavLinks = document.querySelectorAll('.mobile-nav a');
            mobileNavLinks.forEach(link => {
                link.addEventListener('click', function() {
                    mobileNavLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Simulate typing indicators
            setInterval(() => {
                const indicators = document.querySelectorAll('.typing-indicator');
                indicators.forEach(indicator => {
                    if (Math.random() > 0.7) {
                        indicator.style.display = 'inline-flex';
                        setTimeout(() => {
                            indicator.style.display = 'none';
                        }, 2000 + Math.random() * 3000);
                    }
                });
            }, 8000);
            
            // Animate message previews
            const messagePreviews = document.querySelectorAll('.message-preview');
            messagePreviews.forEach(preview => {
                preview.style.animationDelay = `${Math.random() * 0.5}s`;
            });
            
            // Add touch feedback for mobile
            document.querySelectorAll('.chat-head').forEach(head => {
                head.addEventListener('touchstart', function() {
                    this.classList.add('animate__pulse');
                });
                head.addEventListener('touchend', function() {
                    setTimeout(() => {
                        this.classList.remove('animate__pulse');
                    }, 300);
                });
            });
        });
    </script>
</body>
</html>