<?php
require_once 'db_connection.php'; // your $conn should be inside this

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hotelID = $_POST['hotelID'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($hotelID && in_array($action, ['add', 'remove'])) {
        if ($action === 'add') {
            $stmt = $conn->prepare("UPDATE hotels SET exclusive_offer = 'Exclusive' WHERE hotelID = ?");
        } else {
            $stmt = $conn->prepare("UPDATE hotels SET exclusive_offer = NULL WHERE hotelID = ?");
        }

        $stmt->bind_param("i", $hotelID);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid request']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}

$conn->close();
?>
