<?php
require 'db_connection.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id'] ?? 0);
$type = $input['type'] ?? null;
$action = $input['action'] ?? null;

$tableMap = [
    'hotels' => ['table' => 'hotels', 'id_col' => 'hotelID'],
    'rides' => ['table' => 'rides', 'id_col' => 'rideID'],
    'tours' => ['table' => 'tours', 'id_col' => 'tourID'],
    'tour_packages' => ['table' => 'tour_packages', 'id_col' => 'packageID'],
];

if (!$id || !$type || !isset($tableMap[$type])) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
    exit;
}

$table = $tableMap[$type]['table'];
$idCol = $tableMap[$type]['id_col'];

if ($action === 'approve') {
    $res = $conn->query("SELECT update_request FROM $table WHERE $idCol = $id");
    $row = $res->fetch_assoc();
    $updates = json_decode($row['update_request'], true);

    if (!$updates) {
        echo json_encode(['success' => false, 'message' => 'No update request found']);
        exit;
    }

    $setParts = [];
    foreach ($updates as $field => $value) {
        $escapedValue = $conn->real_escape_string($value);
        $setParts[] = "`$field` = '$escapedValue'";
    }
    $setParts[] = "update_request = NULL";

    $updateSQL = "UPDATE $table SET " . implode(", ", $setParts) . " WHERE $idCol = $id";
    if ($conn->query($updateSQL)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $conn->error]);
    }

} elseif ($action === 'reject') {
    $sql = "UPDATE $table SET update_request = NULL WHERE $idCol = $id";
    echo json_encode(['success' => $conn->query($sql)]);
} elseif ($action === 'review') {
    $res = $conn->query("SELECT update_request FROM $table WHERE $idCol = $id");
    $row = $res->fetch_assoc();
    echo json_encode([
        'success' => true,
        'data' => $row ? json_decode($row['update_request'], true) : []
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
