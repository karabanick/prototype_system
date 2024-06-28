<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: user_login.php');
    exit();
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Connect to the database
$db = new SQLite3('property_database.db');

// Add error handling for database connection
if (!$db) {
    die("Database connection failed: " . $db->lastErrorMsg());
}

// Helper functions
function getUserName($db, $user_id) {
    $stmt = $db->prepare('SELECT username FROM Users WHERE user_id = :user_id');
    $stmt->bindValue(':user_id', $user_id, SQLITE3_INTEGER);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);
    return $user ? $user['username'] : 'N/A';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Specific styles for property cards */
        .property-card {
            display: flex;
            flex-direction: column;
            width: 250px;
            margin: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
        }
        
        .property-image {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .property-content {
            padding: 15px;
            text-align: left;
        }
        
        .property-content h3 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        
        .property-content p {
            margin: 5px 0;
            color: #555;
        }
        
        .property-content .details-button {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 12px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
        }
        
        .property-content .details-button:hover {
            background: #0056b3;
        }
        
        .property-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            padding: 20px;
        }

        /* Ensure main container can scroll */
        main {
            overflow-y: auto;
            max-height: calc(100vh - 100px); /* Adjust height based on header and other elements */
            padding: 20px;
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body class="user-dashboard">
    <div class="dashboard-container user-dashboard">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
            <a href="logout.php" class="logout">Logout</a>
        </header>
        <main>
            <?php if ($role == 'Property Owner'): ?>
                <!-- Property Owner Content -->
                <h2>Dashboard</h2>
                <p>Here are your properties:</p>
                <div class="property-grid">
                    <?php
                    // SQL Query for Property Owner
                    $query = 'SELECT property_id, property_name, location, manager_id, status, property_image FROM Properties WHERE owner_id = :owner_id';
                    $stmt = $db->prepare($query);

                    // Check if statement preparation was successful
                    if (!$stmt) {
                        die("Failed to prepare statement: " . $db->lastErrorMsg());
                    }

                    $stmt->bindValue(':owner_id', $_SESSION['user_id'], SQLITE3_INTEGER);
                    $result = $stmt->execute();

                    // Check if execution was successful
                    if (!$result) {
                        die("Failed to execute statement: " . $db->lastErrorMsg());
                    }

                    // Fetch and display properties
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $property_image = htmlspecialchars($row['property_image']);
                        $propertyName = htmlspecialchars($row['property_name']);
                        $location = htmlspecialchars($row['location']);
                        $managerName = htmlspecialchars(getUserName($db, $row['manager_id']));
                        $status = htmlspecialchars($row['status']);
                        $propertyId = htmlspecialchars($row['property_id']);
                        echo "<div class='property-card'>
                                <img src='$property_image' alt='$propertyName' class='property-image'>
                                <div class='property-content'>
                                    <h3>$propertyName</h3>
                                    <p><strong>Location:</strong> $location</p>
                                    <p><strong>Manager:</strong> $managerName</p>
                                    <p><strong>Status:</strong> $status</p>
                                    <a href='owner_property_details.php?id=$propertyId' class='details-button'>View Details</a>
                                </div>
                              </div>";
                    }
                    ?>
                </div>
            <?php elseif ($role == 'Property Manager'): ?>
                <!-- Property Manager Content -->
                <h2>Dashboard</h2>
                <p>Here are the properties you manage:</p>
                <div class="property-grid">
                    <?php
                    // SQL Query for Property Manager
                    $query = 'SELECT property_id, property_name, location, owner_id, property_image FROM Properties WHERE manager_id = :manager_id';
                    $stmt = $db->prepare($query);

                    // Check if statement preparation was successful
                    if (!$stmt) {
                        die("Failed to prepare statement: " . $db->lastErrorMsg());
                    }

                    $stmt->bindValue(':manager_id', $_SESSION['user_id'], SQLITE3_INTEGER);
                    $result = $stmt->execute();

                    // Check if execution was successful
                    if (!$result) {
                        die("Failed to execute statement: " . $db->lastErrorMsg());
                    }

                    // Fetch and display properties
                    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
                        $property_image = htmlspecialchars($row['property_image']);
                        $propertyName = htmlspecialchars($row['property_name']);
                        $location = htmlspecialchars($row['location']);
                        $ownerName = htmlspecialchars(getUserName($db, $row['owner_id']));
                        $propertyId = htmlspecialchars($row['property_id']);
                        echo "<div class='property-card'>
                                <img src='$property_image' alt='$propertyName' class='property-image'>
                                <div class='property-content'>
                                    <h3>$propertyName</h3>
                                    <p><strong>Location:</strong> $location</p>
                                    <p><strong>Owner:</strong> $ownerName</p>
                                    <a href='property_details.php?id=$propertyId' class='details-button'>Enter</a>
                                </div>
                              </div>";
                    }
                    ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
