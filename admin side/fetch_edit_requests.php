<?php
header('Content-Type: application/json');
require 'db_connection.php';

$tables = [
    'hotels' => ['id' => 'hotelID', 'name' => 'name'],
    'rides' => ['id' => 'rideID', 'name' => 'provider_name'],
    'tours' => ['id' => 'tourID', 'name' => 'provider_name'],
    'tour_packages' => ['id' => 'packageID', 'name' => 'title']
];

$allRequests = [];

foreach ($tables as $table => $info) {
    $idCol = $info['id'];
    $nameCol = $info['name'];

    $sql = "SELECT $idCol AS id, $nameCol AS name, update_request FROM $table 
            WHERE update_request IS NOT NULL AND update_request != ''";
    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()) {
        $updates = json_decode($row['update_request'], true);
        if (!$updates) continue;

        // Get the current DB row to fetch old values
        $id = $row['id'];
        $entityName = $row['name'];
        $currentRowRes = $conn->query("SELECT * FROM $table WHERE $idCol = $id LIMIT 1");
        $currentRow = $currentRowRes->fetch_assoc();

        $oldValues = [];
        $newValues = [];
        foreach ($updates as $field => $newValue) {
            $oldValues[$field] = $currentRow[$field] ?? 'N/A';
            $newValues[$field] = $newValue;
        }

        // Store the grouped request as one row
        $allRequests[] = [
            'id' => $id,
            'type' => $table,
            'name' => $entityName,
            'oldValue' => json_encode($oldValues, JSON_PRETTY_PRINT),
            'newValue' => json_encode($newValues, JSON_PRETTY_PRINT),
            'dateRequested' => date('Y-m-d H:i:s')
        ];
    }
}

echo json_encode($allRequests);
