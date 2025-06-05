<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include "../db_connect.php";

// Check if the user is an admin


// Fetch all groups
$groups_query = $conn->query("SELECT id, group_name FROM groups");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Groups - Admin</title>
</head>
<body>
    <h2>Manage Groups</h2>
    
    <form method="POST" action="add_group.php">
        <input type="text" name="group_name" placeholder="Group Name" required>
        <button type="submit">Add Group</button>
    </form>

    <h3>Groups List</h3>
    <ul>
        <?php while ($group = $groups_query->fetch_assoc()) { ?>
            <li>
                <?php echo htmlspecialchars($group['group_name']);
if (!empty($group['is_verified']) && $group['is_verified'] == 1) {
    echo ' <img src="media/veri.png" alt="Verified" width="20">';
}
                <a href="remove_group.php?id=<?php echo $group['id']; ?>">Remove</a>
            </li>
        <?php } ?>
    </ul>
</body>
</html>