<?php
session_start();
include "../db_connect.php";

// Ensure the user is logged in
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];
$username_query = $conn->query("SELECT username FROM users WHERE id = '$user_id'");
$username_row = $username_query->fetch_assoc();
$username = $username_row['username'];

// Generate unique referral link
$referral_link = "https://pupchat.infy.uk/new_user.php?referral=$user_id";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Share Your Referral Link</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            font-family: 'Arial', sans-serif;
            color: #fff;
        }

        .container {
            max-width: 800px;
            padding: 30px;
            background-color: rgba(0, 0, 0, 0.7);
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin-top: 50px;
        }

        h2, h4 {
            font-family: 'Roboto', sans-serif;
            text-align: center;
            font-weight: 700;
        }

        p {
            font-size: 1.2rem;
            text-align: center;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .btn {
            background-color: #ff7e5f;
            color: white;
            border: none;
            padding: 10px 20px;
            margin-top: 10px;
            font-size: 1.1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #feb47b;
            cursor: pointer;
        }

        .referral-list {
            margin-top: 30px;
        }

        .referral-list h3 {
            text-align: center;
            font-weight: 600;
        }

        .referral-list ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
        }

        .referral-list li {
            background: #ffffff;
            color: #333;
            margin: 5px;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Share PupChat Everywhere!</h2>
        <p>Invite your friends to join the network by sharing the link below.</p>

        <div class="card">
            <h4>Your Unique Invite Link</h4>
            <input type="text" class="form-control" value="<?php echo $referral_link; ?>" readonly>
            <button class="btn" onclick="copyLink()">Copy Link</button>
        </div>

        <div class="referral-list">
            <?php
            $user_id = $_SESSION["user_id"];
            $referrals_query = $conn->query("SELECT username FROM users WHERE referrer_id = '$user_id'");

            echo "<h3>Your Referrals</h3>";
            if ($referrals_query->num_rows > 0) {
                echo "<ul>";
                while ($referral = $referrals_query->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($referral['username']) . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No referrals yet.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        function copyLink() {
            const link = document.querySelector("input");
            link.select();
            link.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand("copy");
            alert("Invite link copied to clipboard!");
        }
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>