<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}
$db = new SQLite3('../property_database.db');

// Handle create, update, delete actions by admin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    if ($action == 'create') {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        $stmt = $db->prepare('INSERT INTO Users (username, password, role) VALUES (?, ?, ?)');
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, $password, SQLITE3_TEXT);
        $stmt->bindValue(3, $role, SQLITE3_TEXT);
        $stmt->execute();
    } elseif ($action == 'update') {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $role = $_POST['role'];
        $stmt = $db->prepare('UPDATE Users SET username = ?, role = ? WHERE id = ?');
        $stmt->bindValue(1, $username, SQLITE3_TEXT);
        $stmt->bindValue(2, $role, SQLITE3_TEXT);
        $stmt->bindValue(3, $user_id, SQLITE3_INTEGER);
        $stmt->execute();
    } elseif ($action == 'delete') {
        $user_id = $_POST['user_id'];
        $stmt = $db->prepare('DELETE FROM Users WHERE id = ?');
        $stmt->bindValue(1, $user_id, SQLITE3_INTEGER);
        $stmt->execute();
    }
}

$result = $db->query('SELECT * FROM Users');
$users = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $users[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="../style.css">
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
                <input type="password" name="password" placeholder="Password" required>
                <select name="role">
                    <option value="Admin">Admin</option>
                    <option value="Property Owner">Property Owner</option>
                    <option value="Property Manager">Property Manager</option>
                </select>
                <button type="submit">Create</button>
            </form>

            <h2>Existing Users</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                    <td>
                        <form method="post" action="" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">
                            <button type="submit">Delete</button>
                        </form>
                        <button onclick="openEditModal(<?php echo $user['id']; ?>, '<?php echo $user['username']; ?>', '<?php echo $user['role']; ?>')">Edit</button>
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
                        <select name="role" id="editRole">
                            <option value="Admin">Admin</option>
                            <option value="Property Owner">Property Owner</option>
                            <option value="Property Manager">Property Manager</option>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openEditModal(id, username, role) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUsername').value = username;
            document.getElementById('editRole').value = role;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
    </script>
</body>
</html>
