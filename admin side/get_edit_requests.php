<?php
require 'db_connection.php';
header('Content-Type: application/json');

$tables = [
    'hotels' => ['id' => 'hotelID', 'name' => 'name'],
    'rides' => ['id' => 'rideID', 'name' => 'provider_name'],
    'tours' => ['id' => 'tourID', 'name' => 'provider_name'],
];

$allRequests = [];

foreach ($tables as $table => $info) {
    $sql = "SELECT {$info['id']} AS id, {$info['name']} AS name, update_request FROM $table WHERE update_request IS NOT NULL";
    $result = $conn->query($sql);
    
    while ($row = $result->fetch_assoc()) {
        $request = json_decode($row['update_request'], true);
        if (!$request) continue;

        foreach ($request as $field => $newValue) {
            $allRequests[] = [
                'type' => ucfirst($table),
                'name' => $row['name'],
                'field' => $field,
                'oldValue' => '[Fetch via JS]',  // We’ll load this via JS later
                'newValue' => $newValue,
                'date' => date('Y-m-d'),
            ];
        }
    }
}

echo json_encode($allRequests);
