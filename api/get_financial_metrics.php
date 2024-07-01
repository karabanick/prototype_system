<?php
// api/get_financial_metrics.php

// Connect to the database
$db = new SQLite3('../property_database.db');

// Fetch performance metrics and calculate `system_operations`
$query = '
    SELECT 
        p.property_name,
        pm.rental_income,
        pm.tips,
        pm.operational_cost,
        pm.maintenance_cost,
        pm.unexpected_cost,
        (pm.rental_income + pm.tips) - (pm.operational_cost + pm.maintenance_cost + pm.unexpected_cost) AS profit,
        ((pm.rental_income + pm.tips) - (pm.operational_cost + pm.maintenance_cost + pm.unexpected_cost)) * 0.05 AS system_operations
    FROM PerformanceMetrics pm
    JOIN Properties p ON pm.property_id = p.property_id
';
$result = $db->query($query);

$metrics = [];
$totalSystemOperations = 0;
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $row['profit'] = (float) $row['profit'];
    $row['system_operations'] = (float) $row['system_operations'];
    $metrics[] = $row;
    $totalSystemOperations += $row['system_operations'];
}

$db->close();

// Return the data in JSON format
echo json_encode(['metrics' => $metrics, 'totalSystemOperations' => $totalSystemOperations]);
?>
