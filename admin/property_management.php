<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}

$db = new SQLite3('../property_database.db');

// Function to execute database queries for CRUD operations and handle errors
function executeQuery($db, $query) {
    $result = $db->query($query);
    if (!$result) {
        die("Database Query Error: " . $db->lastErrorMsg());
    }
    return $result;
}

// Fetching properties with manager and owner names displayed
$propertiesQuery = executeQuery($db, '
    SELECT Properties.property_id AS property_id, Properties.property_name AS property_name, Properties.location, 
           Managers.username AS manager_name, Owners.username AS owner_name
    FROM Properties
    LEFT JOIN Users AS Managers ON Properties.manager_id = Managers.user_id
    LEFT JOIN Users AS Owners ON Properties.owner_id = Owners.user_id
');
$properties = [];
while ($row = $propertiesQuery->fetchArray(SQLITE3_ASSOC)) {
    $properties[] = $row;
}

// Fetch property owners for assignment
$ownersQuery = executeQuery($db, 'SELECT user_id, username FROM Users WHERE role_id = "2"');
$owners = [];
while ($row = $ownersQuery->fetchArray(SQLITE3_ASSOC)) {
    $owners[] = $row;
}

// Fetch property managers for assignment
$managersQuery = executeQuery($db, 'SELECT user_id, username FROM Users WHERE role_id = "3"');
$managers = [];
while ($row = $managersQuery->fetchArray(SQLITE3_ASSOC)) {
    $managers[] = $row;
}

// Calculate total number of properties
$totalPropertiesQuery = executeQuery($db, 'SELECT COUNT(*) AS total_properties FROM Properties');
$totalProperties = $totalPropertiesQuery->fetchArray(SQLITE3_ASSOC)['total_properties'];

// Handle add/edit/delete properties
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    if ($action == 'add') {
        $name = $_POST['name'];
        $location = $_POST['location'];
        $owner_id = $_POST['owner_id'];

        if (empty($owner_id)) {
            die("Error: Owner must be selected.");
        }

        $stmt = $db->prepare('INSERT INTO Properties (property_name, location, owner_id) VALUES (?, ?, ?)');
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, $location, SQLITE3_TEXT);
        $stmt->bindValue(3, $owner_id, SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            die("Database Insert Error: " . $db->lastErrorMsg());
        }
    } elseif ($action == 'edit') {
        $property_id = $_POST['property_id'];
        $name = $_POST['name'];
        $location = $_POST['location'];
        $owner_id = $_POST['owner_id'];

        if (empty($owner_id)) {
            die("Error: Owner must be selected.");
        }

        $stmt = $db->prepare('UPDATE Properties SET property_name = ?, location = ?, owner_id = ? WHERE property_id = ?');
        $stmt->bindValue(1, $name, SQLITE3_TEXT);
        $stmt->bindValue(2, $location, SQLITE3_TEXT);
        $stmt->bindValue(3, $owner_id, SQLITE3_INTEGER);
        $stmt->bindValue(4, $property_id, SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            die("Database Update Error: " . $db->lastErrorMsg());
        }
    } elseif ($action == 'delete') {
        $property_id = $_POST['property_id'];
        
        // Error message for invalid property_id
        if (empty($property_id)) {
            die("Error: Invalid property ID.");
        }
        
        $stmt = $db->prepare('DELETE FROM Properties WHERE property_id = ?');
        $stmt->bindValue(1, $property_id, SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            die("Database Delete Error: " . $db->lastErrorMsg());
        }
    } elseif ($action == 'assign_manager') {
        $property_id = $_POST['property_id'];
        $manager_id = $_POST['manager_id'];

        if (empty($manager_id)) {
            die("Error: Manager must be selected.");
        }

        $stmt = $db->prepare('UPDATE Properties SET manager_id = ? WHERE property_id = ?');
        $stmt->bindValue(1, $manager_id, SQLITE3_INTEGER);
        $stmt->bindValue(2, $property_id, SQLITE3_INTEGER);
        if (!$stmt->execute()) {
            die("Database Update Error: " . $db->lastErrorMsg());
        }
    }
    header('Location: property_management.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Management</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background-image: url('../images/admin_background.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
            overflow-y: scroll; /* Enable vertical scroll */
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        main {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        .back {
            padding: 10px 20px;
            background-color: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .back:hover {
            background-color: #cc0000;
        }

        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgb(0,0,0); /* Fallback color */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            <h1>Property Management</h1>
            <a href="../admin_dashboard.php" class="back">Back to Dashboard</a>
        </header>
        <main>
            <h2>Total Properties: <?php echo $totalProperties; ?></h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Location</th>
                    <th>Manager</th>
                    <th>Owner</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($properties as $property): ?>
                <tr>
                    <td><?php echo htmlspecialchars($property['property_id']); ?></td>
                    <td><?php echo htmlspecialchars($property['property_name']); ?></td>
                    <td><?php echo htmlspecialchars($property['location']); ?></td>
                    <td><?php echo htmlspecialchars($property['manager_name'] ?: 'Unassigned'); ?></td>
                    <td><?php echo htmlspecialchars($property['owner_name'] ?: 'Unassigned'); ?></td>
                    <td>
                        <button onclick="openEditModal(<?php echo $property['property_id']; ?>, '<?php echo htmlspecialchars($property['property_name']); ?>', '<?php echo htmlspecialchars($property['location']); ?>', '<?php echo htmlspecialchars($property['owner_id']); ?>')">Edit</button>
                        <button onclick="openAssignManagerModal(<?php echo $property['property_id']; ?>)">Assign Manager</button>
                        <button onclick="openDeleteModal(<?php echo $property['property_id']; ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>

            <h2>Add Property</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="add">
                <input type="text" name="name" placeholder="Property Name" required>
                <input type="text" name="location" placeholder="Location" required>
                <label for="owner_id">Owner:</label>
                <select name="owner_id" required>
                    <option value="" disabled selected>Select an owner</option>
                    <?php foreach ($owners as $owner): ?>
                    <option value="<?php echo htmlspecialchars($owner['user_id']); ?>"><?php echo htmlspecialchars($owner['username']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Add</button>
            </form>

            <div id="editModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeEditModal()">&times;</span>
                    <h2>Edit Property</h2>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="property_id" id="editPropertyId">
                        <input type="text" name="name" id="editPropertyName" required>
                        <input type="text" name="location" id="editPropertyLocation" required>
                        <label for="owner_id">Owner:</label>
                        <select name="owner_id" id="editOwnerId" required>
                            <option value="" disabled selected>Select an owner</option>
                            <?php foreach ($owners as $owner): ?>
                            <option value="<?php echo htmlspecialchars($owner['user_id']); ?>"><?php echo htmlspecialchars($owner['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>

            <div id="assignManagerModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAssignManagerModal()">&times;</span>
                    <h2>Assign Manager</h2>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="assign_manager">
                        <input type="hidden" name="property_id" id="assignManagerPropertyId">
                        <label for="manager_id">Manager:</label>
                        <select name="manager_id" id="manager_id" required>
                            <option value="" disabled selected>Select a manager</option>
                            <?php foreach ($managers as $manager): ?>
                            <option value="<?php echo htmlspecialchars($manager['user_id']); ?>"><?php echo htmlspecialchars($manager['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit">Assign</button>
                    </form>
                </div>
            </div>

            <div id="deleteModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeDeleteModal()">&times;</span>
                    <h2>Delete Property</h2>
                    <form method="post" action="">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="property_id" id="deletePropertyId">
                        <p>Are you sure you want to delete this property?</p>
                        <button type="submit">Yes, Delete</button>
                        <button type="button" onclick="closeDeleteModal()">Cancel</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openEditModal(id, name, location, ownerId) {
            document.getElementById('editPropertyId').value = id;
            document.getElementById('editPropertyName').value = name;
            document.getElementById('editPropertyLocation').value = location;
            document.getElementById('editOwnerId').value = ownerId;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function openAssignManagerModal(id) {
            document.getElementById('assignManagerPropertyId').value = id;
            document.getElementById('assignManagerModal').style.display = 'block';
        }

        function closeAssignManagerModal() {
            document.getElementById('assignManagerModal').style.display = 'none';
        }

        function openDeleteModal(id) {
            document.getElementById('deletePropertyId').value = id;
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
        }
    </script>
</body>
</html>
