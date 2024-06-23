<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}

$db = new SQLite3('../property_database.db');

// Function to execute queries and handle errors
function executeQuery($db, $query) {
    $result = $db->query($query);
    if (!$result) {
        die("Database Query Error: " . $db->lastErrorMsg());
    }
    return $result;
}

// Handle create, update, delete actions by admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    if ($action == 'create') {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role_id = $_POST['role_id'];
        $stmt = $db->prepare('INSERT INTO Users (username, email, password, role_id) VALUES (?, ?, ?, ?)');
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, $email, SQLITE3_TEXT);
        $stmt->bindValue(3, $password, SQLITE3_TEXT);
        $stmt->bindValue(4, $role_id, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($action == 'update') {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $email = $_POST['email'];
        $role_id = $_POST['role_id'];
        $stmt = $db->prepare('UPDATE Users SET username = ?, email = ?, role_id = ? WHERE user_id = ?');
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, $email, SQLITE3_TEXT);
        $stmt->bindValue(3, $role_id, SQLITE3_INTEGER);
        $stmt->bindValue(4, $user_id, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($action == 'update_password') {
        $user_id = $_POST['user_id'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE Users SET password = ? WHERE user_id = ?');
        $stmt->bindValue(1, $password, SQLITE3_TEXT);
        $stmt->bindValue(2, $user_id, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($action == 'delete') {
        $user_id = $_POST['user_id'];
        $stmt = $db->prepare('DELETE FROM Users WHERE user_id = ?');
        $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

// Fetch users with role names
$usersQuery = executeQuery($db, '
    SELECT Users.user_id AS user_id, Users.username AS username, Users.email AS email, Roles.role_name AS role_name, Users.role_id AS role_id
    FROM Users
    LEFT JOIN Roles ON Users.role_id = Roles.role_id
');
$users = [];
while ($row = $usersQuery->fetchArray(SQLITE3_ASSOC)) {
    $users[] = $row;
}

// Fetch roles for the role selection
$rolesQuery = executeQuery($db, 'SELECT role_id, role_name FROM Roles');
$roles = [];
while ($row = $rolesQuery->fetchArray(SQLITE3_ASSOC)) {
    $roles[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        /* Add some styles for modal */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0,0,0);
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>User Management</h1>
            <a href="../admin_dashboard.php" class="back">Back to Dashboard</a>
        </header>
        <main>
            <h2>Create User</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="create">
                <input type="text" name="username" placeholder="Username" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <label for="role_id">Role:</label>
                <select name="role_id" required>
                    <option value="" disabled selected>Select a role</option>
                    <?php foreach ($roles as $role): ?>
                    <option value="<?php echo htmlspecialchars($role['role_id']); ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Create</button>
            </form>

            <h2>Existing Users</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['user_id']); ?>">
                            <button type="submit">Delete</button>
                        </form>
                        <button onclick="openEditModal(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars(addslashes($user['username'])); ?>', '<?php echo htmlspecialchars(addslashes($user['email'])); ?>', '<?php echo htmlspecialchars($user['role_id']); ?>')">Edit</button>
                        <button onclick="openPasswordModal(<?php echo $user['user_id']; ?>)">Change Password</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2>Edit User</h2>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="user_id" id="editUserId">
                        <input type="text" name="username" id="editUsername" required>
                        <input type="email" name="email" id="editEmail" required>
                        <label for="editRoleId">Role:</label>
                        <select name="role_id" id="editRoleId" required>
                            <option value="" disabled selected>Select a role</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo htmlspecialchars($role['role_id']); ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>

            <div id="passwordModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closePasswordModal()">&times;</span>
                    <h2>Change Password</h2>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="update_password">
                        <input type="hidden" name="user_id" id="passwordUserId">
                        <input type="password" name="password" placeholder="New Password" required>
                        <button type="submit">Update Password</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openEditModal(id, username, email, roleId) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            document.getElementById('editRoleId').value = roleId;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openPasswordModal(id) {
            document.getElementById('passwordUserId').value = id;
            document.getElementById('passwordModal').style.display = 'block';
        }

        function closePasswordModal() {
            document.getElementById('passwordModal').style.display = 'none';
        }
    </script>
</body>
</html>
