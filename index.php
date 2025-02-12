<!-- 
This will be the main page for inventory manager (subject to change). The user will be able to use the nav bar to navigate to the login and signup pages.
 There will also be additional information about the site and its purpose. 
-->

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
</head>
<body>

    <div class="container">
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

        <!-- Main Content -->
        <main class="content">
            <h1>Welcome to Inventory Manager</h1>
            <p>Manage your inventory efficiently with our easy-to-use platform.</p>
            <p>Use the navigation on the left to log in or sign up.</p>
        </main>
    </div>

</body>
</html>
