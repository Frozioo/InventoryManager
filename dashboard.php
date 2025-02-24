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
        header('Location: dashboard.php?category_id=' . $category_id);
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
    $stmtItems = $conn->prepare('SELECT Inventory.*, InventoryCategories.category_name FROM Inventory JOIN InventoryCategories
     ON Inventory.category_id = InventoryCategories.category_id WHERE Inventory.user_id = :user_id AND Inventory.category_id = :category_id');
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

                <div class="sorting-container">
                    <button title="filter" class="filter" onclick="toggleSortDropdown()">
                    <svg viewBox="0 0 512 512" height="1em">
                        <path
                        d="M0 416c0 17.7 14.3 32 32 32l54.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 448c17.7 0 32-14.3 32-32s-14.3-32-32-32l-246.7 0c-12.3-28.3-40.5-48-73.3-48s-61 19.7-73.3 48L32 384c-17.7 0-32 14.3-32 32zm128 0a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zM320 256a32 32 0 1 1 64 0 32 32 0 1 1 -64 0zm32-80c-32.8 0-61 19.7-73.3 48L32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l246.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48l54.7 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-54.7 0c-12.3-28.3-40.5-48-73.3-48zM192 128a32 32 0 1 1 0-64 32 32 0 1 1 0 64zm73.3-64C253 35.7 224.8 16 192 16s-61 19.7-73.3 48L32 64C14.3 64 0 78.3 0 96s14.3 32 32 32l86.7 0c12.3 28.3 40.5 48 73.3 48s61-19.7 73.3-48L480 128c17.7 0 32-14.3 32-32s-14.3-32-32-32L265.3 64z"
                        ></path>
                    </svg>
                    </button>
                    <div id="sortDropdown" class="sort-dropdown-content">
                        <a href="javascript:void(0)" onclick="sortTable(2, 'asc')">Quantity (Asc)</a>
                        <a href="javascript:void(0)" onclick="sortTable(2, 'desc')">Quantity (Desc)</a>
                        <a href="javascript:void(0)" onclick="sortTable(3, 'asc')">Price (Asc)</a>
                        <a href="javascript:void(0)" onclick="sortTable(3, 'desc')">Price (Desc)</a>
                    </div>
                </div>

                <button id="openCategoryModal" class="button">
                    <span class="text">Add Category</span>
                    <span class="icon">
                        <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg"></svg>
                        <span class="buttonSpan">+</span>
                    </span>
                </button>

                <button id="openItemModal" class="button">
                    <span class="text">Add Item</span>
                    <span class="icon">
                        <svg viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg"></svg>
                        <span class="buttonSpan">+</span>
                    </span>
                </button>
            </div>

            <!-- The Modal -->
            <div id="categoryModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <form method="POST" action="dashboard.php">
                        <label for="category_name">Category Name:</label>
                        <input type="text" id="category_name" name="category_name" required>
                        <button type="submit">Add Category</button>
                    </form>
                </div>
            </div>


            <!-- The Modal -->
            <div id="itemModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <form method="POST" action="dashboard.php">
                        <label for="item_name">Item Name:</label>
                        <input type="text" id="item_name" name="item_name" required><br><br>
                        
                        <label for="description">Description:</label>
                        <input id="description" name="description"></input><br><br>
                        
                        <label for="quantity">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" required><br><br>
                        
                        <label for="price">Price:</label>
                        <input type="number" step="0.01" id="price" name="price" required><br><br>
                        
                        <label for="category_id">Category:</label>
                        <select id="category_id" name="category_id" required>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>"><?php echo $category['category_name']; ?></option>
                            <?php endforeach; ?>
                        </select><br><br>
                        
                        <button type="submit">Add Item</button>
                    </form>
                </div>
            </div>
            <!-- Table to display inventory items -->
                                        
            <table id="inventoryTable">
                <tr>
                    <th>Item</th>
                    <th class="description">Description</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <!-- <th>Category</th> -->
                </tr>

                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo $item['item_name']; ?></td>
                        <td class="description"><?php echo $item['description']; ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td><?php echo $item['price']; ?></td>
                        <!-- <td><?php echo $item['category_name']; ?></td> -->
                    </tr>
                <?php endforeach; ?>
            </table>
        </main>
</div>
    

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
        }
    </script>

    <script>
        function filterTable() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("inventoryTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        }
    </script>

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
        // Create a function that allows the user to click on a button and it opens
        // a window that allows the user to create a category and add it to the database
        // Create a function that allows the user to click on a button and it opens
        // a window that allows the user to create an item and add it to the database

        // Get the modal
        var modal = document.getElementById("categoryModal");

        // Get the button that opens the modal
        var btn = document.getElementById("openCategoryModal");

        // Get the <span> element that closes the modal
        var span = document.getElementsByClassName("close")[0];

        // When the user clicks the button, open the modal 
        btn.onclick = function() {
            modal.style.display = "block";
        }

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Get the modal
        var modal2 = document.getElementById("itemModal");
        // Get the button that opens the modal
        var btn2 = document.getElementById("openItemModal");
        // Get the <span> element that closes the modal
        var span2 = document.getElementsByClassName("close")[1];
        // When the user clicks the button, open the modal
        btn2.onclick = function() {
            modal2.style.display = "block";
        }
        // When the user clicks on <span> (x), close the modal
        span2.onclick = function() {
            modal2.style.display = "none";
        }
        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal2.style.display = "none";
            }
        }


    </script>

</body>
</html>