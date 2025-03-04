<!-- User will be able to view their profile information and change their password -->

<?php

    require '/home/tmlarson/connections/connect.php';
    session_start();

    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }
    $stmt = $conn->prepare("SELECT email FROM Users WHERE email = :email");
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : null;
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : null;
        $password2 = isset($_POST["password2"]) ? trim($_POST["password2"]) : null;

        if (empty($email) || empty($password) || empty($password2)) {
            $message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "Invalid email format.";
        } elseif ($password !== $password2) {
            $message = "Passwords do not match.";
        } else {
            try {
                $stmt = $conn->prepare("UPDATE Users SET email = :email, password = :password WHERE email = :old_email");
                $stmt->bindParam(':email', $email);
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $hashed_password);
                $stmt->bindParam(':old_email', $_SESSION['email']);
                $stmt->execute();

                $_SESSION['email'] = $email;

                header('Location: profile.php');
                exit;
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
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
    <title>Profile - Inventory Manager</title>
    <link rel="icon" href="assets/inventory-system.png">
</head>
<body>
    <h1>User Profile</h1>

            <!-- Sidebar Navigation -->
            <nav class="sidebar">
           <h2>Inventory Manager</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php" class="active">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <h5>Copyright © 2025 Trey Larson</h5>
        </nav>

        <main class="content">
            <h1>Profile</h1>
            <p>Update your profile information below.</p>
            <br>
            <form class="profile-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <label for="email">Email:</label>
                <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($row['email']); ?>" required>

                <label for="password">New Password:</label>
                <input type="password" id="password" name="password">

                <label for="password2">Confirm New Password:</label>
                <input type="password" id="password2" name="password2">

                <button type="submit">Update Profile</button>

                <?php if (!empty($message)): ?>
                    <p class="error"><?php echo $message; ?></p>
                <?php endif; ?>
            </form>
        </main>
</body>
</html>