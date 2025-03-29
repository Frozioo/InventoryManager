<?php
    require '/home/tmlarson/connections/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Stockify</title>
    <link rel="stylesheet" href="CSS/stylesheet.css">
    <link rel="icon" href="assets/inventory-system.png">
</head>
<body>
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <h2>Stockify</h2>
        <ul>
            <li><a href="index.php" class="active">Home</a></li>
            <li><a href="login.php">Login</a></li>
            <li><a href="signup.php">Sign Up</a></li>
        </ul>
        <h5>Copyright © 2025 Trey Larson</h5>
    </nav>

    <main class="content">
        <!-- Hero Section -->
        <section class="hero">
            <!-- <img src="assets/cover-image.webp" alt="Inventory Management" class="hero-image"> -->
            <div class="hero-text">
                <h1>Welcome to Stockify</h1>
                <p>Streamline your inventory management with our powerful and user-friendly platform.</p>
            </div>
        </section>

        <!-- Features Section -->
        <section class="features">
            <h2>Why Choose Stockify?</h2>
            <div class="features-grid">
                <div class="feature">
                    <img src="assets/feature1.png" alt="Easy to Use">
                    <h3>Easy to Use</h3>
                    <p>Our intuitive dashboard makes managing your inventory simple and efficient.</p>
                </div>
                <div class="feature">
                    <img src="assets/feature2.png" alt="Real-Time Tracking">
                    <h3>Inventory Management</h3>
                    <p>Track your stock levels in real-time, ensuring you always have the right amount of inventory on hand.</p>
                </div>
                <div class="feature">
                    <img src="assets/feature3.png" alt="Category Management">
                    <h3>Category Management</h3>
                    <p>Organize your inventory by categories for better tracking and streamlined operations.</p>
                </div>
            </div>
        </section>

        <!-- Testimonials Section -->
        <section class="testimonials">
            <h2>What Our Users Say</h2>
            <div class="testimonials-grid">
                <div class="testimonial">
                    <p>"Stockify has transformed the way we handle our stock. Highly recommended!"</p>
                    <h4>- Sarah, Small Business Owner</h4>
                </div>
                <div class="testimonial">
                    <p>"The real-time tracking feature is a game-changer for our warehouse operations."</p>
                    <h4>- Mike, Warehouse Manager</h4>
                </div>
                <div class="testimonial">
                    <p>"I love how easy it is to use. It saves me so much time every day!"</p>
                    <h4>- Emily, Retail Store Manager</h4>
                </div>
            </div>
        </section>

        <!-- Call-to-Action Section -->
        <section class="cta">
            <h2>Ready to Take Control of Your Inventory?</h2>
            <p>Join thousands of businesses already using Stockify to streamline their operations.</p>
            <a href="signup.php" class="btn">Sign Up Now</a>
        </section>
    </main>
</body>
</html>