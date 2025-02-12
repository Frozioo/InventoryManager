<?php
    require "/home/tmlarson/connections/connect.php";
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST["email"];
        $password = $_POST["password"];
        $password2 = $_POST["password2"];
        $empty = true;
        // Check if the email and password are empty
        if($password != $password2) {
            $empty = false;
            $message = "Passwords do not match.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM Users WHERE username = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $empty = false;
                $message = "Email already exists.";
            } else {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                // Insert the new user into the database
                $stmt = $conn->prepare("INSERT INTO Users (username, password) VALUES (:email, :password)");
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $hashedPassword);
                if ($stmt->execute()) {
                    session_start();
                    $_SESSION['email'] = $email;
                    header('Location: index.php');
                    exit();
                } else {
                    echo "Error: " . $stmt->errorInfo()[2];
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
    <title>Signup</title>
</head>
<body class="login-body">
    <div class="header">
        <h1>Inventory Manager</h1>
    </div>
    <div class="form-container">
        <h2>Sign Up</h2>
        <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <label for="password2">Confirm Password:</label>
            <input type="password" id="password2" name="password2" required><br>
            <?php if(!$empty) {echo "<div class='error'>".$message."</div>";} ?>
            <button type="submit">Sign Up</button>
        </form>
    </div>
</body>
</html>