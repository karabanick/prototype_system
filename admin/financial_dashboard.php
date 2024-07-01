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
    <title>Financial Dashboard</title>
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
            box-sizing: border-box;
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            overflow-x: auto; /* Ensure horizontal scroll for tables */
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

        @media (max-width: 1200px) {
            .dashboard-container {
                padding: 10px;
                width: 100%;
            }

            table {
                display: block;
                overflow-x: auto; /* Add horizontal scroll for smaller screens */
            }

            th, td {
                padding: 5px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Financial Dashboard</h1>
            <a href="../admin_dashboard.php" class="back">Back to Dashboard</a>
        </header>
        <main>
            <h2>Financial Metrics</h2>
            <table id="financial-metrics">
                <thead>
                    <tr>
                        <th>Property Name</th>
                        <th>Rental Income</th>
                        <th>Tips</th>
                        <th>Operational Cost</th>
                        <th>Maintenance Cost</th>
                        <th>Unexpected Cost</th>
                        <th>Profit</th>
                        <th>System Operations (5%)</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Load financial metrics here -->
                </tbody>
            </table>
            <h3>Total System Operations: $<span id="total-system-operations"></span></h3>
        </main>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Fetch financial metrics
            fetch('../api/get_financial_metrics.php')
                .then(response => response.json())
                .then(data => {
                    const metricsTable = document.getElementById('financial-metrics').querySelector('tbody');
                    data.metrics.forEach(metric => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${metric.property_name}</td>
                            <td>$${metric.rental_income.toFixed(2)}</td>
                            <td>$${metric.tips.toFixed(2)}</td>
                            <td>$${metric.operational_cost.toFixed(2)}</td>
                            <td>$${metric.maintenance_cost.toFixed(2)}</td>
                            <td>$${metric.unexpected_cost.toFixed(2)}</td>
                            <td>$${metric.profit.toFixed(2)}</td>
                            <td>$${metric.system_operations.toFixed(2)}</td>
                        `;
                        metricsTable.appendChild(row);
                    });
                    document.getElementById('total-system-operations').textContent = data.totalSystemOperations.toFixed(2);
                });
        });
    </script>
</body>
</html>
