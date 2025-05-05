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

/**
 * Allows the admin to reject an image for an item.
 * It will update the image status to 'Rejected' in the database
 * and redirect the admin back to the image approval page.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_id = $_POST['item_id'];

    $stmtGetImage = $conn->prepare("SELECT image_path FROM Inventory WHERE item_id = :item_id");
    $stmtGetImage->bindParam(':item_id', $item_id);
    $stmtGetImage->execute();
    $imagePath = $stmtGetImage->fetch(PDO::FETCH_ASSOC)['image_path'];

    if (!empty($imagePath) && file_exists(__DIR__ . '/' . $imagePath)) {
        unlink(__DIR__ . '/' . $imagePath);
    }

    // Delete the item from the database
    $stmtDelete = $conn->prepare("DELETE FROM Inventory WHERE item_id = :item_id");
    $stmtDelete->bindParam(':item_id', $item_id);
    $stmtDelete->execute();

    header('Location: imageTable.php');
    exit;
}
?>