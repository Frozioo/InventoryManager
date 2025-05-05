<?php
    require '/home/tmlarson/connections/connect.php';
    session_start();
    if(!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }

    // Check if the user is an admin
    $adminCheck = "SELECT * FROM Admin WHERE admin_email = :email";
    $stmt = $conn->prepare($adminCheck);
    $stmt->bindParam(':email', $_SESSION['email']);
    $stmt->execute();
    $is_admin = false;
    if ($stmt->rowCount() > 0) {
        $is_admin = true;
    }
    
    if (!$is_admin){
        header('Location: dashboard.php');
        exit;
    }


    /**
     * Fetch all images with status 'Pending' from the database
     * This will be used to display all pending images in the table
     * for the admin to approve or reject.
     * The admin can approve or reject the image by clicking the respective button.
     */
    $stmtPendingImages = $conn->prepare("SELECT * FROM Inventory WHERE image_status = 'Pending'");
    $stmtPendingImages->execute();
    $pendingImages = $stmtPendingImages->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Approval - Stockify</title>
    <link rel="stylesheet" href="CSS/stylesheet.css">
</head>
<body>
    <div class="container">
            <!-- Sidebar Navigation -->
            <nav class="sidebar">
                <h2>Admin Portal</h2>
                <ul>
                    <li><a href="adminPortal.php">Manage Users</a></li>
                    <li><a href="imageTable.php">Image Approval</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        <main class="content">
            <h2>Pending Images</h2>
                <table id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Image</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pendingImages as $item): ?>
                            <tr>
                                <td><?php echo $item['item_name']; ?></td>
                                <td>
                                    <img src="<?php echo $item['image_path']; ?>" alt="Pending Image" style="max-width: 100px; max-height: 100px;">
                                </td>
                                <td>
                                    <form method="POST" action="approveImage.php" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                        <button type="submit" class="approve-button">Approve</button>
                                    </form>
                                    <form method="POST" action="rejectImage.php" style="display:inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                        <button type="submit" class="reject-button">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
        </main>
    </div>

</body>
</html>