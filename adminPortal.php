<?php
    /**
     * This page allows the admin to manage users.
     * It will display all users in a table and allow the admin to add or delete users.
     * It will also allow the admin to view the image approval page.
     */

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
     * Fetch all users from the database
     * This will be used to display all users in the table
     * for the admin to view and manage.
     */
    $stmtUsers = $conn->prepare("SELECT * FROM Users");
    $stmtUsers->execute();
    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Portal - Stockify</title>
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
                <li><a href="javascript:void(0)" onclick="openAddUserModal()">Add User</a></li>
                <li><a href="javascript:void(0)" onclick="openDeleteUserModal()">Delete User</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="content">
            <h1>Manage Users</h1>

            <!-- Display all users in a table -->
            <table border="1">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Email</th>
                        <th>Creation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['user_id']; ?></td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['created_at']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Add User Modal -->
    <div id="addUserModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeAddUserModal()">&times;</span>
            <h2>Add New User</h2>
            <br>
            <form class="login-form" method="POST" action="addUser.php">
                <label for="new_email">Email:</label>
                <input type="email" id="new_email" name="email" required><br><br>
                
                <label for="new_password">Password:</label>
                <input type="password" id="new_password" name="password" required><br><br>
                
                <button class="item-button" type="submit">Add User</button>
            </form>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div id="deleteUserModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close" onclick="closeDeleteUserModal()">&times;</span>
            <h2>Delete User</h2>
            <form class="login-form" method="POST" action="deleteUser.php">
                <label for="user_id">Select User:</label>
                <select id="user_id" name="user_id" required>
                    <?php foreach ($users as $user): ?>
                        <option value="<?php echo $user['user_id']; ?>"><?php echo $user['email']; ?></option>
                    <?php endforeach; ?>
                </select><br><br>
                <button class="item-button" type="submit" onclick="return confirm('Are you sure you want to delete this user?');">Delete</button>
            </form>
        </div>
    </div>

    <!-- /**
    * Functions to open and close windows for adding and deleting users.
    */ -->
    <script>
        // Add User Modal
        function openAddUserModal() {
            document.getElementById("addUserModal").style.display = "block";
        }

        function closeAddUserModal() {
            document.getElementById("addUserModal").style.display = "none";
        }

        // Delete User Modal
        function openDeleteUserModal() {
            document.getElementById("deleteUserModal").style.display = "block";
        }

        function closeDeleteUserModal() {
            document.getElementById("deleteUserModal").style.display = "none";
        }

        window.onclick = function(event) {
            var addModal = document.getElementById("addUserModal");
            var deleteModal = document.getElementById("deleteUserModal");
            if (event.target == addModal) {
                addModal.style.display = "none";
            }
            if (event.target == deleteModal) {
                deleteModal.style.display = "none";
            }
        }
    </script>
</body>
</html>