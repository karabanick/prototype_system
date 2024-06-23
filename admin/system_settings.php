<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}

$db = new SQLite3('../property_database.db');

// Function to fetch a setting from the database
function getSetting($db, $key) {
    $stmt = $db->prepare('SELECT setting_value FROM Settings WHERE setting_key = ?');
    $stmt->bindValue(1, $key, SQLITE3_TEXT);
    $result = $stmt->execute();
    $row = $result->fetchArray(SQLITE3_ASSOC);
    return $row ? $row['setting_value'] : '';
}

// Function to fetch all settings from the database
function getAllSettings($db) {
    $result = $db->query('SELECT setting_key, setting_value FROM Settings');
    $settings = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $settings[] = $row;
    }
    return $settings;
}

// Function to update or insert a setting
function saveSetting($db, $key, $value) {
    $stmt = $db->prepare('INSERT INTO Settings (setting_key, setting_value) VALUES (?, ?)
                          ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value');
    $stmt->bindValue(1, $key, SQLITE3_TEXT);
    $stmt->bindValue(2, $value, SQLITE3_TEXT);
    $stmt->execute();
}

// Function to delete a setting
function deleteSetting($db, $key) {
    $stmt = $db->prepare('DELETE FROM Settings WHERE setting_key = ?');
    $stmt->bindValue(1, $key, SQLITE3_TEXT);
    $stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    if ($action == 'update') {
        saveSetting($db, $_POST['setting_key'], $_POST['setting_value']);
    } elseif ($action == 'add') {
        saveSetting($db, $_POST['new_setting_key'], $_POST['new_setting_value']);
    } elseif ($action == 'delete') {
        deleteSetting($db, $_POST['setting_key']);
    }
}

// Fetch all settings
$settings = getAllSettings($db);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Settings</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>System Settings</h1>
            <a href="../admin_dashboard.php" class="back">Back to Dashboard</a>
        </header>
        <main>
            <h2>Update Settings</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="update">
                <table>
                    <tr>
                        <th>Setting Key</th>
                        <th>Setting Value</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($settings as $setting): ?>
                    <tr>
                        <td>
                            <input type="text" name="setting_key" value="<?php echo htmlspecialchars($setting['setting_key']); ?>" readonly>
                        </td>
                        <td>
                            <input type="text" name="setting_value" value="<?php echo htmlspecialchars($setting['setting_value']); ?>" required>
                        </td>
                        <td>
                            <button type="submit">Update</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </form>

            <h2>Add New Setting</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="add">
                <label for="new_setting_key">Setting Key:</label>
                <input type="text" id="new_setting_key" name="new_setting_key" required>

                <label for="new_setting_value">Setting Value:</label>
                <input type="text" id="new_setting_value" name="new_setting_value" required>

                <button type="submit">Add</button>
            </form>

            <h2>Delete Setting</h2>
            <form method="post" action="">
                <input type="hidden" name="action" value="delete">
                <label for="delete_setting_key">Setting Key:</label>
                <select id="delete_setting_key" name="setting_key" required>
                    <option value="" disabled selected>Select a setting to delete</option>
                    <?php foreach ($settings as $setting): ?>
                    <option value="<?php echo htmlspecialchars($setting['setting_key']); ?>"><?php echo htmlspecialchars($setting['setting_key']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Delete</button>
            </form>
        </main>
    </div>
</body>
</html>
