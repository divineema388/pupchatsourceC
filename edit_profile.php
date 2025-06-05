<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$user_query = $conn->query("SELECT * FROM users WHERE id='$user_id'");
$user = $user_query->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $bio = trim($_POST["bio"]);

    // Handle profile picture upload
    if (!empty($_FILES["profile_pic"]["name"])) {
        $target_dir = "media/";
        $new_filename = "profile_" . $user_id . "_" . time() . "." . pathinfo($_FILES["profile_pic"]["name"], PATHINFO_EXTENSION);
        $target_file = $target_dir . $new_filename;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png"];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                $conn->query("UPDATE users SET profile_pic='$target_file' WHERE id='$user_id'");
            }
        }
    }

    $conn->query("UPDATE users SET bio='$bio' WHERE id='$user_id'");
    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    
    <!-- External CSS Libraries -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        .container {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            max-width: 600px;
            margin: auto;
        }
        h2 {
            font-size: 32px;
            margin-bottom: 15px;
            color: #007bff;
        }
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }
        .input-group label {
            font-size: 18px;
            color: #333;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }
        input[type="file"], textarea {
            font-size: 16px;
            padding: 12px;
            margin: 10px 0;
            border: 2px solid #ccc;
            border-radius: 5px;
            width: 100%;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }
        input[type="file"]:focus, textarea:focus {
            border-color: #007bff;
            background-color: #f1faff;
            outline: none;
        }
        .preview-container {
            margin-top: 20px;
            display: inline-block;
            text-align: center;
        }
        .preview-container img {
            max-width: 150px;
            max-height: 150px;
            border-radius: 5px;
            object-fit: cover;
            border: 2px solid #007bff;
        }
        .button {
            padding: 12px 30px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        .button:hover {
            background: #218838;
        }
        .button:active {
            background: #1e7e34;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Edit Profile</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="input-group">
            <label for="profile_pic">Profile Picture:</label>
            <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="previewImage(event)">
        </div>

        <div class="preview-container">
            <img id="imgPreview" src="<?php echo isset($user["profile_pic"]) ? htmlspecialchars($user["profile_pic"]) : 'media/default_profile.png'; ?>" alt="Profile Picture Preview">
        </div>

        <div class="input-group">
            <label for="bio">Bio:</label>
            <textarea name="bio" id="bio" rows="4"><?php echo htmlspecialchars($user["bio"]); ?></textarea>
        </div>

        <button type="submit" class="button">Save Changes</button>
    </form>
</div>

<!-- External JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<script>
    function previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                document.getElementById('imgPreview').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    }
</script>

</body>
</html>