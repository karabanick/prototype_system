<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}
// Fetch and display audit logs from the database here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Logs</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Audit Logs</h1>
            <a href="../admin_dashboard.php" class="back">Back to Dashboard</a>
        </header>
        <main>
            <h2>Logs</h2>
            <table>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Timestamp</th>
                </tr>
                <!-- Loop through audit logs and display them here -->
            </table>
        </main>
    </div>
</body>
</html>
