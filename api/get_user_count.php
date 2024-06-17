<?php
$db = new SQLite3('../property_database.db');
$result = $db->query('SELECT COUNT(*) as count FROM Users');
$row = $result->fetchArray();
echo json_encode(['count' => $row['count']]);
?>
