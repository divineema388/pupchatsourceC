<?php
session_start();
include "db_connect.php";

// Check if referral id is present in the URL
$referral_id = isset($_GET['referral']) ? intval($_GET['referral']) : null;
$referrer_username = '';

// If referral id is set, show who invited the user
if ($referral_id) {
    $referrer_query = $conn->query("SELECT username FROM users WHERE id = '$referral_id'");
    if ($referrer_query->num_rows > 0) {
        $referrer_row = $referrer_query->fetch_assoc();
        $referrer_username = $referrer_row['username'];
    } else {
        echo "<p>Invalid referral link.</p>";
        exit();
    }
}

// Handle new user signup after the invite link
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];

    // Insert new user into the database with the referrer_id
    $stmt = $conn->prepare("INSERT INTO users (username, password, email, referrer_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $password, $email, $referral_id);
    $stmt->execute();

    // After signup, redirect to the homepage or dashboard
    header("Location: home.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - PupChat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background: #f0f4f8;
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        .container {
            max-width: 550px;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 60px;
        }

        h2 {
            font-family: 'Georgia', serif;
            color: #1E3A8A;
            font-weight: bold;
            text-align: center;
            margin-bottom: 30px;
        }

        p {
            font-family: 'Roboto', sans-serif;
            color: #333;
            text-align: center;
            font-size: 1.1rem;
            margin-bottom: 20px;
        }

        .referral-info {
            font-family: 'Tahoma', sans-serif;
            font-style: italic;
            color: #6c757d;
            text-align: center;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: bold;
        }

        .form-control {
            border-radius: 8px;
            box-shadow: inset 0 0 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: #4CAF50;
            border: none;
            padding: 10px 20px;
            font-size: 1.1rem;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #45a049;
        }

        .footer {
            font-size: 0.9rem;
            text-align: center;
            margin-top: 40px;
            color: #6c757d;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .cta-banner {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 30px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Welcome to PupChat!</h2>

        <?php if ($referral_id && $referrer_username): ?>
            <p class="referral-info">You were invited by <strong><?php echo htmlspecialchars($referrer_username); ?></strong></p>
        <?php endif; ?>

        <div class="cta-banner">
            Join PupChat and connect with others! Sign up now.
        </div>

        <form method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign Up</button>
        </form>
    </div>

    <div class="footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>