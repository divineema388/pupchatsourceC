<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PupChat - Connect with Fellow Dog Lovers</title>
    
    <!-- Favicon -->
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        :root {
            --primary: #6C63FF;
            --secondary: #FF6584;
            --accent: #FFD166;
            --light: #F8F9FA;
            --dark: #212529;
            --gray: #6C757D;
            --light-gray: #E9ECEF;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }
        
        /* Header Styles */
        header {
            background: linear-gradient(135deg, var(--primary) 0%, #7b74ff 100%);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        
        .logo {
            display: flex;
            align-items: center;
            font-family: 'Fredoka One', cursive;
            font-size: 1.8rem;
            text-decoration: none;
            color: white;
        }
        
        .logo i {
            margin-right: 10px;
            color: var(--accent);
        }
        
        /* Sidebar Navigation */
        .sidebar-toggle {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .sidebar-toggle:hover {
            transform: scale(1.1);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            right: -300px;
            width: 300px;
            height: 100%;
            background: white;
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            z-index: 1001;
            transition: right 0.3s ease;
            padding: 2rem;
        }
        
        .sidebar.active {
            right: 0;
        }
        
        .sidebar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .sidebar-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--gray);
            cursor: pointer;
        }
        
        .sidebar-nav {
            list-style: none;
        }
        
        .sidebar-nav li {
            margin-bottom: 1rem;
        }
        
        .sidebar-nav a {
            display: block;
            padding: 0.8rem 1rem;
            background: var(--light-gray);
            color: var(--dark);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        
        .sidebar-nav a:hover {
            background: var(--primary);
            color: white;
            transform: translateX(5px);
        }
        
        .sidebar-nav i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Hero Section */
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 8rem 2rem 4rem;
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.9)), 
                        url('assets/pupchat-bg.jpg') no-repeat center center/cover;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }
        
        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            color: var(--primary);
            font-family: 'Fredoka One', cursive;
        }
        
        .hero-content h1 span {
            color: var(--secondary);
        }
        
        .hero-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--gray);
        }
        
        .cta-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a52e0;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(108, 99, 255, 0.2);
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: white;
        }
        
        .btn-secondary:hover {
            background: #e64c6c;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(255, 101, 132, 0.2);
        }
        
        .hero-image {
            position: relative;
            text-align: center;
        }
        
        .hero-image img {
            max-width: 100%;
            height: auto;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            animation: float 6s ease-in-out infinite;
        }
        
        /* Features Section */
        .features {
            padding: 5rem 2rem;
            background: white;
        }
        
        .section-title {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .section-title h2 {
            font-size: 2.5rem;
            color: var(--primary);
            font-family: 'Fredoka One', cursive;
            margin-bottom: 1rem;
        }
        
        .section-title p {
            color: var(--gray);
            max-width: 700px;
            margin: 0 auto;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .feature-card {
            background: var(--light);
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary) 0%, #7b74ff 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 2rem;
        }
        
        .feature-card h3 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--dark);
        }
        
        .feature-card p {
            color: var(--gray);
        }
        
        /* Testimonials */
        .testimonials {
            padding: 5rem 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
        }
        
        .testimonials-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .testimonial-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }
        
        .testimonial-card::before {
            content: '"';
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 5rem;
            color: var(--light-gray);
            font-family: serif;
            line-height: 1;
            z-index: 0;
        }
        
        .testimonial-content {
            position: relative;
            z-index: 1;
            margin-bottom: 1.5rem;
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 3px solid var(--primary);
        }
        
        .author-info h4 {
            color: var(--dark);
            margin-bottom: 0.2rem;
        }
        
        .author-info p {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Footer */
        footer {
            background: var(--dark);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .footer-logo {
            font-family: 'Fredoka One', cursive;
            font-size: 2rem;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
        
        .footer-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .footer-links a:hover {
            color: var(--accent);
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
        }
        
        .social-links a:hover {
            background: var(--primary);
            transform: translateY(-3px);
        }
        
        .copyright {
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        .copyright a {
            color: var(--accent);
            text-decoration: none;
        }
        
        /* Animations */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        .animate-delay-1 {
            animation-delay: 0.2s;
        }
        
        .animate-delay-2 {
            animation-delay: 0.4s;
        }
        
        .animate-delay-3 {
            animation-delay: 0.6s;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .hero-container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .hero-content {
                order: 2;
            }
            
            .hero-image {
                order: 1;
                margin-bottom: 3rem;
            }
            
            .cta-buttons {
                justify-content: center;
            }
        }
        
        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }
            
            .section-title h2 {
                font-size: 2rem;
            }
        }
        
        @media (max-width: 576px) {
            .cta-buttons {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-container">
            <a href="index.php" class="logo animate__animated animate__fadeInLeft">
                <i class="fas fa-paw"></i> PupChat
            </a>
            <button class="sidebar-toggle animate__animated animate__fadeInRight">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </header>
    
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>Menu</h3>
            <button class="sidebar-close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <ul class="sidebar-nav">
            <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <li><a href="signup.php"><i class="fas fa-user-plus"></i> Sign Up</a></li>
            <li><a href="tc.html"><i class="fas fa-file-alt"></i> Terms & Conditions</a></li>
            <li><a href="about.html"><i class="fas fa-info-circle"></i> About PupChat</a></li>
        </ul>
    </div>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content animate__animated animate__fadeInLeft">
                <h1>Connect with <span>Fellow Dog Lovers</span></h1>
                <p>PupChat is the premier social network for dog enthusiasts. Share moments, join groups, and connect with a community that loves dogs as much as you do!</p>
                <div class="cta-buttons">
                    <a href="signup.php" class="btn btn-primary animate__animated animate__fadeInUp animate-delay-1">Get Started</a>
                    <a href="login.php" class="btn btn-secondary animate__animated animate__fadeInUp animate-delay-2">Login</a>
                </div>
            </div>
            <div class="hero-image animate__animated animate__fadeInRight">
                <img src="assets/hero-dogs.png" alt="Happy dogs connecting">
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="features">
        <div class="section-title animate__animated animate__fadeIn">
            <h2>Why Choose PupChat?</h2>
            <p>Discover the features that make our community special</p>
        </div>
        <div class="features-grid">
            <div class="feature-card animate__animated animate__fadeInUp">
                <div class="feature-icon">
                    <i class="fas fa-users"></i>
                </div>
                <h3>Dog Lovers Community</h3>
                <p>Connect with thousands of dog owners and enthusiasts who share your passion for our furry friends.</p>
            </div>
            <div class="feature-card animate__animated animate__fadeInUp animate-delay-1">
                <div class="feature-icon">
                    <i class="fas fa-camera"></i>
                </div>
                <h3>Photo Sharing</h3>
                <p>Show off your pup with our easy-to-use photo sharing features and get lots of pawsitive feedback!</p>
            </div>
            <div class="feature-card animate__animated animate__fadeInUp animate-delay-2">
                <div class="feature-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>Group Chats</h3>
                <p>Join breed-specific or local dog owner groups to share tips, arrange playdates, and more.</p>
            </div>
        </div>
    </section>
    
    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="section-title animate__animated animate__fadeIn">
            <h2>What Our Users Say</h2>
            <p>Hear from our happy community members</p>
        </div>
        <div class="testimonials-grid">
            <div class="testimonial-card animate__animated animate__fadeInUp">
                <div class="testimonial-content">
                    <p>PupChat helped me find other golden retriever owners in my area. Now our dogs have weekly playdates!</p>
                </div>
                <div class="testimonial-author">
                    <img src="assets/user1.jpg" alt="Sarah J." class="author-avatar">
                    <div class="author-info">
                        <h4>Sarah J.</h4>
                        <p>Golden Retriever Owner</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card animate__animated animate__fadeInUp animate-delay-1">
                <div class="testimonial-content">
                    <p>As a first-time dog owner, the advice I've gotten from the PupChat community has been invaluable.</p>
                </div>
                <div class="testimonial-author">
                    <img src="assets/user2.jpg" alt="Michael T." class="author-avatar">
                    <div class="author-info">
                        <h4>Michael T.</h4>
                        <p>New Dog Parent</p>
                    </div>
                </div>
            </div>
            <div class="testimonial-card animate__animated animate__fadeInUp animate-delay-2">
                <div class="testimonial-content">
                    <p>I've made lifelong friends through PupChat - both human and canine! Best community ever.</p>
                </div>
                <div class="testimonial-author">
                    <img src="assets/user3.jpg" alt="Lisa M." class="author-avatar">
                    <div class="author-info">
                        <h4>Lisa M.</h4>
                        <p>Dog Trainer</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="footer-container">
            <a href="index.php" class="footer-logo">
                <i class="fas fa-paw"></i> PupChat
            </a>
            <div class="footer-links">
                <a href="about.html">About Us</a>
                <a href="tc.html">Terms & Conditions</a>
                <a href="privacy.html">Privacy Policy</a>
                <a href="contact.html">Contact</a>
            </div>
            <div class="social-links">
                <a href="#"><i class="fab fa-facebook-f"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
            <p class="copyright">
                &copy; 2023 PupChat. All rights reserved.<br>
                Developed by <a href="#">Dev Divine Ema Asuquo</a> | Powered by <a href="https://dealabs.rf.gd" target="_blank">Dealabs</a>
            </p>
        </div>
    </footer>
    
    <script>
        // Sidebar Toggle
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const sidebarClose = document.querySelector('.sidebar-close');
        const sidebar = document.querySelector('.sidebar');
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('active');
        });
        
        sidebarClose.addEventListener('click', () => {
            sidebar.classList.remove('active');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', (e) => {
            if (!sidebar.contains(e.target) && e.target !== sidebarToggle) {
                sidebar.classList.remove('active');
            }
        });
        
        // Animation on scroll
        document.addEventListener('DOMContentLoaded', () => {
            const animateElements = document.querySelectorAll('.animate__animated');
            
            const animateOnScroll = () => {
                animateElements.forEach(element => {
                    const elementTop = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementTop < windowHeight - 100) {
                        element.style.opacity = 1;
                    }
                });
            };
            
            // Initial check
            animateOnScroll();
            
            // Check on scroll
            window.addEventListener('scroll', animateOnScroll);
        });
    </script>
</body>
</html>