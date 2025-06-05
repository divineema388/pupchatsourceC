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

// Handle group creation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["create_group"])) {
    $group_name = trim($_POST["group_name"]);
    $group_password = trim($_POST["group_password"]);

    if (!empty($group_name)) {
        $hashed_password = !empty($group_password) ? password_hash($group_password, PASSWORD_DEFAULT) : null;
        $is_encrypted = !empty($group_password) ? 1 : 0;

        $stmt = $conn->prepare("INSERT INTO groups (group_name, owner_id, created_at, is_encrypted, encrypted_password) VALUES (?, ?, NOW(), ?, ?)");
        $stmt->bind_param("siis", $group_name, $user_id, $is_encrypted, $hashed_password);
        $stmt->execute();
        header("Location: group.php");
        exit();
    }
}

// Fetch user's groups
$my_groups_query = $conn->prepare("
    SELECT g.id, g.group_name, g.is_verified, g.is_encrypted 
    FROM groups g 
    JOIN group_members gm ON g.id = gm.group_id 
    WHERE gm.user_id = ?
");
$my_groups_query->bind_param("i", $user_id);
$my_groups_query->execute();
$my_groups = $my_groups_query->get_result();

// Fetch all groups user is NOT in
$all_groups_query = $conn->prepare("
    SELECT id, group_name, is_verified, is_encrypted 
    FROM groups 
    WHERE id NOT IN (SELECT group_id FROM group_members WHERE user_id = ?)
");
$all_groups_query->bind_param("i", $user_id);
$all_groups_query->execute();
$all_groups = $all_groups_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Groups</title>

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
        
        .main-container {
            max-width: 800px;
            margin: 20px auto;
            padding-bottom: 100px;
        }
        
        .group-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 24px;
            border: none;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .group-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            padding: 16px;
            background: linear-gradient(135deg, var(--primary) 0%, #7b74ff 100%);
            color: white;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header i {
            font-size: 1.2em;
        }
        
        .group-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .group-item:hover {
            background-color: rgba(108, 99, 255, 0.05);
        }
        
        .group-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .group-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            font-size: 1.2em;
        }
        
        .group-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0;
        }
        
        .verified-badge {
            width: 16px;
            height: 16px;
            margin-left: 4px;
        }
        
        .group-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-primary:hover {
            background: #5a52e0;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: var(--success);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background: var(--danger);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        .btn-warning {
            background: var(--warning);
            border: none;
            border-radius: 50px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .btn-warning:hover {
            background: #e0a800;
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
        
        .form-control {
            border-radius: 50px;
            padding: 12px 16px;
            border: 1px solid rgba(0,0,0,0.1);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
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
        
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
        
        .paw-print {
            position: absolute;
            opacity: 0.1;
            z-index: -1;
        }
        
        .paw-1 {
            top: 10%;
            left: 5%;
            transform: rotate(20deg);
            font-size: 60px;
            color: var(--primary);
        }
        
        .paw-2 {
            bottom: 15%;
            right: 5%;
            transform: rotate(-10deg);
            font-size: 80px;
            color: var(--secondary);
        }
    </style>
</head>
<body>

<!-- Decorative paw prints -->
<i class="fas fa-paw paw-print paw-1 floating"></i>
<i class="fas fa-paw paw-print paw-2 floating"></i>

<div class="main-container animate__animated animate__fadeIn">
    <h1 class="text-center mb-4 animate__animated animate__fadeInDown">
        <i class="fas fa-users" style="color: var(--primary);"></i> PupChat Groups
    </h1>

    <!-- My Groups Section -->
    <div class="group-card animate__animated animate__fadeInLeft">
        <div class="card-header">
            <i class="fas fa-user-friends"></i> My Groups
        </div>
        <div class="card-body p-0">
            <?php if ($my_groups->num_rows > 0): ?>
                <?php while ($group = $my_groups->fetch_assoc()): ?>
                    <div class="group-item animate__animated animate__fadeIn">
                        <div class="group-info">
                            <div class="group-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="group-name">
                                <?php echo htmlspecialchars($group["group_name"]); ?>
                                <?php if (!empty($group['is_verified']) && $group['is_verified'] == 1): ?>
                                    <img src="media/veri.png" alt="Verified" class="verified-badge">
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="group-actions">
                            <a href="join_group.php?group_id=<?php echo $group["id"]; ?>" 
                               class="btn btn-success">
                                <i class="fas fa-door-open"></i> Enter
                            </a>
                            <button class="btn btn-danger leave-btn" data-group-id="<?php echo $group["id"]; ?>">
                                <i class="fas fa-sign-out-alt"></i> Leave
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state animate__animated animate__fadeIn">
                    <i class="fas fa-user-friends"></i>
                    <h5>No Groups Yet</h5>
                    <p>You're not in any groups yet. Create or join one below!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Create Group Section -->
    <div class="group-card animate__animated animate__fadeInRight">
        <div class="card-header" style="background: linear-gradient(135deg, var(--secondary) 0%, #ff7b8b 100%);">
            <i class="fas fa-plus-circle"></i> Create New Group
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <div class="mb-3">
                    <input type="text" name="group_name" class="form-control" placeholder="Group Name" required>
                </div>
                <div class="mb-3">
                    <input type="password" name="group_password" class="form-control" placeholder="Password (optional)">
                    <small class="text-muted">Leave blank for public group</small>
                </div>
                <button type="submit" name="create_group" class="btn btn-primary w-100">
                    <i class="fas fa-check-circle"></i> Create Group
                </button>
            </form>
        </div>
    </div>

    <!-- Search Group Section -->
    <div class="group-card animate__animated animate__fadeInUp">
        <div class="card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #5bc0de 100%);">
            <i class="fas fa-search"></i> Search Groups
        </div>
        <div class="card-body">
            <form action="" method="POST" id="search-form">
                <div class="input-group mb-3">
                    <input type="text" name="search_query" class="form-control" placeholder="Search for groups..." required>
                    <button type="submit" name="search_group" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            <div id="search-results" class="mt-3">
                <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["search_group"])): ?>
                    <?php
                    $search_query = trim($_POST["search_query"]);
                    $search_query_stmt = $conn->prepare("
                        SELECT id, group_name, is_verified 
                        FROM groups 
                        WHERE group_name LIKE ? 
                    ");
                    $like_query = "%" . $search_query . "%";
                    $search_query_stmt->bind_param("s", $like_query);
                    $search_query_stmt->execute();
                    $search_results = $search_query_stmt->get_result();
                    ?>
                    
                    <?php if ($search_results->num_rows > 0): ?>
                        <h5 class="mb-3">Search Results:</h5>
                        <?php while ($row = $search_results->fetch_assoc()): ?>
                            <div class="group-item animate__animated animate__fadeIn">
                                <div class="group-info">
                                    <div class="group-icon">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h5 class="group-name">
                                        <?php echo htmlspecialchars($row["group_name"]); ?>
                                        <?php if (!empty($row['is_verified']) && $row['is_verified'] == 1): ?>
                                            <img src="media/veri.png" alt="Verified" class="verified-badge">
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <div class="group-actions">
                                    <a href="join_group.php?group_id=<?php echo $row["id"]; ?>" 
                                       class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt"></i> Join
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="empty-state animate__animated animate__fadeIn">
                            <i class="fas fa-search"></i>
                            <h5>No Groups Found</h5>
                            <p>No groups match your search for "<?php echo htmlspecialchars($search_query); ?>"</p>
                        </div>
                    <?php endif; ?>
                    <?php $search_query_stmt->close(); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Join Group Section -->
    <div class="group-card animate__animated animate__fadeInUp">
        <div class="card-header" style="background: linear-gradient(135deg, #ffc107 0%, #ffd54f 100%);">
            <i class="fas fa-user-plus"></i> Join a Group
        </div>
        <div class="card-body p-0">
            <?php if ($all_groups->num_rows > 0): ?>
                <?php while ($group = $all_groups->fetch_assoc()): ?>
                    <div class="group-item animate__animated animate__fadeIn">
                        <div class="group-info">
                            <div class="group-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h5 class="group-name">
                                <?php echo htmlspecialchars($group["group_name"]); ?>
                                <?php if (!empty($group['is_verified']) && $group['is_verified'] == 1): ?>
                                    <img src="media/veri.png" alt="Verified" class="verified-badge">
                                <?php endif; ?>
                            </h5>
                        </div>
                        <div class="group-actions">
                            <?php if ($group["is_encrypted"]): ?>
                                <button class="btn btn-primary join-btn" 
                                        data-group-id="<?php echo $group["id"]; ?>" 
                                        data-group-name="<?php echo htmlspecialchars($group["group_name"]); ?>">
                                    <i class="fas fa-lock"></i> Join
                                </button>
                            <?php else: ?>
                                <a href="join_group.php?group_id=<?php echo $group["id"]; ?>" 
                                   class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Join
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="empty-state animate__animated animate__fadeIn">
                    <i class="fas fa-user-friends"></i>
                    <h5>No Groups Available</h5>
                    <p>All groups have been joined or no groups exist yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Join Group Modal -->
<div class="modal fade" id="joinGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="join_group.php">
                <div class="modal-header">
                    <h5 class="modal-title">Join Group: <span id="modalGroupName"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="group_id" id="modalGroupId">
                    <div class="mb-3">
                        <label for="groupPassword" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" id="groupPassword" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="join_group" class="btn btn-primary">Join Group</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Leave group confirmation
    document.querySelectorAll(".leave-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            let groupId = this.getAttribute("data-group-id");
            if (confirm("Are you sure you want to leave this group?")) {
                window.location.href = "leave.php?group_id=" + groupId;
            }
        });
    });

    // Join encrypted group modal
    document.querySelectorAll(".join-btn").forEach(btn => {
        btn.addEventListener("click", function() {
            let groupId = this.getAttribute("data-group-id");
            let groupName = this.getAttribute("data-group-name");
            document.getElementById("modalGroupId").value = groupId;
            document.getElementById("modalGroupName").textContent = groupName;
            
            let joinModal = new bootstrap.Modal(document.getElementById('joinGroupModal'));
            joinModal.show();
        });
    });

    // Add animation to buttons
    document.querySelectorAll(".btn").forEach(btn => {
        btn.addEventListener("click", function() {
            this.classList.add("animate__animated", "animate__pulse");
            setTimeout(() => {
                this.classList.remove("animate__animated", "animate__pulse");
            }, 1000);
        });
    });
</script>

</body>
</html>