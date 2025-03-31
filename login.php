<?php
    // Needed for MariaDB to work with PHP
    require '/home/tmlarson/connections/connect.php';
    session_start();
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $empty = true;
        if (empty($email) || empty($password)) {
            $empty = false;
            $message = "Email and password cannot be empty.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password']) || $row && $password == $row['password']) {
                session_start();
                $_SESSION['email'] = $row['email'];
                header('Location: dashboard.php');
                exit;
            } else {
                $stmt = $conn->prepare("select * from Admin where admin_email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                // Admin login
                if ($row && $password == $row['admin_password']) {
                    session_start();
                    $_SESSION['email'] = $row['admin_email'];
                    header("Location: adminPortal.php");
                } else {
                    $message = "Invalid email or password.";
                }
            }
        }
    }
    

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventory Manager</title>
    <link rel="stylesheet" href="CSS/stylesheet.css">
    <link rel="icon" href="assets/inventory-system.png">
</head>
<body>
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h2>Inventory Manager</h2>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php" class="active">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
            <h5>Copyright © 2025 Trey Larson</h5>
        </nav>

        <main class="content">
            <h1>Log In</h1>
            <p>Access your inventory by logging in below.</p>
            <br>
            <!-- Login Form -->
            <form class="login-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <button type="submit">Log In</button>

                <?php if (!empty($message)): ?>
                    <p class="error"><?php echo $message; ?></p>
                <?php endif; ?>
            </form>
        </main>

</body>
</html>