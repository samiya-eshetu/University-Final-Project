<?php
require 'db_connection.php';
header('Content-Type: application/json');

$hotelName = $_POST['hotelName'] ?? null;
$action = $_POST['action'] ?? null;

if (!$hotelName || !$action) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$tables = [
    'hotels' => ['pk' => 'hotelID', 'name' => 'name'],
    'rides' => ['pk' => 'rideID', 'name' => 'provider_name'],
    'tours' => ['pk' => 'tourID', 'name' => 'provider_name'],
    'tour_packages' => ['pk' => 'packageID', 'name' => 'title'],
];

$table = null;
$pk = null;

foreach ($tables as $tbl => $conf) {
    $stmt = $conn->prepare("SELECT {$conf['pk']}, update_request FROM $tbl WHERE {$conf['name']} = ?");
    $stmt->bind_param('s', $hotelName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $row = $result->fetch_assoc()) {
        $table = $tbl;
        $pk = $conf['pk'];
        $id = $row[$conf['pk']];
        $updateData = json_decode($row['update_request'], true);
        break;
    }
}

if (!$table || !$updateData) {
    echo json_encode(['success' => false, 'message' => 'No update request found']);
    exit;
}

if ($action === 'approve') {
    $set = [];
    $types = '';
    $values = [];
    foreach ($updateData as $column => $value) {
        $set[] = "$column = ?";
        $types .= is_numeric($value) ? 'd' : 's';
        $values[] = $value;
    }
    $set[] = "update_request = NULL";
    $query = "UPDATE $table SET " . implode(', ', $set) . " WHERE $pk = ?";
    $types .= 'i';
    $values[] = $id;

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$values);
    $success = $stmt->execute();

    echo json_encode(['success' => $success]);

} elseif ($action === 'reject') {
    $stmt = $conn->prepare("UPDATE $table SET update_request = NULL WHERE $pk = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    echo json_encode(['success' => $success]);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
