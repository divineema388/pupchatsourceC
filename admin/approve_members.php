<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db_connect.php";

// Fetch all users
$users_query = $conn->query("SELECT id, username, email, status FROM users");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action == "delete") {
        // Soft delete user (mark as deleted)
        $stmt = $conn->prepare("UPDATE users SET status = 'deleted', deleted_at = NOW() WHERE id = ?");
    } elseif ($action == "restore") {
        // Restore deleted user
        $stmt = $conn->prepare("UPDATE users SET status = 'active', deleted_at = NULL WHERE id = ?");
    }

    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Refresh page to update list
    header("Location: approve_members.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
</head>
<body>
    <h2>Manage Users</h2>
    
    <h3>All Users</h3>
    <ul>
        <?php while ($user = $users_query->fetch_assoc()) { ?>
            <li>
                <?php echo htmlspecialchars($user['username']) . " (" . htmlspecialchars($user['email']) . ") - Status: " . $user['status']; ?>
                <form method="POST" action="approve_members.php" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <?php if ($user['status'] == 'active') { ?>
                        <button type="submit" name="action" value="delete">Delete</button>
                    <?php } else { ?>
                        <button type="submit" name="action" value="restore">Restore</button>
                    <?php } ?>
                </form>
            </li>
        <?php } ?>
    </ul>
</body>
</html>