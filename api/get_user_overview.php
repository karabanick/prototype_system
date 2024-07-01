<?php
header('Content-Type: application/json');

// Connect to the SQLite database
$db = new SQLite3('../property_database.db');

// Fetch user details including username and role_id
$query = 'SELECT username, role_id FROM Users';
$result = $db->query($query);

// Map role_id to role names
$roles = [
    1 => 'Admin',
    2 => 'Property Owner',
    3 => 'Property Manager'
];

$users = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $users[] = [
        'username' => $row['username'],
        'role' => $roles[$row['role_id']] ?? 'Unknown'
    ];
}

// Count the total number of users
$totalUsersQuery = 'SELECT COUNT(*) as count FROM Users';
$totalResult = $db->query($totalUsersQuery);
$totalRow = $totalResult->fetchArray();
$totalUsers = $totalRow['count'];

// Return the data as JSON
echo json_encode([
    'totalUsers' => $totalUsers,
    'users' => $users
]);

$db->close();
?>
