<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: index.php');
    exit();
}

// Connect to the database
$db = new SQLite3('property_database.db');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            overflow: auto; /* Enable both vertical and horizontal scroll */
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .widget {
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .flex-row {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }

        .half-width {
            flex: 1 1 48%;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body class="admin-dashboard">
    <div class="dashboard-container admin-dashboard">
        <header>
            <img src="images/logo.png" alt="Logo" class="logo">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            <a href="admin_logout.php" class="logout">Logout</a>
        </header>
        <nav>
            <ul>
                <li><a href="admin/property_management.php">Property Management</a></li>
                <li><a href="admin/user_management.php">User Management</a></li>
                <li><a href="admin/system_settings.php">System Settings</a></li>
                <li><a href="admin/reports.php">Reports</a></li>
                <li><a href="admin/financial_dashboard.php">Financial Dashboard</a></li>
            </ul>
        </nav>
        <main>
            <div class="content">
                <!-- System Overview Section -->
                <h2>System Overview</h2>
                <p>Here you can manage users, properties, and system settings, view reports, audit logs, and more.</p>

                <div class="widget half-width">
                    <h3>User Overview</h3>
                    <table id="user-overview">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Load user overview here -->
                        </tbody>
                    </table>
                    <p>Total Users: <span id="total-users"></span></p>
                </div>

                <div class="widget half-width">
                    <h3>Property Overview</h3>
                    <table id="property-overview">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Location</th>
                                <th>Owner</th>
                                <th>Manager</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Load property overview here -->
                        </tbody>
                    </table>
                </div>            

                <div class="widget half-width">
                    <h3>System Notifications</h3>
                    <ul id="notifications">
                        <!-- Load notifications here -->
                    </ul>
                </div>

                <div class="widget">
                    <h3>Financial Dashboard</h3>
                    <p>Total Revenue: $<span id="total-revenue"></span></p>
                    <p>Total Expenses: $<span id="total-expenses"></span></p>
                    <p>Total Profit: $<span id="total-profit"></span></p>
                </div>
            </div>
        </main>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Load user overview
            fetch('api/get_user_overview.php')
                .then(response => response.json())
                .then(data => {
                    const userList = document.getElementById('user-overview').querySelector('tbody');
                    data.users.forEach(user => {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td>${user.username}</td><td>${user.role}</td>`;
                        userList.appendChild(row);
                    });
                    document.getElementById('total-users').textContent = data.totalUsers;
                });

            // Load property overview
            fetch('api/get_property_overview.php')
                .then(response => response.json())
                .then(data => {
                    const propertyList = document.getElementById('property-overview').querySelector('tbody');
                    data.properties.forEach(property => {
                        const row = document.createElement('tr');
                        row.innerHTML = `<td>${property.name}</td><td>${property.type}</td><td>${property.location}</td><td>${property.owner}</td><td>${property.manager}</td><td>${property.status}</td>`;
                        propertyList.appendChild(row);
                    });
                });

            // Load recent activities
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

            // Load notifications
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

            // Load financial data
            fetch('api/get_financial_data.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total-revenue').textContent = data.totalRevenue;
                    document.getElementById('total-expenses').textContent = data.totalExpenses;
                    document.getElementById('total-profit').textContent = data.totalProfit;
                });
        });
    </script>
</body>
</html>
