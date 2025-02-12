<!-- 
This will be the main page for inventory manager (subject to change). The user will be able to sign up and login here.
 There will also be additional information about the site and its purpose. 
-->

<?php
    // Needed for MariaDB to work with PHP
    require '/home/tmlarson/connections/connect.php';
    // Start the session
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $password = $_POST['password'];
        $empty = true;
        // Check if the email and password are empty
        if (empty($email) || empty($password)) {
            $empty = false;
            $message = "Email and password cannot be empty.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE username = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && password_verify($password, $row['password']) || $row && $password == $row['password']) {
                session_start();
                $_SESSION['email'] = $row['username'];
                header('Location: dashboard.php');
            }
        }
    }
    

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/stylesheet.css">
    <title>Inventory Manager</title>
</head>
<body class="index-body">
    <header class="index-header">
        <h1>Inventory Manager</h1>
    </header>
    <main class="index-main">
        <section class="index-section">
            <h2>Welcome to Inventory Manager!</h2>
            <p>This is a web application designed to help you manage your inventory efficiently. You can sign up, log in, and start tracking your items.</p>
        </section>
        <section class="index-section">
            <h2>Sign Up</h2>
            <form action="signup.php" method="POST">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Sign Up</button>
            </form>
        </section>
        <section class="index-section">
            <h2>Log In</h2>
            <form action="login.php" method="POST">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" required>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <button type="submit">Log In</button>
            </form>
        </section>
    </main>
    
</body>
</html>