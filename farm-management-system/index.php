<?php
session_start();
// Check if user is logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    switch($_SESSION['user_role']) {
        case 'admin':
            header('Location: views/users/admin/dashboard.php');
            exit;
        case 'farmer':
            header('Location: views/users/farmer/dashboard.php');
            exit;
        case 'customer':
            header('Location: views/users/customer/dashboard.php');
            exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farm Management System - Home</title>
    <style>
        /* Farm Theme Styles */
        :root {
            --forest-green: #228B22;
            --earth-brown: #8B4513;
            --sky-blue: #87CEEB;
            --cream-white: #FFFDD0;
            --wheat: #F5DEB3;
            --dark-brown: #3E2723;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--forest-green), var(--sky-blue));
            min-height: 100vh;
            color: var(--dark-brown);
        }

        /* Header Styles */
        .header {
            background: linear-gradient(to right, var(--forest-green), var(--earth-brown));
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.2);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: var(--cream-white);
            color: var(--forest-green);
        }

        .btn-primary:hover {
            background: var(--wheat);
            transform: translateY(-2px);
        }

        /* Hero Section */
        .hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 4rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
            min-height: 80vh;
        }

        .hero-content {
            flex: 1;
            padding-right: 2rem;
        }

        .hero-title {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: white;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .hero-subtitle {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            color: var(--cream-white);
            line-height: 1.6;
        }

        .hero-image {
            flex: 1;
            text-align: center;
            font-size: 15rem;
            animation: float 6s ease-in-out infinite;
        }

        /* Features Section */
        .features {
            background: var(--cream-white);
            padding: 4rem 2rem;
        }

        .section-title {
            text-align: center;
            font-size: 2.5rem;
            margin-bottom: 3rem;
            color: var(--forest-green);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-left: 5px solid var(--forest-green);
            transition: transform 0.3s ease;
        }

        .feature-card:hover {
            transform: translateY(-10px);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: var(--forest-green);
        }

        .feature-description {
            color: var(--dark-brown);
            line-height: 1.6;
        }

        /* Role Selection */
        .roles {
            padding: 4rem 2rem;
            background: linear-gradient(135deg, var(--earth-brown), var(--forest-green));
            color: white;
        }

        .roles-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .role-card {
            background: rgba(255,255,255,0.1);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
        }

        .role-card:hover {
            background: rgba(255,255,255,0.2);
            transform: scale(1.05);
        }

        .role-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .role-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .role-description {
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* Authentication Section */
        .auth-section {
            background: var(--cream-white);
            padding: 4rem 2rem;
            text-align: center;
        }

        .auth-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 2rem;
        }

        .btn-large {
            padding: 15px 30px;
            font-size: 1.1rem;
        }

        .btn-success {
            background: var(--forest-green);
            color: white;
        }

        .btn-success:hover {
            background: var(--earth-brown);
        }

        /* Footer */
        .footer {
            background: var(--dark-brown);
            color: var(--cream-white);
            padding: 2rem;
            text-align: center;
        }

        /* Animations */
        @keyframes float {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-20px);
            }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .hero {
                flex-direction: column;
                text-align: center;
                padding: 2rem 1rem;
            }

            .hero-content {
                padding-right: 0;
                margin-bottom: 2rem;
            }

            .hero-title {
                font-size: 2rem;
            }

            .hero-image {
                font-size: 8rem;
            }

            .nav-links {
                gap: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <span>🚜</span>
            <span>FARM MANAGEMENT SYSTEM</span>
        </div>
        <div class="nav-links">
            <a href="#features" class="nav-link">Features</a>
            <a href="#roles" class="nav-link">Roles</a>
            <a href="#auth" class="nav-link">Get Started</a>
        </div>
    </div>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-title">🌾 Welcome to Your Digital Farm 🌾</h1>
            <p class="hero-subtitle">
                Streamline your farm operations with our comprehensive management system. 
                Track animals, manage inventory, handle sales, and make data-driven decisions 
                to grow your agricultural business efficiently.
            </p>
            <div class="auth-buttons">
                <a href="views/auth/register.php" class="btn btn-primary btn-large">
                    <span>🌱</span>
                    <span>Get Started Free</span>
                </a>
                <a href="views/auth/login.php" class="btn btn-success btn-large">
                    <span>🔑</span>
                    <span>Sign In</span>
                </a>
            </div>
        </div>
        <div class="hero-image">
            🚜
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <h2 class="section-title">✨ Key Features</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🐄</div>
                <h3 class="feature-title">Animal Management</h3>
                <p class="feature-description">
                    Track all your livestock with detailed records including breed, 
                    health status, location, and population statistics.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📦</div>
                <h3 class="feature-title">Inventory Control</h3>
                <p class="feature-description">
                    Manage farm supplies, equipment, and feed with real-time 
                    stock monitoring and automated alerts.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3 class="feature-title">Financial Tracking</h3>
                <p class="feature-description">
                    Monitor income, expenses, sales orders, and generate 
                    comprehensive financial reports.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3 class="feature-title">Analytics & Reports</h3>
                <p class="feature-description">
                    Make informed decisions with detailed analytics, 
                    charts, and customizable reports.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👥</div>
                <h3 class="feature-title">Multi-Role Access</h3>
                <p class="feature-description">
                    Secure role-based access for administrators, farmers, 
                    and customers with appropriate permissions.
                </p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <h3 class="feature-title">Secure & Reliable</h3>
                <p class="feature-description">
                    Enterprise-grade security with two-factor authentication 
                    and regular data backups.
                </p>
            </div>
        </div>
    </section>

    <!-- Role Selection Section -->
    <section class="roles" id="roles">
        <h2 class="section-title" style="color: white;">🎭 Choose Your Role</h2>
        <div class="roles-grid">
            </div>
            <div class="role-card">
                <div class="role-icon">👨‍🌾</div>
                <h3 class="role-title">Farmer</h3>
                <p class="role-description">
                    Manage your animals, track health records, 
                    monitor inventory, and generate farm reports.
                </p>
                <a href="views/auth/register.php?role=farmer" class="btn btn-primary">
                    Join as Farmer
                </a>
            </div>
            <div class="role-card">
                <div class="role-icon">🛒</div>
                <h3 class="role-title">Customer</h3>
                <p class="role-description">
                    Browse available animals, place orders, 
                    track purchases, and manage your account.
                </p>
                <a href="views/auth/register.php?role=customer" class="btn btn-primary">
                    Join as Customer
                </a>
            </div>
        </div>
    </section>

    <!-- Authentication Section -->
    <section class="auth-section" id="auth">
        <h2 class="section-title">🚀 Ready to Get Started?</h2>
        <p style="font-size: 1.2rem; margin-bottom: 2rem; color: var(--dark-brown);">
            Join thousands of farmers who are already managing their farms efficiently with our system.
        </p>
        <div class="auth-buttons">
            <a href="views/auth/register.php" class="btn btn-success btn-large">
                <span>🌱</span>
                <span>Create Your Account</span>
            </a>
            <a href="views/auth/login.php" class="btn btn-primary btn-large">
                <span>🔑</span>
                <span>Sign In to Existing Account</span>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Farm Management System. All rights reserved. | 🚜 Cultivating Digital Agriculture</p>
        <p style="margin-top: 1rem; opacity: 0.8;">
            Built with ❤️ for the farming community
        </p>
    </footer>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add some interactive animations
        document.addEventListener('DOMContentLoaded', function() {
            const featureCards = document.querySelectorAll('.feature-card');
            featureCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });

            // Parallax effect for hero section
            window.addEventListener('scroll', function() {
                const scrolled = window.pageYOffset;
                const hero = document.querySelector('.hero');
                if (hero) {
                    hero.style.transform = `translateY(${scrolled * 0.5}px)`;
                }
            });
        });
    </script>
</body>
</html>