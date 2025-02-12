<?php
    require "/home/tmlarson/connections/connect.php";
    
    $message = ""; // Initialize message variable
    $empty = true;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = isset($_POST["email"]) ? trim($_POST["email"]) : null;
        $password = isset($_POST["password"]) ? trim($_POST["password"]) : null;
        $password2 = isset($_POST["password2"]) ? trim($_POST["password2"]) : null;

        if (empty($email) || empty($password) || empty($password2)) {
            $empty = false;
            $message = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $empty = false;
            $message = "Invalid email format.";
        } elseif ($password !== $password2) {
            $empty = false;
            $message = "Passwords do not match.";
        } else {
            try {
                // Check if the user already exists
                $stmt = $conn->prepare("SELECT email FROM Users WHERE email = :email");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($existingUser) {
                    $empty = false;
                    $message = "User already exists.";
                } else {
                    // Insert new user
                    $stmt = $conn->prepare("INSERT INTO Users (email, password) VALUES (:email, :password)");
                    $stmt->bindParam(':email', $email);
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt->bindParam(':password', $hashed_password);
                    $stmt->execute();

                    header('Location: index.php');
                    exit;
                }

            } catch (PDOException $e) {
                $empty = false;
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
    <title>Signup - Inventory Manager</title>
    <link rel="stylesheet" href="CSS/stylesheet.css">
</head>
<body class="login-body">

    <div class="container">
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
        <h2>Sign Up</h2>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <label for="password2">Confirm Password:</label>
            <input type="password" id="password2" name="password2" required><br>
            <?php if (!$empty) { echo "<div class='error'>".$message."</div>"; } ?>
            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>
