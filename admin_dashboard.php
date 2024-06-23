<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <a href="logout.php" class="logout">Logout</a>
        </header>
        <nav>
            <ul>
                <li><a href="admin/property_management.php">Property Management</a></li>
                <li><a href="admin/user_management.php">User Management</a></li>
                <li><a href="admin/system_settings.php">System Settings</a></li>
                <li><a href="admin/reports.php">Reports</a></li>
                <li><a href="admin/audit_logs.php">Audit Logs</a></li>
            </ul>
        </nav>
        <main>
            <div class="content">
                <!-- Placeholder for System Overview, Recent Activities & Notifications -->
                <h2>System Overview</h2>
                <p>Here you can manage users, configure system settings, view reports, and audit logs.</p>
                <div class="widget">
                    <h3>User Overview</h3>
                    <p>Total Users: <span id="total-users"></span></p>
                </div>
                <div class="widget">
                    <h3>Recent Activities</h3>
                    <ul id="recent-activities">
                        <!--  load recent activities here -->
                    </ul>
                </div>
                <div class="widget">
                    <h3>System Notifications</h3>
                    <ul id="notifications">
                        <!-- load notifications here -->
                    </ul>
                </div>
            </div>
        </main>
    </div>
    <script>
        // JavaScript to load data 
        document.addEventListener("DOMContentLoaded", () => {
            // Example of fetching data using Javascsript and APIs
            fetch('api/get_user_count.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total-users').textContent = data.count;
                });

            fetch('api/get_recent_activities.php')
                .then(response => response.json())
                .then(data => {
                    const activityList = document.getElementById('recent-activities');
                    data.activities.forEach(activity => {
                        const li = document.createElement('li');
                        li.textContent = activity.description;
                        activityList.appendChild(li);
                    });
                });

            fetch('api/get_notifications.php')
                .then(response => response.json())
                .then(data => {
                    const notificationsList = document.getElementById('notifications');
                    data.notifications.forEach(notification => {
                        const li = document.createElement('li');
                        li.textContent = notification.message;
                        notificationsList.appendChild(li);
                    });
                });
        });
    </script>
</body>
</html>
