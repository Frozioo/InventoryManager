<?php
    require '/home/tmlarson/connections/connect.php';

    session_start();
    if (!isset($_SESSION['email'])) {
        header('Location: index.php');
        exit;
    }

    /**
     * Preparing and executing a SQL query to select the user_id from the Users table.
     * This checks if the user is logged in by matching the email stored in the session.
    */
    $stmtUser = $conn->prepare("SELECT user_id FROM Users WHERE email = :email");
    $stmtUser->bindParam(':email', $_SESSION['email']);
    $stmtUser->execute();
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $user_id = $user['user_id'];

    /**
     * Preparing and executing a SQL query to select the category_id from the InventoryCategories table.
     * This checks if the user has any categories created by matching the user_id.
     * This also creates a category folder for the category if it does not exist.
     * If no categories are found, it redirects the user to the dashboard page.
     */
    if (isset($_POST['category_name'])) {
        $category_name = $_POST['category_name'];
    
        $stmtInsertCategory = $conn->prepare('INSERT INTO InventoryCategories (user_id, category_name) VALUES (:user_id, :category_name)');
        $stmtInsertCategory->bindParam(':user_id', $user_id);
        $stmtInsertCategory->bindParam(':category_name', $category_name);
        $stmtInsertCategory->execute();
    
        $category_id = $conn->lastInsertId();
    
        $categoryFolder = __DIR__ . "/uploads/category_$category_id";
        if (!file_exists($categoryFolder)) {
            mkdir($categoryFolder, 0777, true);
        }
    
        header('Location: dashboard.php');
        exit;
    }

    /** 
     * Handles the form submission for adding an item to the inventory. It
     * retrieves the item details like name, description, quantity, price, and category ID from the
     * POST request. It then checks if an image file was uploaded, validates its size, and moves it to
     * a specific directory based on the categories ID.
     */
    if (isset($_POST['item_name'])) {
        $item_name = $_POST['item_name'];
        $description = $_POST['description'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        $category_id = $_POST['category_id'];
    
        $imagePath = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            if ($_FILES['image']['size'] > 1048576) {
                $_SESSION['error'] = "The uploaded file exceeds the size limit of 1 MB.";
                header('Location: dashboard.php?category_id=' . $category_id);
                exit;
            }
            $uploadDir = __DIR__ . "/uploads/category_$category_id/";
            $imageName = basename($_FILES['image']['name']);
            $imagePath = $uploadDir . $imageName;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                die("Failed to upload image.");
            }

            $imagePath = "uploads/category_$category_id/" . $imageName;
        }
    
        $stmtInsertItem = $conn->prepare('INSERT INTO Inventory (user_id, category_id, item_name, description, quantity, price, image_path, image_status) VALUES (:user_id, :category_id, :item_name, :description, :quantity, :price, :image_path, "Pending")');
        $stmtInsertItem->bindParam(':user_id', $user_id);
        $stmtInsertItem->bindParam(':category_id', $category_id);
        $stmtInsertItem->bindParam(':item_name', $item_name);
        $stmtInsertItem->bindParam(':description', $description);
        $stmtInsertItem->bindParam(':quantity', $quantity);
        $stmtInsertItem->bindParam(':price', $price);
        $stmtInsertItem->bindParam(':image_path', $imagePath);
        $stmtInsertItem->execute();
    
        header('Location: dashboard.php?category_id=' . $category_id);
        exit;
    }

    $stmtCategories = $conn->prepare('SELECT * FROM InventoryCategories WHERE user_id = :user_id');
    $stmtCategories->bindParam(':user_id', $user_id);
    $stmtCategories->execute();
    $categories = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

    $selected_category_id = isset($_GET['category_id']) ? $_GET['category_id'] : $categories[0]['category_id'];

    /**
     * Preparing and executing a SQL query to select items from the Inventory table.
     * It retrieves all items for the given user ID and category ID.
     * The query joins the Inventory table with the InventoryCategories table to get the category name.
     */
    $stmtItems = $conn->prepare('SELECT Inventory.*, InventoryCategories.category_name FROM Inventory JOIN InventoryCategories
     ON Inventory.category_id = InventoryCategories.category_id WHERE Inventory.user_id = :user_id AND Inventory.category_id = :category_id');
    $stmtItems->bindParam(':user_id', $user_id);
    $stmtItems->bindParam(':category_id', $selected_category_id);
    $stmtItems->execute();
    $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

    /**
     * Preparing and executing a SQL query to select the low stock threshold from the
     * UserSettings table. It retrieves the low stock threshold for the given user ID.
     * If no threshold is found, it defaults to 5.
     */
    $stmtThreshold = $conn->prepare("SELECT low_stock_threshold FROM UserSettings WHERE user_id = :user_id");
    $stmtThreshold->bindParam(':user_id', $user_id);
    $stmtThreshold->execute();
    $userSettings = $stmtThreshold->fetch(PDO::FETCH_ASSOC);
    $low_stock_threshold = $userSettings['low_stock_threshold'] ?? 5;

    /**
     * Preparing and executing a SQL query to select items with low stock from the
     * Inventory table. It checks if a category ID is selected, and
     * if so, it retrieves items with a quantity below a specified threshold for the given user and
     * category. If no category ID is selected, an empty array is assigned to $low_stock_items.
     */
    if (isset($selected_category_id)) {
        $stmtLowStock = $conn->prepare("SELECT item_name, quantity FROM Inventory WHERE user_id = :user_id AND category_id = :category_id AND quantity < :threshold");
        $stmtLowStock->bindParam(':user_id', $user_id);
        $stmtLowStock->bindParam(':category_id', $selected_category_id);
        $stmtLowStock->bindParam(':threshold', $low_stock_threshold);
        $stmtLowStock->execute();
        $low_stock_items = $stmtLowStock->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $low_stock_items = [];
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="CSS/stylesheet.css">
    <link rel="icon" href="assets/inventory-system.png">
    <title>Dashboard - Stockify</title>
</head>
<body>
    <div class="container">
            <!-- /**
             * Top navbar that is meant for the low stock items.
             * Only appears when the user is on the dashboard page.
             * It contains a button that, when clicked, will show the low stock items in a dropdown menu.
             */ -->
            <div class="top-navbar">
                <div class="low-stock-container">
                    <button id="lowStockButton" class="low-stock-btn" onclick="toggleLowStockDropdown()">
                    <svg viewBox="0 0 448 512" class="bell"><path d="M224 0c-17.7 0-32 14.3-32 32V49.9C119.5 61.4 64 124.2 64 200v33.4c0 45.4-15.5 89.5-43.8 124.9L5.3 377c-5.8 7.2-6.9 17.1-2.9 25.4S14.8 416 24 416H424c9.2 0 17.6-5.3 21.6-13.6s2.9-18.2-2.9-25.4l-14.9-18.6C399.5 322.9 384 278.8 384 233.4V200c0-75.8-55.5-138.6-128-150.1V32c0-17.7-14.3-32-32-32zm0 96h8c57.4 0 104 46.6 104 104v33.4c0 47.9 13.9 94.6 39.7 134.6H72.3C98.1 328 112 281.3 112 233.4V200c0-57.4 46.6-104 104-104h8zm64 352H224 160c0 17 6.7 33.3 18.7 45.3s28.3 18.7 45.3 18.7s33.3-6.7 45.3-18.7s18.7-28.3 18.7-45.3z"></path></svg>
                    </button>
                    <div id="lowStockDropdown" class="low-stock-dropdown-content">
                        <?php if (!empty($low_stock_items)): ?>
                            <ul>
                                <?php foreach ($low_stock_items as $item): ?>
                                    <li><?php echo $item['item_name']; ?>: Only <?php echo $item['quantity']; ?> left in stock!</li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p>No items are below the low stock threshold.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <!-- Sidebar Navigation -->
            <nav class="sidebar">
            <h2>Stockify</h2>
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
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
                <h5>Copyright © 2025 Trey Larson</h5>
            </nav>

            <main class="content">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="error-message">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                <h1>Dashboard</h1>
                <!-- Display which category the user is currently in -->
                <h2><?php echo $categories[array_search($selected_category_id, array_column($categories, 'category_id'))]['category_name']; ?></h2>
                <p> Manage your inventory below </p>

                <!-- Contains all the buttons used above the dashboard -->
                <!-- Search field, allows the user to search for items within the table -->
                <div class="button-container">
                    <div class="group">
                    <svg viewBox="0 0 24 24" aria-hidden="true" class="icon2">
                        <g>
                        <path
                            d="M21.53 20.47l-3.66-3.66C19.195 15.24 20 13.214 20 11c0-4.97-4.03-9-9-9s-9 4.03-9 9 4.03 9 9 9c2.215 0 4.24-.804 5.808-2.13l3.66 3.66c.147.146.34.22.53.22s.385-.073.53-.22c.295-.293.295-.767.002-1.06zM3.5 11c0-4.135 3.365-7.5 7.5-7.5s7.5 3.365 7.5 7.5-3.365 7.5-7.5 7.5-7.5-3.365-7.5-7.5z"
                        ></path>
                        </g>
                    </svg>
                    <input id="searchInput" class="input" type="search" onkeyup="filterTable()" placeholder="Search" />
                    </div>
                    <!-- Sorting button -->
                    <div class="sorting-container">
                        <button title="filter" class="filter" onclick="toggleSortDropdown()">
                        <svg viewBox="0 0 512 512" height="1em">
                            <path
                            d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"
                            ></path>
                        </svg>
                        </button>
                        <div id="sortDropdown" class="sort-dropdown-content">
                            <a href="javascript:void(0)" onclick="sortTable(3, 'asc')">Quantity (Asc)</a>
                            <a href="javascript:void(0)" onclick="sortTable(3, 'desc')">Quantity (Desc)</a>
                            <a href="javascript:void(0)" onclick="sortTable(4, 'asc')">Price (Asc)</a>
                            <a href="javascript:void(0)" onclick="sortTable(4, 'desc')">Price (Desc)</a>
                        </div>
                    </div>
                    <!-- Allows the user to add a category -->
                    <button id="openCategoryModal" class="button">
                        <span class="text">Add Category</span>
                        <span class="icon">
                            <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg"></svg>
                            <span class="buttonSpan">+</span>
                        </span>
                    </button>
                    <!-- Allows the user to add an item to the table -->
                    <?php if (!empty($categories)): ?>
                        <button id="openItemModal" class="button">
                            <span class="text">Add Item</span>
                            <span class="icon">
                                <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg"></svg>
                                <span class="buttonSpan">+</span>
                            </span>
                        </button>
                    <?php endif; ?>
                    <!-- Lets the user delete a category if they so desire -->
                    <form action="deleteCategory.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this category?')">
                        <input type="hidden" name="category_id" value="<?php echo $selected_category_id; ?>">
                        <button type="submit" class="delbtn">
                            <svg viewBox="0 0 448 512" class="svgIcon">
                                <path d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z"></path>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- /**
                 * Allows users to add a category to the dashboard. It includes a modal that
                 * contains a close button, a text input field for entering the category name, and a submit
                 * button for adding the category. When the form is submitted, it will send the data to
                 * "dashboard.php" using the POST method. 
                 */ -->
                <div id="categoryModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <form method="POST" action="dashboard.php">
                            <label for="category_name">Category Name:</label>
                            <input type="text" id="category_name" name="category_name" required>
                            <button class="category-button" type="submit">Add Category</button>
                        </form>
                    </div>
                </div>

                <!-- /** 
                 * Allows users to add an item to the dashboard. It includes input fields for item name, description, quantity, price,
                 * category, and an option to upload an image. The form is submitted to "dashboard.php"
                 * using the POST method with enctype set to "multipart/form-data" for handling file
                 * uploads. The category options are populated dynamically using PHP foreach loop based
                 * on the array. The form has validation for required fields and limits on
                 * input lengths and file size. 
                 */ -->
                <div id="itemModal" class="modal">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <form method="POST" action="dashboard.php" enctype="multipart/form-data">
                            <label for="item_name">Item Name:</label>
                            <input type="text" id="item_name" name="item_name" required maxlength="40"><br><br>
                            
                            <label for="description">Description:</label>
                            <input type="text" id="description" name="description" maxlength="78"><br><br>
                            
                            <label for="quantity">Quantity:</label>
                            <input type="number" id="quantity" name="quantity" required><br><br>
                            
                            <label for="price">Price:</label>
                            <input type="number" step="0.01" id="price" name="price" required><br><br>
                            
                            <label for="category_id">Category:</label>
                            <select id="category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" <?php echo $category['category_id'] == $selected_category_id ? 'selected' : ''; ?>>
                                        <?php echo $category['category_name']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select><br><br>

                            <label for="image">Upload Image (Max: 1 MB):</label>
                            <input type="file" id="image" name="image" accept="image/*"><br><br>

                            <button class="item-button" type="submit">Add Item</button>
                        </form>
                    </div>
                </div>
                <table id="inventoryTable">
                    <tr>
                        <!-- /** 
                         * Creates a table that displays the items in the user's inventory. 
                         * It includes columns for the item image, item name, description, quantity, price, and actions.
                         */ -->
                        <th>Image</th>
                        <th>Item</th>
                        <th class="description">Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th class="actions">Actions</th>
                    </tr>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <!-- /** 
                             * This code displays the image of the item if it exists and is approved. If the image is not approved,
                             * it displays a message indicating that the image is pending approval. If there is no image, it displays "N/A". 
                             */ -->
                            <td>
                                <?php if (!empty($item['image_path'])): ?>
                                    <?php if ($item['image_status'] === 'Approved'): ?>
                                        <img src="<?php echo $item['image_path']; ?>" alt="Item Image" style="max-width: 100px; max-height: 100px;">
                                    <?php else: ?>
                                        <p>Image Pending Approval</p>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <p>N/A</p>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $item['item_name']; ?></td>
                            <td class="description"><?php echo $item['description']; ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo $item['price']; ?></td>
                            <td class="actions">
                                    <!-- /**
                                     * This button allows the user to edit an item, when clicked, it opens a
                                     * window that allows the user to edit the item.
                                     */ -->
                                    <button class="edit-btn" onclick="openEditModal(<?php echo htmlspecialchars(json_encode($item)); ?>)">
                                        <svg class="edit-svgIcon" viewBox="0 0 512 512">
                                            <path d="M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1v32c0 8.8 7.2 16 16 16h32zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z"></path>
                                        </svg>
                                    </button>

                                    <!-- /** 
                                     * Form that allows the user to delete an item. When the form is submitted, it sends
                                     * a GET request to "deleteItem.php" with the item_id and category_id as hidden input values. The form
                                     * also includes a confirmation dialog using JavaScript's confirm function to ask the user if they are
                                     * sure they want to delete the item. If the user confirms, the form will proceed with the deletion
                                     * process.
                                     */ -->
                                    <form action="deleteItem.php" method="GET" onsubmit="return confirm('Are you sure you want to delete this item?')" style="display: inline;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                                        <input type="hidden" name="category_id" value="<?php echo $selected_category_id; ?>"> <!-- Pass the current category_id -->
                                        <button class="btn3">
                                            <svg viewBox="0 0 15 17.5" height="17.5" width="15" xmlns="http://www.w3.org/2000/svg" class="icon3">
                                                <path transform="translate(-2.5 -1.25)" d="M15,18.75H5A1.251,1.251,0,0,1,3.75,17.5V5H2.5V3.75h15V5H16.25V17.5A1.251,1.251,0,0,1,15,18.75ZM5,5V17.5H15V5Zm7.5,10H11.25V7.5H12.5V15ZM8.75,15H7.5V7.5H8.75V15ZM12.5,2.5h-5V1.25h5V2.5Z" id="Fill"></path>
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <!-- /** 
                 * Allows users to edit an item. It includes input fields for item name, description, quantity, 
                 * price, category, and an optional image upload. When the user submits the form, the data is sent 
                 * to "editItem.php" using the POST method.
                 */ -->
                <div id="editItemModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <form method="POST" action="editItem.php" enctype="multipart/form-data">
                            <input type="hidden" id="edit_item_id" name="item_id">
                            <input type="hidden" id="edit_category_id_hidden" name="category_id"> <!-- Hidden input for category_id -->

                            <label for="edit_item_name">Item Name:</label>
                            <input type="text" id="edit_item_name" name="item_name" required maxlength="40"><br><br>

                            <label for="edit_description">Description:</label>
                            <input type="text" id="edit_description" name="description" maxlength="78"><br><br>

                            <label for="edit_quantity">Quantity:</label>
                            <input type="number" id="edit_quantity" name="quantity" required><br><br>

                            <label for="edit_price">Price:</label>
                            <input type="number" step="0.01" id="edit_price" name="price" required><br><br>

                            <label for="edit_category_id">Category:</label>
                            <select id="edit_category_id" name="category_id" required>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                                <?php endforeach; ?>
                            </select><br><br>

                            <label for="edit_image">Upload New Image (Optional, Max: 1 MB):</label>
                            <input type="file" id="edit_image" name="image" accept="image/*"><br><br>

                            <button class="item-button" type="submit">Save Changes</button>
                        </form>
                    </div>
                </div>
            </main>
    </div>

 <!-- /** 
  * Function that toggles the visibility of a dropdown menu with low
  * stock items. When the function toggleLowStockDropdown() is called, it finds the dropdown element
  * with the id lowStockDropdown and toggles the show class
  */ -->
    <script>
        function toggleLowStockDropdown() {
            const dropdown = document.getElementById("lowStockDropdown");
            dropdown.classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.low-stock-btn')) {
                const dropdowns = document.getElementsByClassName("low-stock-dropdown-content");
                for (let i = 0; i < dropdowns.length; i++) {
                    const openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        };
    </script>
    

 <!-- /**
  * Functions for toggling a sort dropdown, sorting a table, and updating delete
  * buttons dynamically in a web application.
  */ -->
    <script>
        function toggleSortDropdown() {
            document.getElementById("sortDropdown").classList.toggle("show");
        }

        window.onclick = function(event) {
            if (!event.target.matches('.filter')) {
                var dropdowns = document.getElementsByClassName("sort-dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
        // This allows the user to sort the table
        function sortTable(columnIndex, order) {
        var table, rows, switching, i, x, y, shouldSwitch;
        table = document.getElementById("inventoryTable");
        switching = true;

        while (switching) {
            switching = false;
            rows = table.rows;

            for (i = 1; i < (rows.length - 1); i++) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName("TD")[columnIndex];
                y = rows[i + 1].getElementsByTagName("TD")[columnIndex];

                if (order === "asc") {
                    if (parseFloat(x.innerHTML) > parseFloat(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (order === "desc") {
                    if (parseFloat(x.innerHTML) < parseFloat(y.innerHTML)) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }

            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
            }
        }

        updateDeleteButtons();
    }
        // This allows the user to delete and edit the items, ensuring the buttons update dynamically.
        function updateDeleteButtons() {
            var table = document.getElementById("inventoryTable");
            var rows = table.getElementsByTagName("tr");
            var deleteButtonsContainer = document.querySelector(".delete-buttons");
            deleteButtonsContainer.innerHTML = "";

            for (var i = 1; i < rows.length; i++) {
                var itemId = rows[i].getElementsByTagName("input")[0].value;
                var itemName = rows[i].getElementsByTagName("td")[0].innerText;
                var description = rows[i].getElementsByTagName("td")[1].innerText;
                var quantity = rows[i].getElementsByTagName("td")[2].innerText;
                var price = rows[i].getElementsByTagName("td")[3].innerText;

                var deleteButtonRow = document.createElement("div");
                deleteButtonRow.className = "delete-button-row";

                // Edit Button
                var editButton = document.createElement("button");
                editButton.className = "edit-btn";
                editButton.onclick = (function(item) {
                    return function() {
                        openEditModal(item);
                    };
                })({
                    item_id: itemId,
                    item_name: itemName,
                    description: description,
                    quantity: quantity,
                    price: price,
                    category_id: document.getElementById("edit_category_id").value
                });

                var editSvg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                editSvg.setAttribute("viewBox", "0 0 512 512");
                editSvg.classList.add("edit-svgIcon");

                var editPath = document.createElementNS("http://www.w3.org/2000/svg", "path");
                editPath.setAttribute("d", "M410.3 231l11.3-11.3-33.9-33.9-62.1-62.1L291.7 89.8l-11.3 11.3-22.6 22.6L58.6 322.9c-10.4 10.4-18 23.3-22.2 37.4L1 480.7c-2.5 8.4-.2 17.5 6.1 23.7s15.3 8.5 23.7 6.1l120.3-35.4c14.1-4.2 27-11.8 37.4-22.2L387.7 253.7 410.3 231zM160 399.4l-9.1 22.7c-4 3.1-8.5 5.4-13.3 6.9L59.4 452l23-78.1c1.4-4.9 3.8-9.4 6.9-13.3l22.7-9.1v32c0 8.8 7.2 16 16 16h32zM362.7 18.7L348.3 33.2 325.7 55.8 314.3 67.1l33.9 33.9 62.1 62.1 33.9 33.9 11.3-11.3 22.6-22.6 14.5-14.5c25-25 25-65.5 0-90.5L453.3 18.7c-25-25-65.5-25-90.5 0zm-47.4 168l-144 144c-6.2 6.2-16.4 6.2-22.6 0s-6.2-16.4 0-22.6l144-144c6.2-6.2 16.4-6.2 22.6 0s6.2 16.4 0 22.6z");

                editSvg.appendChild(editPath);
                editButton.appendChild(editSvg);
                deleteButtonRow.appendChild(editButton);

                // Delete Button
                var form = document.createElement("form");
                form.action = "deleteItem.php";
                form.method = "GET";
                form.onsubmit = function() {
                    return confirm('Are you sure you want to delete this item?');
                };

                var input = document.createElement("input");
                input.type = "hidden";
                input.name = "item_id";
                input.value = itemId;

                var button = document.createElement("button");
                button.type = "submit";
                button.className = "btn3";

                var svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
                svg.setAttribute("viewBox", "0 0 15 17.5");
                svg.setAttribute("height", "17.5");
                svg.setAttribute("width", "15");
                svg.setAttribute("xmlns", "http://www.w3.org/2000/svg");
                svg.classList.add("icon3");

                var path = document.createElementNS("http://www.w3.org/2000/svg", "path");
                path.setAttribute("transform", "translate(-2.5 -1.25)");
                path.setAttribute("d", "M15,18.75H5A1.251,1.251,0,0,1,3.75,17.5V5H2.5V3.75h15V5H16.25V17.5A1.251,1.251,0,0,1,15,18.75ZM5,5V17.5H15V5Zm7.5,10H11.25V7.5H12.5V15ZM8.75,15H7.5V7.5H8.75V15ZM12.5,2.5h-5V1.25h5V2.5Z");
                path.setAttribute("id", "Fill");

                svg.appendChild(path);
                button.appendChild(svg);

                form.appendChild(input);
                form.appendChild(button);
                deleteButtonRow.appendChild(form);

                deleteButtonsContainer.appendChild(deleteButtonRow);
            }
        }
    </script>

<!-- /**
 * Function that enables the user to search for items in an inventory
 * table. When the user types a search query into an input field with the id "searchInput", the
 * function filters the rows of the table with the id "inventoryTable" based on the search query. It
 * iterates through each row of the table, skipping the header row, and then iterates through each
 * column of the row to check if the text content of the cell contains the search query. If a match is
 * found, the row is displayed, otherwise, the row is hidden. 
 */ -->
    <script>
        // Allows the user to be able to search for items in their inventory table
        function filterTable() {
            var input, filter, table, tr, td, i, j, txtValue, found;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("inventoryTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td");
                found = false;

                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            found = true;
                            break;
                        }
                    }
                }

                if (found) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    </script>

<!-- /** 
 * Functions to toggle a dropdown menu, open
 * and close a window for categories, and open and close a window for items. 
 */ -->
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

        var modal = document.getElementById("categoryModal");
        var btn = document.getElementById("openCategoryModal");
        var span = document.getElementsByClassName("close")[0];

        btn.onclick = function() {
            modal.style.display = "block";
        }

        span.onclick = function() {
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        var modal2 = document.getElementById("itemModal");
        var btn2 = document.getElementById("openItemModal");
        var span2 = document.getElementsByClassName("close")[1];

        btn2.onclick = function() {
            modal2.style.display = "block";
        }

        span2.onclick = function() {
            modal2.style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == modal) {
                modal2.style.display = "none";
            }
        }
    </script>

 <!-- /**
  * The script defines functions to open and close a window modal for editing the item details
  */ -->
    <script>
        function openEditModal(item) {
            document.getElementById("edit_item_id").value = item.item_id;
            document.getElementById("edit_item_name").value = item.item_name;
            document.getElementById("edit_description").value = item.description;
            document.getElementById("edit_quantity").value = item.quantity;
            document.getElementById("edit_price").value = item.price;
            document.getElementById("edit_category_id").value = item.category_id;
            document.getElementById("edit_category_id_hidden").value = item.category_id;

            document.getElementById("editItemModal").style.display = "block";
        }

        function closeEditModal() {
            document.getElementById("editItemModal").style.display = "none";
        }

        window.onclick = function(event) {
            var modal = document.getElementById("editItemModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>

</body>
</html>