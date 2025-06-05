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
    <title>About PupChat - The Social Playground</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary: #6c5ce7;
            --secondary: #a29bfe;
            --accent: #fd79a8;
            --light: #f5f6fa;
            --dark: #2d3436;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Comic Neue', cursive, sans-serif;
            overflow-x: hidden;
        }
        
        .party-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            position: relative;
        }
        
        /* Floating pups */
        .pup {
            position: absolute;
            width: 80px;
            height: 80px;
            background-size: contain;
            background-repeat: no-repeat;
            z-index: -1;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }
        
        .pup1 { background-image: url('https://cdn.pixabay.com/photo/2017/09/25/13/12/dog-2785074_640.png'); }
        .pup2 { background-image: url('https://cdn.pixabay.com/photo/2017/09/25/13/12/dog-2785076_640.png'); }
        .pup3 { background-image: url('https://cdn.pixabay.com/photo/2017/09/25/13/12/dog-2785077_640.png'); }
        
        /* Header animation */
        .title-box {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(108, 92, 231, 0.2);
            transform-style: preserve-3d;
            position: relative;
            overflow: hidden;
        }
        
        .title-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(108, 92, 231, 0.1),
                rgba(253, 121, 168, 0.1)
            );
            animation: rotate 15s linear infinite;
            z-index: -1;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        h1 {
            font-weight: 800;
            color: var(--primary);
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .tagline {
            color: var(--accent);
            font-weight: 600;
        }
        
        /* Feature cards */
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary);
            position: relative;
            overflow: hidden;
        }
        
        .feature-card:hover {
            transform: translateY(-5px) rotate(1deg);
            box-shadow: 0 15px 30px rgba(108, 92, 231, 0.2);
        }
        
        .feature-icon {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .feature-title {
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        /* Source code section */
        .source-box {
            background: var(--dark);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-top: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .source-box::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://cdn.pixabay.com/photo/2017/01/31/23/42/decorative-2028031_640.png') center/cover;
            opacity: 0.1;
            z-index: 0;
        }
        
        .source-content {
            position: relative;
            z-index: 1;
        }
        
        .github-btn {
            background: #333;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 50px;
            font-weight: 600;
            margin-top: 15px;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }
        
        .github-btn:hover {
            background: #555;
            transform: scale(1.05);
        }
        
        .github-btn i {
            margin-right: 10px;
        }
        
        /* Continuous animations */
        .pulse {
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .wiggle {
            animation: wiggle 3s ease-in-out infinite;
        }
        
        @keyframes wiggle {
            0%, 100% { transform: rotate(-3deg); }
            50% { transform: rotate(3deg); }
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .pup {
                width: 60px;
                height: 60px;
            }
            
            .title-box {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Floating animated pups -->
    <div class="pup pup1 animate__animated animate__bounceInDown" style="top: 10%; left: 5%;"></div>
    <div class="pup pup2 animate__animated animate__bounceInLeft animate__delay-1s" style="top: 30%; right: 8%;"></div>
    <div class="pup pup3 animate__animated animate__bounceInRight animate__delay-2s" style="bottom: 15%; left: 10%;"></div>
    
    <div class="party-container py-5">
        <!-- Animated title section -->
        <div class="title-box animate__animated animate__fadeIn">
            <h1 class="text-center pulse">Welcome to PupChat!</h1>
            <p class="text-center tagline">The social playground for pup lovers everywhere 🐾</p>
        </div>
        
        <!-- What is PupChat? -->
        <div class="feature-card animate__animated animate__fadeInLeft">
            <div class="feature-icon float">
                <i class="fas fa-paw"></i>
            </div>
            <h3 class="feature-title">What is PupChat?</h3>
            <p>PupChat is a vibrant social platform where dog lovers connect, share, and celebrate their furry friends. It's more than just a network—it's a community that barks together!</p>
            <div class="mt-3">
                <span class="badge bg-primary me-2 animate__animated animate__pulse animate__infinite">Connect</span>
                <span class="badge bg-success me-2 animate__animated animate__pulse animate__infinite animate__delay-1s">Share</span>
                <span class="badge bg-accent animate__animated animate__pulse animate__infinite animate__delay-2s">Celebrate</span>
            </div>
        </div>
        
        <!-- Features -->
        <div class="feature-card animate__animated animate__fadeInRight animate__delay-1s">
            <div class="feature-icon wiggle">
                <i class="fas fa-bolt"></i>
            </div>
            <h3 class="feature-title">Awesome Features</h3>
            <div class="row">
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item animate__animated animate__fadeIn"><i class="fas fa-check-circle text-success me-2"></i> Post updates & photos</li>
                        <li class="list-group-item animate__animated animate__fadeIn animate__delay-1s"><i class="fas fa-check-circle text-success me-2"></i> Connect with pup lovers</li>
                        <li class="list-group-item animate__animated animate__fadeIn animate__delay-2s"><i class="fas fa-check-circle text-success me-2"></i> Join dog groups</li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item animate__animated animate__fadeIn animate__delay-3s"><i class="fas fa-check-circle text-success me-2"></i> Private messaging</li>
                        <li class="list-group-item animate__animated animate__fadeIn animate__delay-4s"><i class="fas fa-check-circle text-success me-2"></i> Verified pup profiles</li>
                        <li class="list-group-item animate__animated animate__fadeIn animate__delay-5s"><i class="fas fa-check-circle text-success me-2"></i> And more coming soon!</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Community -->
        <div class="feature-card animate__animated animate__fadeInUp animate__delay-2s">
            <div class="feature-icon pulse">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="feature-title">Our Growing Community</h3>
            <p>Join thousands of pup enthusiasts who share your passion. Whether you're a proud pet parent, professional breeder, or just love dogs, there's a place for you in our pack!</p>
            <div class="d-flex justify-content-around mt-4">
                <div class="text-center">
                    <div class="display-4 animate__animated animate__bounceIn">10K+</div>
                    <small class="text-muted">Pups Registered</small>
                </div>
                <div class="text-center">
                    <div class="display-4 animate__animated animate__bounceIn animate__delay-1s">5K+</div>
                    <small class="text-muted">Daily Posts</small>
                </div>
                <div class="text-center">
                    <div class="display-4 animate__animated animate__bounceIn animate__delay-2s">100+</div>
                    <small class="text-muted">Countries</small>
                </div>
            </div>
        </div>
        
        <!-- Source code section -->
        <div class="source-box animate__animated animate__fadeInUp animate__delay-3s">
            <div class="source-content">
                <h3 class="text-white">Open Source</h3>
                <p class="text-white-50">PupChat is built with love and open-source technologies</p>
                <button class="github-btn" disabled>
                    <i class="fab fa-github"></i> Source Code (Coming Soon)
                </button>
                <p class="text-white-50 mt-3">Check back later for GitHub repository access</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Additional animation triggers
        document.addEventListener('DOMContentLoaded', function() {
            // Make pups float continuously
            const pups = document.querySelectorAll('.pup');
            pups.forEach((pup, index) => {
                pup.style.animation = `float ${4 + index}s ease-in-out infinite`;
            });
            
            // Animate features on scroll
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate__animated', 'animate__fadeInUp');
                    }
                });
            }, { threshold: 0.1 });
            
            document.querySelectorAll('.feature-card').forEach(card => {
                observer.observe(card);
            });
        });
    </script>
</body>
</html>