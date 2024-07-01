<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Property Manager') {
    header('Location: user_login.php');
    exit();
}

$manager_id = $_SESSION['user_id'];
$property_id = $_GET['id'];

// Connect to the database
$db = new SQLite3('property_database.db');

try {
    // Enable exceptions for better error handling
    $db->enableExceptions(true);

    // Fetch property details and performance metrics
    $property_stmt = $db->prepare('SELECT property_name, owner_id, property_type, property_image FROM Properties WHERE property_id = :property_id AND manager_id = :manager_id');
    if (!$property_stmt) {
        throw new Exception("Failed to prepare statement: " . $db->lastErrorMsg());
    }

    $property_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
    $property_stmt->bindValue(':manager_id', $manager_id, SQLITE3_INTEGER);
    $property_result = $property_stmt->execute();
    if (!$property_result) {
        throw new Exception("Failed to execute statement: " . $db->lastErrorMsg());
    }
    $property = $property_result->fetchArray(SQLITE3_ASSOC);

    if (!$property) {
        throw new Exception("Property not found or you do not have permission to view this property.");
    }

    $metrics_stmt = $db->prepare('SELECT * FROM PerformanceMetrics WHERE property_id = :property_id ORDER BY timestamp DESC LIMIT 3');
    $metrics_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
    $metrics_result = $metrics_stmt->execute();
    $metrics = [];
    while ($metric = $metrics_result->fetchArray(SQLITE3_ASSOC)) {
        $metrics[] = $metric;
    }

    // Handle form submission for updating metrics
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_metrics'])) {
        // Initialize metric variables
        $rental_income = isset($_POST['rental_income']) ? $_POST['rental_income'] : NULL;
        $tips = isset($_POST['tips']) ? $_POST['tips'] : NULL;
        $operation_cost = isset($_POST['operation_cost']) ? $_POST['operation_cost'] : NULL;
        $maintenance_cost = isset($_POST['maintenance_cost']) ? $_POST['maintenance_cost'] : NULL;
        $unexpected_cost = isset($_POST['unexpected_cost']) ? $_POST['unexpected_cost'] : NULL;
        $profit = ($rental_income + $tips) - ($operation_cost + $maintenance_cost + $unexpected_cost);
        $vacancy_status = $_POST['vacancy_status'];
        $property_info = $_POST['property_info'];

        // Delete the oldest record if more than 2 records exist
        $count_stmt = $db->prepare('SELECT COUNT(*) as count FROM PerformanceMetrics WHERE property_id = :property_id');
        $count_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
        $count_result = $count_stmt->execute();
        $count = $count_result->fetchArray(SQLITE3_ASSOC)['count'];

        if ($count >= 3) {
            $delete_oldest_stmt = $db->prepare('DELETE FROM PerformanceMetrics WHERE property_id = :property_id ORDER BY timestamp ASC LIMIT 1');
            $delete_oldest_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
            $delete_oldest_stmt->execute();
        }

        // Insert new metrics with timestamp
        $insert_stmt = $db->prepare('
            INSERT INTO PerformanceMetrics 
            (property_id, rental_income, tips, operation_cost, maintenance_cost, unexpected_cost, profit, vacancy_status, property_info, timestamp)
            VALUES (:property_id, :rental_income, :tips, :operation_cost, :maintenance_cost, :unexpected_cost, :profit, :vacancy_status, :property_info, datetime("now"))
        ');

        $insert_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
        $insert_stmt->bindValue(':rental_income', $rental_income, SQLITE3_FLOAT);
        $insert_stmt->bindValue(':tips', $tips, SQLITE3_FLOAT);
        $insert_stmt->bindValue(':operation_cost', $operation_cost, SQLITE3_FLOAT);
        $insert_stmt->bindValue(':maintenance_cost', $maintenance_cost, SQLITE3_FLOAT);
        $insert_stmt->bindValue(':unexpected_cost', $unexpected_cost, SQLITE3_FLOAT);
        $insert_stmt->bindValue(':profit', $profit, SQLITE3_FLOAT);
        $insert_stmt->bindValue(':vacancy_status', $vacancy_status, SQLITE3_TEXT);
        $insert_stmt->bindValue(':property_info', $property_info, SQLITE3_TEXT);

        if ($insert_stmt->execute()) {
            header("Location: property_details.php?id=$property_id");
            exit();
        } else {
            echo "Failed to update metrics: " . $db->lastErrorMsg();
        }
    }

    // Handle message submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
        $message = $_POST['message'];
        $owner_id = $property['owner_id'];

        $message_stmt = $db->prepare('
            INSERT INTO Messages (property_id, sender_id, receiver_id, message)
            VALUES (:property_id, :sender_id, :receiver_id, :message)
        ');

        $message_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
        $message_stmt->bindValue(':sender_id', $manager_id, SQLITE3_INTEGER);
        $message_stmt->bindValue(':receiver_id', $owner_id, SQLITE3_INTEGER);
        $message_stmt->bindValue(':message', $message, SQLITE3_TEXT);

        if ($message_stmt->execute()) {
            header("Location: property_details.php?id=$property_id");
            exit();
        } else {
            echo "Failed to send message: " . $db->lastErrorMsg();
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Details - <?php echo htmlspecialchars($property['property_name']); ?></title>
    <style>
        body {
            background: url("<?php echo htmlspecialchars($property['property_image']); ?>") no-repeat center center fixed;
            background-size: cover;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .property-details {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 800px;
            overflow-y: auto;
            max-height: 90vh;
        }

        h2, h3 {
            color: #333;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input[type="text"], input[type="number"], textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 15px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .metrics-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .metrics-table th, .metrics-table td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: left;
        }

        .metrics-table th {
            background-color: #f2f2f2;
        }

        .messages {
            margin-top: 20px;
        }

        .messages h3 {
            margin-bottom: 10px;
        }

        .message {
            padding: 10px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
        }

        .message p {
            margin: 0;
        }

        .message .meta {
            color: #777;
            font-size: 0.9em;
        }
        
        .back-to-dashboard {
            position: fixed;
            bottom: 20px;
            left: 50%; /* Centers the button horizontally */
            transform: translateX(-50%);
        }

    </style>
</head>
<body>
    <div class="property-details">
        <h2>Property: <?php echo htmlspecialchars($property['property_name']); ?></h2>
        <form method="post">
            <h3>Performance Metrics</h3>

                <label for="rental_income">Rental Income:</label>
                <input type="number" step="0.01" id="rental_income" name="rental_income" value="<?php echo htmlspecialchars($metrics[0]['rental_income'] ?? ''); ?>" required>

                <label for="tips">Grants:</label>
                <input type="number" step="0.01" id="tips" name="tips" value="<?php echo htmlspecialchars($metrics[0]['tips'] ?? ''); ?>" required>


                <label for="operation_cost">Operation Cost:</label>
                <input type="number" step="0.01" id="operation_cost" name="operation_cost" value="<?php echo htmlspecialchars($metrics[0]['operation_cost'] ?? ''); ?>" required>

                <label for="maintenance_cost">Maintenance Cost:</label>
                <input type="number" step="0.01" id="maintenance_cost" name="maintenance_cost" value="<?php echo htmlspecialchars($metrics[0]['maintenance_cost'] ?? ''); ?>" required>


            <label for="unexpected_cost">Unexpected Cost:</label>
            <input type="number" step="0.01" id="unexpected_cost" name="unexpected_cost" value="<?php echo htmlspecialchars($metrics[0]['unexpected_cost'] ?? ''); ?>" required>

            <label for="vacancy_status">Vacancy Status:</label>
            <input type="text" id="vacancy_status" name="vacancy_status" value="<?php echo htmlspecialchars($metrics[0]['vacancy_status'] ?? ''); ?>" required>

            <label for="property_info">Property Info:</label>
            <textarea id="property_info" name="property_info" required><?php echo htmlspecialchars($metrics[0]['property_info'] ?? ''); ?></textarea>

            <button type="submit" name="update_metrics">Update Metrics</button>
        </form>

        <h3>Recent Performance Metrics</h3>
        <table class="metrics-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>Rental Income</th>
                    <th>Grants</th>
                    <th>Operation Cost</th>
                    <th>Maintenance Cost</th>
                    <th>Unexpected Cost</th>
                    <th>Profit</th>
                    <th>Vacancy Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($metrics as $metric): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($metric['timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($metric['rental_income'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($metric['tips'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($metric['operation_cost'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($metric['maintenance_cost'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($metric['unexpected_cost'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($metric['profit'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($metric['vacancy_status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="messages">
            <h3>Messages</h3>
            <?php
            // Fetch messages
            $message_stmt = $db->prepare('
                SELECT m.message, m.timestamp, u.username AS sender 
                FROM Messages m 
                JOIN Users u ON m.sender_id = u.user_id 
                WHERE m.property_id = :property_id 
                ORDER BY m.timestamp DESC
            ');
            $message_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
            $message_result = $message_stmt->execute();

            while ($message = $message_result->fetchArray(SQLITE3_ASSOC)) {
                echo "<div class='message'>
                        <p>" . htmlspecialchars($message['message']) . "</p>
                        <p class='meta'>Sent by " . htmlspecialchars($message['sender']) . " on " . htmlspecialchars($message['timestamp']) . "</p>
                      </div>";
            }
            ?>
            <form method="post">
                <label for="message">Send a message:</label>
                <textarea id="message" name="message" required></textarea>
                <button type="submit" name="send_message">Send</button>
            </form>
        </div>
    </div>
    <div class="back-to-dashboard">
            <button onclick="window.location.href='user_dashboard.php';">Back to Dashboard</button>
        </div>
    </div>
</body>
</html>
