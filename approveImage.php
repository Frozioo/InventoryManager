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
if ($stmt->rowCount() === 0) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];

    $stmtApprove = $conn->prepare("UPDATE Inventory SET image_status = 'Approved' WHERE item_id = :item_id");
    $stmtApprove->bindParam(':item_id', $item_id);
    $stmtApprove->execute();

    header('Location: imageTable.php');
    exit;
}
?>