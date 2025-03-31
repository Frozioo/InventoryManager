<?php
    require '/home/tmlarson/connections/connect.php';
    session_start();

    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }

    // Check if the user is an admin
    $adminCheck = "SELECT * FROM Admin WHERE admin_email = :email";
    $stmt = $conn->prepare($adminCheck);
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    if ($stmt->rowCount() == 0) {
        header('Location: adminPortal.php');
        exit;
    }

    // Delete user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_POST['user_id'];

        $stmtDelete = $conn->prepare("DELETE FROM Users WHERE user_id = :user_id");
        $stmtDelete->bindParam(':user_id', $user_id);
        $stmtDelete->execute();

        header('Location: adminPortal.php');
        exit;
    }
?>