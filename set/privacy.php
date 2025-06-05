<?php
session_start();
include "../db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hide_profile = isset($_POST["hide_profile"]) ? 1 : 0;

    $query = "UPDATE users SET hide_profile = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $hide_profile, $user_id);
    
    if ($stmt->execute()) {
        $message = "Privacy settings updated successfully!";
    } else {
        $message = "Error updating privacy settings.";
    }
}

$query = "SELECT hide_profile FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Settings</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2>Privacy Settings</h2>
        <p>Control your profile visibility in search results.</p>

        <?php if (isset($message)) { echo "<p class='alert alert-info'>$message</p>"; } ?>

        <form method="POST">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="hide_profile" id="hide_profile" <?php echo ($user['hide_profile'] ? 'checked' : ''); ?>>
                <label class="form-check-label" for="hide_profile">
                    Hide my profile from search results
                </label>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Save Changes</button>
        </form>

        <a href="../profile.php" class="btn btn-secondary mt-3">Back to Profile</a>
    </div>
</body>
</html>