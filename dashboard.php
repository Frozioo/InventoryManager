<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
            <h2>Inventory Manager</h2>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="login.php" class="active">Login</a></li>
                <li><a href="signup.php">Sign Up</a></li>
            </ul>
            <h5>Copyright © 2025 Trey Larson</h5>
        </nav>

        <!-- Main Content -->
        <!-- Displays table of the inventory information from the inventory database -->
        <main class="content">
            <h1>Dashboard</h1>
            <p>View your inventory below.</p>

            <table>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>

                <?php
                    require '/home/tmlarson/connections/connect.php';

                    $stmt = $conn->prepare('SELECT * FROM Inventory');
                    $stmt->execute();

                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<tr>";
                        echo "<td>" . $row['item_name'] . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>" . $row['quantity'] . "</td>";
                        echo "<td>" . $row['price'] . "</td>";
                        echo "</tr>";
                    }
                ?>
            </table>


</body>
</html>