<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Property Owner') {
    header('Location: user_login.php');
    exit();
}

$owner_id = $_SESSION['user_id'];
$property_id = $_GET['id'];

// Connect to the database
$db = new SQLite3('property_database.db');

try {
    // Enable exceptions for better error handling
    $db->enableExceptions(true);

    // Fetch property details and performance metrics
    $property_stmt = $db->prepare('SELECT property_name, manager_id, property_image FROM Properties WHERE property_id = :property_id AND owner_id = :owner_id');
    if (!$property_stmt) {
        throw new Exception("Failed to prepare statement: " . $db->lastErrorMsg());
    }

    $property_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
    $property_stmt->bindValue(':owner_id', $owner_id, SQLITE3_INTEGER);
    $property_result = $property_stmt->execute();
    if (!$property_result) {
        throw new Exception("Failed to execute statement: " . $db->lastErrorMsg());
    }
    $property = $property_result->fetchArray(SQLITE3_ASSOC);

    if (!$property) {
        throw new Exception("Property not found or you do not have permission to view this property.");
    }

    // Fetch performance metrics
    $metrics_stmt = $db->prepare('SELECT rental_income, tips, operation_cost, maintenance_cost, unexpected_cost, profit, vacancy_status, property_info, timestamp FROM PerformanceMetrics WHERE property_id = :property_id ORDER BY rowid DESC LIMIT 3');
    $metrics_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
    $metrics_result = $metrics_stmt->execute();
    $metrics = [];
    while ($metric = $metrics_result->fetchArray(SQLITE3_ASSOC)) {
        $metrics[] = $metric;
    }

    // Fetch manager's contact info
    $manager_stmt = $db->prepare('SELECT username, email FROM Users WHERE user_id = :manager_id');
    $manager_stmt->bindValue(':manager_id', $property['manager_id'], SQLITE3_INTEGER);
    $manager_result = $manager_stmt->execute();
    $manager = $manager_result->fetchArray(SQLITE3_ASSOC);

    // Handle note submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_note'])) {
        $note = $_POST['note'];

        $note_stmt = $db->prepare('INSERT INTO Notes (property_id, owner_id, note) VALUES (:property_id, :owner_id, :note)');
        $note_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
        $note_stmt->bindValue(':owner_id', $owner_id, SQLITE3_INTEGER);
        $note_stmt->bindValue(':note', $note, SQLITE3_TEXT);

        if ($note_stmt->execute()) {
            header("Location: owner_property_details.php?id=$property_id");
            exit();
        } else {
            echo "Failed to save note: " . $db->lastErrorMsg();
        }
    }

    // Handle message submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
        $message = $_POST['message'];

        $message_stmt = $db->prepare('INSERT INTO Messages (property_id, sender_id, receiver_id, message) VALUES (:property_id, :sender_id, :receiver_id, :message)');
        $message_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
        $message_stmt->bindValue(':sender_id', $owner_id, SQLITE3_INTEGER);
        $message_stmt->bindValue(':receiver_id', $property['manager_id'], SQLITE3_INTEGER);
        $message_stmt->bindValue(':message', $message, SQLITE3_TEXT);

        if ($message_stmt->execute()) {
            header("Location: owner_property_details.php?id=$property_id");
            exit();
        } else {
            echo "Failed to send message: " . $db->lastErrorMsg();
        }
    }

    // Fetch messages sent by the manager to the owner
    $received_messages_stmt = $db->prepare('SELECT message, timestamp FROM Messages WHERE property_id = :property_id AND sender_id = :manager_id AND receiver_id = :owner_id ORDER BY timestamp DESC');
    $received_messages_stmt->bindValue(':property_id', $property_id, SQLITE3_INTEGER);
    $received_messages_stmt->bindValue(':manager_id', $property['manager_id'], SQLITE3_INTEGER);
    $received_messages_stmt->bindValue(':owner_id', $owner_id, SQLITE3_INTEGER);
    $received_messages_result = $received_messages_stmt->execute();
    $received_messages = [];
    while ($msg = $received_messages_result->fetchArray(SQLITE3_ASSOC)) {
        $received_messages[] = $msg;
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
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .metrics-table th {
            background-color: #f4f4f4;
        }

        .messages, .notes {
            margin-top: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .message, .note {
            margin-bottom: 15px;
        }

        .message p, .note p {
            margin: 0;
        }

        .message .meta, .note .meta {
            color: #777;
            font-size: 0.9em;
        }

        .calculator {
            margin-top: 30px;
            padding: 20px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .back-to-dashboard {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="property-details">
        <h2>Property: <?php echo htmlspecialchars($property['property_name']); ?></h2>

        <h3>Performance Metrics</h3>
        <table class="metrics-table">
            <tr>
                <th>Timestamp</th>
                <th>Rental Income</th>
                <th>Operation Cost</th>
                <th>Maintenance Cost</th>
                <th>Unexpected Cost</th>
                <th>Profit</th>
                <th>Vacancy Status</th>
                <th>Property Info</th>
            </tr>
            <?php foreach ($metrics as $metric): ?>
                <tr>
                    <td><?php echo htmlspecialchars($metric['timestamp']); ?></td>
                    <td><?php echo htmlspecialchars($metric['rental_income']); ?></td>
                    <td><?php echo htmlspecialchars($metric['tips']); ?></td>
                    <td><?php echo htmlspecialchars($metric['operation_cost']);?></td>
                    <td><?php echo htmlspecialchars($metric['maintenance_cost']); ?></td>
                    <td><?php echo htmlspecialchars($metric['unexpected_cost']); ?></td>
                    <td><?php echo htmlspecialchars($metric['profit']); ?></td>
                    <td><?php echo htmlspecialchars($metric['vacancy_status']); ?></td>
                    <td><?php echo htmlspecialchars($metric['property_info']); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="messages">
            <h3>Communication with Manager</h3>
            <!-- Display received messages -->
            <?php if (!empty($received_messages)): ?>
                <?php foreach ($received_messages as $msg): ?>
                    <div class="message">
                        <p><?php echo htmlspecialchars($msg['message']); ?></p>
                        <div class="meta"><?php echo htmlspecialchars($msg['timestamp']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No messages received yet.</p>
            <?php endif; ?>

            <!-- Form for sending messages to the manager -->
            <form method="POST" action="">
                <textarea name="message" rows="3" placeholder="Type your message to the manager"></textarea><br>
                <button type="submit" name="send_message">Send Message</button>
            </form>
        </div>

        <div class="notes">
            <h3>Notes</h3>
            <!-- Form for saving notes about the property -->
            <form method="POST" action="">
                <textarea name="note" rows="3" placeholder="Write your notes here"></textarea><br>
                <button type="submit" name="save_note">Save Note</button>
            </form>
        </div>

        <div class="calculator">
            <h3>Simple Calculator</h3>
            <!-- Basic calculator for property related calculations -->
            <!-- Example for rental income calculation -->
            <label for="rental_income">Rental Income:</label>
            <input type="number" id="rental_income" name="rental_income" placeholder="Enter rental income">
            
            <label for="operation_cost">Operation Cost:</label>
            <input type="number" id="operation_cost" name="operation_cost" placeholder="Enter operation cost">

            <label for="maintenance_cost">Maintenance Cost:</label>
            <input type="number" id="maintenance_cost" name="maintenance_cost" placeholder="Enter maintenance cost">

            <label for="unexpected_cost">Unexpected Cost:</label>
            <input type="number" id="unexpected_cost" name="unexpected_cost" placeholder="Enter unexpected cost">

            <button type="button" onclick="calculateProfit()">Calculate Profit</button>
            <p id="profitResult"></p>
        </div>

        <script>
            function calculateProfit() {
                var rentalIncome = parseFloat(document.getElementById('rental_income').value) || 0;
                var operationCost = parseFloat(document.getElementById('operation_cost').value) || 0;
                var maintenanceCost = parseFloat(document.getElementById('maintenance_cost').value) || 0;
                var unexpectedCost = parseFloat(document.getElementById('unexpected_cost').value) || 0;
                var profit = (rentalIncome - (operationCost + maintenanceCost + unexpectedCost)).toFixed(2);
                document.getElementById('profitResult').innerText = 'Estimated Profit: $' + profit;
            }
        </script>

        <h3>Contact Information</h3>
        <p><strong>Manager: </strong><?php echo htmlspecialchars($manager['username']); ?></p>
        <p><strong>Contact Info: </strong><?php echo htmlspecialchars($manager['email']); ?></p>

        <div class="back-to-dashboard">
            <button onclick="window.location.href='user_dashboard.php';">Back to Dashboard</button>
        </div>
    </div>
</body>
</html>
