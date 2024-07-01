<?php
// api/get_activity_report.php

// Connect to the database
$db = new SQLite3('../property_database.db');

// Fetch user activities
$query = '
    SELECT 
        u.username, 
        a.description, 
        a.timestamp 
    FROM Activities a
    JOIN Users u ON a.user_id = u.user_id
    ORDER BY a.timestamp DESC
';
$result = $db->query($query);

$activities = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $activities[] = $row;
}

$db->close();

// Check if there are any activities
if (count($activities) > 0) {
    echo json_encode(['activities' => $activities]);
} else {
    echo json_encode(['message' => 'No recent activity.']);
}
?>
