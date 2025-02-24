<?php
    session_start();
    require "/home/tmlarson/connections/connect.php";
    $itemID = $_GET['item_id'];

    
    $query = "delete from Inventory where item_id=?";
    $qr = $conn->prepare($query);
    $qr->execute([$itemID]);

    header("Location: dashboard.php");
    exit;


?>