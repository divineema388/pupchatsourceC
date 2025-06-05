<?php
session_start();
include "db_connect.php";

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
    <title>Help & Support - PupChat</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --dark-color: #2d3436;
            --light-color: #f5f6fa;
        }
        
        body {
            background-color: var(--light-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            max-width: 100%;
            overflow-x: hidden;
        }
        
        .help-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(108, 92, 231, 0.15);
            padding: 25px;
            margin: 20px auto;
            max-width: 500px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .header i {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 15px;
        }
        
        .header h2 {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .contact-card {
            display: flex;
            align-items: center;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 12px;
            background: rgba(108, 92, 231, 0.05);
            transition: all 0.3s ease;
        }
        
        .contact-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.1);
        }
        
        .contact-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin-right: 15px;
            font-size: 1.5rem;
            color: white;
        }
        
        .whatsapp { background-color: #25D366; }
        .telegram { background-color: #0088cc; }
        .website { background-color: var(--primary-color); }
        
        .contact-info {
            flex: 1;
        }
        
        .contact-title {
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--dark-color);
        }
        
        .contact-link {
            color: var(--primary-color);
            text-decoration: none;
            font-size: 0.9rem;
            word-break: break-all;
        }
        
        .developer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px dashed #ddd;
            font-size: 0.9rem;
            color: var(--dark-color);
        }
        
        .developer strong {
            color: var(--primary-color);
        }
        
        .back-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .back-btn:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="help-container">
        <div class="header">
            <i class="fas fa-hands-helping"></i>
            <h2>Help & Support</h2>
            <p>Contact us through these channels</p>
        </div>
        
        <div class="contact-card">
            <div class="contact-icon whatsapp">
                <i class="fab fa-whatsapp"></i>
            </div>
            <div class="contact-info">
                <div class="contact-title">WhatsApp</div>
                <a href="https://wa.me/2349138828989" class="contact-link" target="_blank">
                    +234 913 882 8989
                </a>
            </div>
        </div>
        
        <div class="contact-card">
            <div class="contact-icon telegram">
                <i class="fab fa-telegram"></i>
            </div>
            <div class="contact-info">
                <div class="contact-title">Telegram Bot</div>
                <a href="https://t.me/dealabs_tot_bot" class="contact-link" target="_blank">
                    @dealabs_tot_bot
                </a>
            </div>
        </div>
        
        <div class="contact-card">
            <div class="contact-icon website">
                <i class="fas fa-globe"></i>
            </div>
            <div class="contact-info">
                <div class="contact-title">Website</div>
                <a href="https://dealabs.rf.gd" class="contact-link" target="_blank">
                    dealabs.rf.gd
                </a>
            </div>
        </div>
        
        <div class="developer">
            <p><strong>Developer:</strong> Dev Divine Ema</p>
            <p>We'll respond as quickly as possible</p>
        </div>
        
        <a href="home.php" class="back-btn">
            <i class="fas fa-arrow-left me-2"></i> Back to App
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>