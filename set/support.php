<?php
session_start();

// Redirect users if they are not logged in
if (!isset($_SESSION["user_id"])) {  
    header("Location: login.php");  
    exit();  
}  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat Support Center</title>

    <!-- External Styles -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Raleway', sans-serif;
            background: linear-gradient(135deg, #ff6ec4, #7873f5);
            color: white;
            text-align: center;
            padding: 40px;
        }
        .container {
            background: white;
            color: #333;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            margin: auto;
        }
        h2 {
            color: #ff6ec4;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-custom {
            background: #ff6ec4;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background: #7873f5;
        }
        .chat-btn {
            display: block;
            margin-top: 20px;
            background: #ff6ec4;
            color: white;
            text-decoration: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
        }
        .chat-btn:hover {
            background: #7873f5;
        }
        .faq-section {
            text-align: left;
            margin-top: 30px;
        }
        .faq-section h3 {
            color: #7873f5;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .faq-section p {
            font-size: 14px;
            margin-bottom: 15px;
        }
        .contact-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .contact-section h3 {
            color: #ff6ec4;
        }
    </style>
</head>
<body>

<div class="container">
    <h2><i class="fas fa-headset"></i> PupChat Support Center</h2>
    <p>Welcome to the PupChat Support Center. If you're experiencing issues, please check our FAQ section or submit a support request.</p>

    <!-- Troubleshooting Section -->
    <div class="faq-section">
        <h3><i class="fas fa-question-circle"></i> Frequently Asked Questions</h3>

        <p><strong>❓ How do I reset my password?</strong><br>
        Go to the <a href="https://pupchat.infy.uk/recover.php">Password Recovery</a> page and enter your registered email. You’ll receive a reset link.</p>

        <p><strong>❓ Why can't I send messages?</strong><br>
        Ensure that your internet connection is stable. If the issue persists, try logging out and logging back in.</p>

        <p><strong>❓ How do I report a bug?</strong><br>
        Use the form below to describe the issue, and if possible, attach a screenshot.</p>
    </div>

    <!-- Support Request Form -->
    <h3 class="mt-4"><i class="fas fa-envelope-open-text"></i> Submit a Support Request</h3>
    <form action="submit_support.php" method="POST" enctype="multipart/form-data">
        <input type="text" class="form-control mb-3" name="name" placeholder="Your Name" required>
        <input type="email" class="form-control mb-3" name="email" placeholder="Your Email" required>
        <textarea class="form-control mb-3" name="issue" rows="4" placeholder="Describe your issue..." required></textarea>
        <input type="file" class="form-control mb-3" name="image">
        <button type="submit" class="btn btn-custom btn-block"><i class="fas fa-paper-plane"></i> Submit Issue</button>
    </form>

    <a href="../support.php" class="chat-btn"><i class="fas fa-comments"></i> Live Support Chat</a>

    <!-- Contact Information -->
    <div class="contact-section">
        <h3><i class="fas fa-phone-alt"></i> Contact Us</h3>
        <p><strong>Email:</strong>pupchatinc@gmail.com</p>
        <p><strong>Phone:</strong> +234 9138828989</p>
    </div>
</div>

</body>
</html>