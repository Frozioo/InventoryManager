<?php
    require '/home/tmlarson/connections/connect.php';

    session_start();
    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }

    // Check if the user is logged in
    $stmtUser = $conn->prepare("SELECT user_id FROM Users WHERE email = :email");
    $stmtUser->bindParam(':email', $_SESSION['email']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $user_id = $user['user_id'];

    // Handle category form submission
    if (isset($_POST['category_name'])) {
        $category_name = $_POST['category_name'];

        $stmtInsertCategory = $conn->prepare('INSERT INTO InventoryCategories (user_id, category_name) VALUES (:user_id, :category_name)');
        $stmtInsertCategory->bindParam(':user_id', $user_id);
        $stmtInsertCategory->bindParam(':category_name', $category_name);
        $stmtInsertCategory->execute();

        // Redirect to avoid form resubmission
        header('Location: dashboard.php');
        exit;
    }

    // Handle item form submission
    if (isset($_POST['item_name'])) {
        $item_name = $_POST['item_name'];
        $description = $_POST['description'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];

        $stmtInsertItem = $conn->prepare('INSERT INTO Inventory (user_id, category_id, item_name, description, quantity, price) VALUES (:user_id, :category_id, :item_name, :description, :quantity, :price)');
        $stmtInsertItem->bindParam(':user_id', $user_id);
        $stmtInsertItem->bindParam(':category_id', $category_id);
        $stmtInsertItem->bindParam(':item_name', $item_name);
        $stmtInsertItem->bindParam(':description', $description);
        $stmtInsertItem->bindParam(':quantity', $quantity);
        $stmtInsertItem->bindParam(':price', $price);
        $stmtInsertItem->execute();

        // Redirect to avoid form resubmission
        header('Location: dashboard.php');
        exit;
    }

    // Fetch categories
    $stmtCategories = $conn->prepare('SELECT * FROM InventoryCategories WHERE user_id = :user_id');
    $stmtCategories->bindParam(':user_id', $user_id);
    $stmtCategories->execute();
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

    // Fetch selected category
    $selected_category_id = isset($_GET['category_id']) ? $_GET['category_id'] : $categories[0]['category_id'];

    // Fetch items for the selected category
    $stmtItems = $conn->prepare('SELECT Inventory.*, InventoryCategories.category_name FROM Inventory JOIN InventoryCategories ON Inventory.category_id = InventoryCategories.category_id WHERE Inventory.user_id = :user_id AND Inventory.category_id = :category_id');
    $stmtItems->bindParam(':user_id', $user_id);
    $stmtItems->bindParam(':category_id', $selected_category_id);
    $stmtItems->execute();
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/stylesheet.css">
    <title>Dashboard - Inventory Manager</title>
</head>
<body>

<div class="container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar">
           <h2>Inventory Manager</h2>
            <ul>
                <li class="dropdown">
                    <a href="javascript:void(0)" class="dropbtn" onclick="toggleDropdown()">
                        Dashboard 
                        <img id="dropdownArrow" src="assets/drop-down-arrow.png" alt="Dropdown Arrow" class="dropdown-arrow">
                    </a>
                    <ul class="dropdown-content" id="categoryDropdown">
                        <?php foreach ($categories as $category): ?>
                            <li><a href="dashboard.php?category_id=<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
            <h5>&copy; 2025 Trey Larson</h5>
        </nav>

        <!-- Main Content -->
        <main class="content">
            <h1>Dashboard</h1>
            <p>View your inventory below.</p>

            <!-- Form to add new inventory category -->
            <form method="POST" action="dashboard.php">
                <label for="category_name">Category Name:</label>
                <input type="text" id="category_name" name="category_name" required>
                <button type="submit">Add Category</button>
            </form>

            <!-- Form to add new inventory item -->
            <form method="POST" action="dashboard.php">
                <label for="item_name">Item Name:</label>
                <input type="text" id="item_name" name="item_name" required>
                
                <label for="description">Description:</label>
                <textarea id="description" name="description"></textarea>
                
                <label for="quantity">Quantity:</label>
                <input type="number" id="quantity" name="quantity" required>
                
                <label for="price">Price:</label>
                <input type="number" step="0.01" id="price" name="price" required>
                
                <label for="category_id">Category:</label>
                <select id="category_id" name="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit">Add Item</button>
            </form>

            <table>
                <tr>
                    <th>Item</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Category</th>
                </tr>

                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['item_name']; ?></td>
                        <td><?php echo $item['description']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['price']; ?></td>
                        <td><?php echo $item['category_name']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </main>
    </div>
    <script>
        function toggleDropdown() {
            var dropdown = document.getElementById("categoryDropdown");
            var arrow = document.getElementById("dropdownArrow");

            if (dropdown.style.display === "block") {
                dropdown.style.display = "none";
                arrow.style.transform = "rotate(0deg)";
            } else {
                dropdown.style.display = "block";
                arrow.style.transform = "rotate(180deg)";
            }
        }
    </script>

</body>
</html>