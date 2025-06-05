<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db_connect.php";

// Handle removing a user from suspension
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["unsuspend_id"])) {
    $unsuspend_id = intval($_POST["unsuspend_id"]);
    $stmt = $conn->prepare("DELETE FROM suspended_users WHERE user_id = ?");
    $stmt->bind_param("i", $unsuspend_id);
    
    if ($stmt->execute()) {
        $message = "User ID $unsuspend_id has been unsuspended.";
    } else {
        $message = "Failed to unsuspend user.";
    }
    $stmt->close();
}

// Fetch suspended users
$stmt = $conn->prepare("SELECT users.id, users.username, users.email, suspended_users.reason, suspended_users.date_suspended FROM users INNER JOIN suspended_users ON users.id = suspended_users.user_id");
$stmt->execute();
$result = $stmt->get_result();
$suspended_users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Suspended Users</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fa;
            margin: 20px;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .btn-unsuspend {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-unsuspend:hover {
            background-color: #218838;
        }
        .message {
            margin-bottom: 15px;
            font-weight: bold;
            color: green;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Suspended Users</h2>

        <?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>

        <?php if (count($suspended_users) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Reason</th>
                        <th>Suspended On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suspended_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo htmlspecialchars($user['reason']); ?></td>
                            <td><?php echo htmlspecialchars($user['date_suspended']); ?></td>
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="unsuspend_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn-unsuspend">Unsuspend</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No suspended users found.</p>
        <?php endif; ?>
    </div>
</body>
</html>