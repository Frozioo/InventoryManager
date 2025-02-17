<?php
    require "/home/tmlarson/connections/connect.php";
    
    $message = "";
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
                <li><a href="login.php">Login</a></li>
                <li><a href="signup.php" class="active">Sign Up</a></li>
            </ul>
            <h5>Copyright © 2025 Trey Larson</h5>
        </nav>

        <!-- Main Content -->
        <main class="content">
            <h1>Sign Up</h1>
            <p>Create your account to start managing your inventory.</p>

            <!-- Signup Form -->
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>

                <label for="password2">Confirm Password:</label>
                <input type="password" id="password2" name="password2" required>

                <div id="password-error" class="error"></div>

                <button type="submit">Sign Up</button>

                <?php if (!empty($message)): ?>
                    <p class="error"><?php echo $message; ?></p>
                <?php endif; ?>
            </form>
        </main>
    </div>

    <!-- JavaScript for Live Password Validation -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const password = document.getElementById("password");
            const password2 = document.getElementById("password2");
            const errorDiv = document.getElementById("password-error");

            password2.addEventListener("input", function() {
                if (password.value !== password2.value) {
                    errorDiv.textContent = "Passwords do not match!";
                    errorDiv.style.color = "red";
                } else {
                    errorDiv.textContent = "";
                }
            });
        });
    </script>

</body>
</html>
