<?php
include "../db_connect.php";

$users_query = $conn->query("SELECT id, username, verified FROM users");

if (isset($_GET["approve"])) {
    $user_id_to_approve = intval($_GET["approve"]);
    $conn->query("UPDATE users SET verified = 1 WHERE id = '$user_id_to_approve'");
    header("Location: manage_users.php");
    exit();
}

if (isset($_GET["unapprove"])) {
    $user_id_to_unapprove = intval($_GET["unapprove"]);
    $conn->query("UPDATE users SET verified = 0 WHERE id = '$user_id_to_unapprove'");
    header("Location: manage_users.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
<div class="container">
    <h2 class="my-4">Manage Users</h2>

    <!-- Users Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Username</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $users_query->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $user["username"]; ?></td>
                    <td>
                        <?php if ($user["verified"] == 1) { ?>
                            <span class="text-primary">🟦 Verified</span>
                        <?php } else { ?>
                            <span class="text-danger">Not Verified</span>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if ($user["verified"] == 0) { ?>
                            <a href="manage_users.php?approve=<?php echo $user['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                        <?php } else { ?>
                            <a href="manage_users.php?unapprove=<?php echo $user['id']; ?>" class="btn btn-warning btn-sm">Unapprove</a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>