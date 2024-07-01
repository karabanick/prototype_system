<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header('Location: ../index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            overflow: auto; /* Enable both vertical and horizontal scroll */
            background-image: url('../images/admin_background.jpg'); /* Add background image */
            background-size: cover;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            background: rgba(255, 255, 255, 0.9); /* Slightly transparent background */
            border-radius: 10px;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
        }

        .back {
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
        }

        main {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }

        th, td {
            white-space: nowrap; /* Prevent text from wrapping */
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Reports</h1>
            <a href="../admin_dashboard.php" class="back">Back to Dashboard</a>
        </header>
        <main>
            <h2>Generate Reports</h2>
            <form id="report-form">
                <label for="reportType">Report Type:</label>
                <select id="reportType" name="reportType">
                    <option value="user_activity">User Activity</option>
                </select>
                <button type="submit">Generate</button>
            </form>

            <h2>Report Results</h2>
            <div id="report-results"></div>
        </main>
    </div>
    <script>
        document.getElementById('report-form').addEventListener('submit', function(event) {
            event.preventDefault();

            // Fetch the report data from the API
            fetch('../api/get_activity_report.php')
                .then(response => response.json())
                .then(data => {
                    const resultsContainer = document.getElementById('report-results');
                    resultsContainer.innerHTML = '';

                    if (data.activities) {
                        const table = document.createElement('table');
                        const thead = document.createElement('thead');
                        const tbody = document.createElement('tbody');

                        thead.innerHTML = `
                            <tr>
                                <th>Username</th>
                                <th>Activity</th>
                                <th>Timestamp</th>
                            </tr>
                        `;
                        table.appendChild(thead);

                        data.activities.forEach(activity => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                                <td>${activity.username}</td>
                                <td>${activity.description}</td>
                                <td>${activity.timestamp}</td>
                            `;
                            tbody.appendChild(row);
                        });

                        table.appendChild(tbody);
                        resultsContainer.appendChild(table);
                    } else {
                        resultsContainer.innerHTML = `<p>${data.message}</p>`;
                    }
                });
        });
    </script>
</body>
</html>
