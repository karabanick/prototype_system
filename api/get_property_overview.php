<?php
header('Content-Type: application/json');

// Connect to the SQLite database
$db = new SQLite3('../property_database.db');

// Query to fetch property details
$query = '
    SELECT 
        p.property_name, 
        p.property_type, 
        p.location, 
        u1.username as owner, 
        u2.username as manager, 
        p.status
    FROM Properties p
    LEFT JOIN Users u1 ON p.owner_id = u1.user_id
    LEFT JOIN Users u2 ON p.manager_id = u2.user_id
';

$result = $db->query($query);

// Initialize an array to store property data
$properties = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $properties[] = [
        'name' => $row['property_name'],       // Ensure the field names match
        'type' => $row['property_type'],       // Ensure the field names match
        'location' => $row['location'],
        'owner' => $row['owner'],
        'manager' => $row['manager'],
        'status' => $row['status']
    ];
}

// Return the data as JSON
echo json_encode(['properties' => $properties]);

$db->close();
?>
