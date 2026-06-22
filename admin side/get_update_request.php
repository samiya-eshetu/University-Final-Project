<?php
require 'db_connection.php';
header('Content-Type: application/json');

$hotelName = $_GET['hotelName'] ?? null;
if (!$hotelName) {
    echo json_encode(['success' => false]);
    exit;
}

$tables = [
    'hotels' => ['pk' => 'hotelID', 'name' => 'name'],
    'rides' => ['pk' => 'rideID', 'name' => 'provider_name'],
    'tours' => ['pk' => 'tourID', 'name' => 'provider_name'],
];

foreach ($tables as $table => $info) {
    $stmt = $conn->prepare("SELECT update_request FROM $table WHERE {$info['name']} = ?");
    $stmt->bind_param('s', $hotelName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'update_request' => json_decode($row['update_request'], true)]);
        exit;
    }
}

echo json_encode(['success' => false]);
