<?php
header('Content-Type: application/json');
require 'db_connection.php'; // adjust if needed

$response = [
    'rides' => [],
    'hotels' => [],
    'tours' => []
];

// Fetch approved rides
$r = $conn->query("SELECT rideID, provider_name FROM rides WHERE status = 'approved'");
while ($row = $r->fetch_assoc()) {
    $response['rides'][] = $row;
}

// Fetch approved hotels
$h = $conn->query("SELECT hotelID, name FROM hotels WHERE status = 'approved'");
while ($row = $h->fetch_assoc()) {
    $response['hotels'][] = $row;
}

// Fetch approved tours
$t = $conn->query("SELECT tourID, provider_name FROM tours WHERE status = 'approved'");
while ($row = $t->fetch_assoc()) {
    $response['tours'][] = $row;
}

echo json_encode($response);
