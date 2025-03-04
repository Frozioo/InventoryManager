<?php
    require '/home/tmlarson/connections/connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Inventory Manager</title>
    <link rel="stylesheet" href="CSS/stylesheet.css">
    <link rel="icon" href="assets/inventory-system.png">
</head>
<body>
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h2>Inventory Manager</h2>
            <ul>
                <li><a href="index.php" class="active">Home</a></li>
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
            <h5>Copyright © 2025 Trey Larson</h5>
        </nav>

        <main class="content">
            <h1>Welcome to Inventory Manager</h1>
            <p>Manage your inventory efficiently with our easy-to-use platform.</p>
            <section class="cta">
                <p>Join us and start managing your inventory!</p>
                <a href="signup.php" class="btn">Sign Up Now</a>
            </section>
        </main>
    </div>

</body>
</html>
