<?php
require '/home/tmlarson/connections/connect.php';

session_start();

if (!isset($_SESSION['email'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $category_id = $_POST['category_id'];

    /**
     * Handles the form submission for editing an item.
     * If the user uploads a new image, it will be saved to the server and the old image will be deleted.
     * If the user does not upload a new image, the old image will remain unchanged.
     * The item is updated in the database and the user is redirected to the dashboard.
     */
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // A check to make sure the uploaded file must be less than 1 MB, since anything over 1 MB makes the image take a while to load on the dashboard
        if ($_FILES['image']['size'] > 1048576) {
            $_SESSION['error'] = "The uploaded file exceeds the size limit of 1 MB.";
            header('Location: dashboard.php?category_id=' . $category_id);
            exit;
        }

        $stmtGetImage = $conn->prepare('SELECT image_path FROM Inventory WHERE item_id = :item_id');
        $stmtGetImage->bindParam(':item_id', $item_id);
        $stmtGetImage->execute();
        $currentImage = $stmtGetImage->fetch(PDO::FETCH_ASSOC)['image_path'];

        $uploadDir = __DIR__ . "/uploads/category_$category_id/";
        $imageName = basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            die("Failed to upload image.");
        }

        // Delete the old image if it exists
        if (!empty($currentImage) && file_exists(__DIR__ . '/' . $currentImage)) {
            unlink(__DIR__ . '/' . $currentImage);
        }

        $imagePath = "uploads/category_$category_id/" . $imageName;
    }

    // Update the item in the database
    $stmtUpdateItem = $conn->prepare('UPDATE Inventory SET item_name = :item_name, description = :description, quantity = :quantity, price = :price, category_id = :category_id' . ($imagePath ? ', image_path = :image_path' : '') . ' WHERE item_id = :item_id');
    $stmtUpdateItem->bindParam(':item_name', $item_name);
    $stmtUpdateItem->bindParam(':description', $description);
    $stmtUpdateItem->bindParam(':quantity', $quantity);
    $stmtUpdateItem->bindParam(':price', $price);
    $stmtUpdateItem->bindParam(':category_id', $category_id);
    $stmtUpdateItem->bindParam(':item_id', $item_id);

    if ($imagePath) {
        $stmtUpdateItem->bindParam(':image_path', $imagePath);
    }

    $stmtUpdateItem->execute();

    header('Location: dashboard.php?category_id=' . $category_id);
    exit;
}
?>