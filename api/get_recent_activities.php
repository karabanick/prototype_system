<?php
header('Content-Type: application/json');

// Connect to the SQLite database
$db = new SQLite3('../property_database.db');

// Query to fetch recent activities
$query = '
    SELECT 
        a.description, 
        u.username, 
        a.timestamp 
    FROM Activities a
    JOIN Users u ON a.user_id = u.user_id
    ORDER BY a.timestamp DESC
    LIMIT 10
';

$result = $db->query($query);

// Initialize an array to store activities data
$activities = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $activities[] = [
        'description' => $row['description'],
        'username' => $row['username'],
        'timestamp' => $row['timestamp']
    ];
}

// Return the data as JSON
echo json_encode(['activities' => $activities]);

$db->close();
?>
