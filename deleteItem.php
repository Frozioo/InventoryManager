<?php
    /* 
    * This allows the user to delete an item from their inventory.
    * It will delete the item from the database and redirect the user back to the dashboard.
    */
    session_start();
    require "/home/tmlarson/connections/connect.php";
    $itemID = $_GET['item_id'];

    
    $query = "delete from Inventory where item_id=?";
    $qr = $conn->prepare($query);
    $qr->execute([$itemID]);

    header('Location: dashboard.php?category_id=' . $_GET['category_id']);
    exit;
?>