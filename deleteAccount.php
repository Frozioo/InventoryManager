<?php
require '/home/tmlarson/connections/connect.php';
session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

try {
    // Get the user ID based on the session email
    $stmtGetUserId = $conn->prepare("SELECT user_id FROM Users WHERE email = :email");
    $stmtGetUserId->bindParam(':email', $_SESSION['email']);
    $stmtGetUserId->execute();
    $user = $stmtGetUserId->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['user_id'];

        // Delete the user's data from related tables
        $stmtDeleteInventory = $conn->prepare("DELETE FROM Inventory WHERE category_id IN (SELECT category_id FROM InventoryCategories WHERE user_id = :user_id)");
        $stmtDeleteInventory->bindParam(':user_id', $user_id);
        $stmtDeleteInventory->execute();

        $stmtDeleteCategories = $conn->prepare("DELETE FROM InventoryCategories WHERE user_id = :user_id");
        $stmtDeleteCategories->bindParam(':user_id', $user_id);
        $stmtDeleteCategories->execute();

        $stmtDeleteSettings = $conn->prepare("DELETE FROM UserSettings WHERE user_id = :user_id");
        $stmtDeleteSettings->bindParam(':user_id', $user_id);
        $stmtDeleteSettings->execute();

        // Finally, delete the user account
        $stmtDeleteUser = $conn->prepare("DELETE FROM Users WHERE user_id = :user_id");
        $stmtDeleteUser->bindParam(':user_id', $user_id);
        $stmtDeleteUser->execute();

        // Destroy the session and redirect to the login page
        session_destroy();
        header('Location: index.php');
        exit;
    } else {
        throw new Exception("User not found.");
    }
} catch (Exception $e) {
    echo "Error deleting account: " . $e->getMessage();
}
?>