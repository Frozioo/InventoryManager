<?php
require '/home/tmlarson/connections/connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    $stmt = $conn->prepare('UPDATE Inventory SET item_name = :item_name, description = :description, quantity = :quantity, price = :price, category_id = :category_id WHERE item_id = :item_id');
    $stmt->bindParam(':item_name', $item_name);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':category_id', $category_id);
    $stmt->bindParam(':item_id', $item_id);
    $stmt->execute();

    // Redirect back to the dashboard with the correct category_id
    header('Location: dashboard.php?category_id=' . $category_id);
    exit;
}
?>