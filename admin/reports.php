<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}
// Generate and display reports from the database here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Reports</h1>
            <a href="../admin_dashboard.php" class="back">Back to Dashboard</a>
        </header>
        <main>
            <h2>Generate Reports</h2>
            <form method="post" action="">
                <!-- Include options for generating reports -->
                <label for="reportType">Report Type:</label>
                <select id="reportType" name="reportType">
                    <option value="user_activity">User Activity</option>
                    <option value="system_usage">System Usage</option>
                    <!-- Add other report types as needed -->
                </select>
                <button type="submit">Generate</button>
            </form>

            <h2>Report Results</h2>
            <!-- Display the generated report here -->
        </main>
    </div>
</body>
</html>
