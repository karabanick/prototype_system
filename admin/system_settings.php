<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}
// Fetch and update settings from the database here
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
                <!-- Include various system settings here -->
                <label for="setting1">Setting 1:</label>
                <input type="text" id="setting1" name="setting1" value="">

                <label for="setting2">Setting 2:</label>
                <input type="text" id="setting2" name="setting2" value="">

                <button type="submit">Update</button>
            </form>
        </main>
    </div>
</body>
</html>
