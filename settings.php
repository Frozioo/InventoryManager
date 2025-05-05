<?php
require '/home/tmlarson/connections/connect.php';
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

/** 
 * This allows the user to update their low stock threshold.
 * It will update the user's settings in the database and display a success message.
 */
$stmtThreshold = $conn->prepare("SELECT low_stock_threshold FROM UserSettings WHERE user_id = (SELECT user_id FROM Users WHERE email = :email)");
$stmtThreshold->bindParam(':email', $_SESSION['email']);
$stmtThreshold->execute();
$userSettings = $stmtThreshold->fetch(PDO::FETCH_ASSOC);
$currentThreshold = $userSettings['low_stock_threshold'] ?? 5;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $threshold = isset($_POST["threshold"]) ? (int)$_POST["threshold"] : null;

    if ($threshold !== null && $threshold >= 0) {
        try {
            $stmtUpdateThreshold = $conn->prepare("UPDATE UserSettings SET low_stock_threshold = :threshold WHERE user_id = (SELECT user_id FROM Users WHERE email = :email)");
            $stmtUpdateThreshold->bindParam(':threshold', $threshold);
            $stmtUpdateThreshold->bindParam(':email', $_SESSION['email']);
            $stmtUpdateThreshold->execute();

            $message = "Threshold updated successfully!";
        } catch (PDOException $e) {
            $message = "Error updating threshold: " . $e->getMessage();
        }
    } else {
        $message = "Please enter a valid threshold.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/stylesheet.css">
    <title>Settings - Stockify</title>
    <link rel="icon" href="assets/inventory-system.png">
</head>
<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h2>Stockify</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="settings.php" class="active">Settings</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <h5>Copyright © 2025 Trey Larson</h5>
        </nav>

        <main class="content">
            <h1>Settings</h1>
            <p>Update your settings below.</p>
            <br>
            <form class="settings-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                <label for="threshold">Low Stock Threshold:</label>
                <input type="number" id="threshold" name="threshold" value="<?php echo htmlspecialchars($currentThreshold); ?>" min="0" required>

                <button type="submit">Update Settings</button>

                <?php if (!empty($message)): ?>
                    <p class="message"><?php echo $message; ?></p>
                <?php endif; ?>
            </form>
            <!-- Delete Account Button -->
            <br>
            <h2>Delete Account</h2>
            <p>Sorry to see you go! If you wish to delete your account, please click the button below. This action cannot be undone.</p>
            <br>
            <form class="settings-form" action="deleteAccount.php" method="POST" onsubmit="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">
                <button type="submit">Delete Account</button>
            </form>
        </main>
    </div>
</body>
</html>