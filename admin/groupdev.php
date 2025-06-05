<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db_connect.php";


// Check if group_id is provided
if (!isset($_GET['group_id'])) {
    die("No group selected.");
}

$group_id = $_GET['group_id'];

// Fetch group details
$group_query = $conn->prepare("SELECT * FROM groups WHERE id = ?");
$group_query->bind_param("i", $group_id);
$group_query->execute();
$group = $group_query->get_result()->fetch_assoc();

if (!$group) {
    die("Group not found.");
}

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Change group name
    if ($action === 'change_name' && isset($_POST['new_group_name'])) {
        $new_name = $_POST['new_group_name'];
        $stmt = $conn->prepare("UPDATE groups SET group_name = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $group_id);
        $stmt->execute();
    }

    // Change group password (only if password exists)
    if ($action === 'change_password' && isset($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        if (!empty($group['encrypted_password'])) { // If group has a password
            $stmt = $conn->prepare("UPDATE groups SET encrypted_password = ? WHERE id = ?");
            $stmt->bind_param("si", $new_password, $group_id);
            $stmt->execute();
        }
    }

    // Add user to group
    if ($action === 'add_user' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
    }

    // Remove user from group
    if ($action === 'remove_user' && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $stmt = $conn->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $group_id, $user_id);
        $stmt->execute();
    }

    // Delete group
    if ($action === 'delete_group') {
        $stmt = $conn->prepare("DELETE FROM groups WHERE id = ?");
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        header("Location: encrypt_groups.php");
        exit;
    }

    // Verify or unverify group
    if ($action === 'toggle_verification') {
        $new_status = $group['is_verified'] ? 0 : 1;
        $stmt = $conn->prepare("UPDATE groups SET is_verified = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_status, $group_id);
        $stmt->execute();
    }

    // Refresh group details after any change
    header("Location: groupdev.php?group_id=$group_id");
    exit;
}

// Fetch group members
$members_query = $conn->prepare("SELECT users.id, users.username FROM users 
    JOIN group_members ON users.id = group_members.user_id 
    WHERE group_members.group_id = ?");
$members_query->bind_param("i", $group_id);
$members_query->execute();
$members = $members_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Group - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Manage Group: <?php echo htmlspecialchars($group['group_name']); ?> 
            <?php if ($group['is_verified']) { ?>
                <img src="media/veri.png" alt="Verified" width="20">
            <?php } ?>
        </h2>

        <!-- Change Group Name -->
        <form method="POST">
            <input type="hidden" name="action" value="change_name">
            <input type="text" name="new_group_name" placeholder="New Group Name" required>
            <button type="submit" class="btn btn-warning">Update Name</button>
        </form>

        <!-- Change Group Password (Only if password exists) -->
        <?php if (!empty($group['encrypted_password'])) { ?>
            <form method="POST" class="mt-3">
                <input type="hidden" name="action" value="change_password">
                <input type="password" name="new_password" placeholder="New Group Password" required>
                <button type="submit" class="btn btn-info">Change Password</button>
            </form>
        <?php } ?>

        <!-- Add User to Group -->
        <form method="POST" class="mt-3">
            <input type="hidden" name="action" value="add_user">
            <input type="text" name="user_id" placeholder="User ID" required>
            <button type="submit" class="btn btn-success">Add User</button>
        </form>

        <!-- Group Members -->
        <h3 class="mt-4">Group Members</h3>
        <?php while ($member = $members->fetch_assoc()) { ?>
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <strong><?php echo htmlspecialchars($member['username']); ?></strong>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="action" value="remove_user">
                    <input type="hidden" name="user_id" value="<?php echo $member['id']; ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                </form>
            </div>
        <?php } ?>

        <!-- Verify/Unverify Group -->
        <form method="POST" class="mt-3">
            <input type="hidden" name="action" value="toggle_verification">
            <button type="submit" class="btn btn-<?php echo $group['is_verified'] ? 'secondary' : 'primary'; ?>">
                <?php echo $group['is_verified'] ? 'Unverify Group' : 'Verify Group'; ?>
            </button>
        </form>

        <!-- Delete Group -->
        <form method="POST" class="mt-4">
            <input type="hidden" name="action" value="delete_group">
            <button type="submit" class="btn btn-danger">Delete Group</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>