<?php
require '/home/tmlarson/connections/connect.php';

session_start();
if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

if (isset($_POST['category_id'])) {
    $category_id = $_POST['category_id'];

    // Delete the category
    $stmtDeleteCategory = $conn->prepare('DELETE FROM InventoryCategories WHERE category_id = :category_id');
    $stmtDeleteCategory->bindParam(':category_id', $category_id);
    $stmtDeleteCategory->execute();

    // Redirect to the dashboard
    header('Location: dashboard.php');
    exit;
}
?>