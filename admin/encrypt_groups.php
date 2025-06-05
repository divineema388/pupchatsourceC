<?php
session_start();
include "../db_connect.php";

// Handle group encryption
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['group_id'], $_POST['group_name'], $_POST['group_password'])) {
    $group_id = $_POST['group_id'];
    $group_name = $_POST['group_name'];
    $group_password = $_POST['group_password'];

    // Encrypt the group name
    $encrypted_group_name = openssl_encrypt($group_name, 'aes-256-cbc', 'encryption_key', 0, 'encryption_iv');
    $hashed_password = password_hash($group_password, PASSWORD_BCRYPT); // Hash the password

    // Update the group with the encrypted name and password
    $stmt = $conn->prepare("UPDATE groups SET group_name = ?, is_encrypted = 1, encrypted_password = ? WHERE id = ?");
    $stmt->bind_param("ssi", $encrypted_group_name, $hashed_password, $group_id);
    $stmt->execute();
}

// Fetch all groups for admin management
$groups_query = $conn->query("SELECT * FROM groups");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encrypt Groups - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Encrypt Group Names</h2>
        <form method="POST" action="encrypt_groups.php">
            <input type="text" name="group_id" placeholder="Group ID" required>
            <input type="text" name="group_name" placeholder="New Group Name" required>
            <input type="password" name="group_password" placeholder="Group Password" required>
            <button type="submit" class="btn btn-primary">Encrypt & Update</button>
        </form>

        <h3 class="mt-4">Manage Groups</h3>
        <?php while ($group = $groups_query->fetch_assoc()) { ?>
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                <strong><?php echo htmlspecialchars($group['group_name']); ?></strong>
                <a href="groupdev.php?group_id=<?php echo $group['id']; ?>" class="btn btn-primary">Manage</a>
            </div>
        <?php } ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>