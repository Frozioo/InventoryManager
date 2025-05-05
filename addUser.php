<?php
    /**
     * This page allows the admin to add a new user to the system.
     * It will insert the user into the database and redirect the admin back to the admin portal.
     */
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

    // Add new user
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        $stmtInsert = $conn->prepare("INSERT INTO Users (email, password) VALUES (:email, :password)");
        $stmtInsert->bindParam(':email', $email);
        $stmtInsert->bindParam(':password', $password);
        $stmtInsert->execute();

        header('Location: adminPortal.php');
        exit;
    }
?>