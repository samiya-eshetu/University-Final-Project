<?php
// Set response header and error logging
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/package_approval_errors.log');

// Connect to MySQL database

$host = "sql207.infinityfree.com";
$user = "if0_42226342";
$pass = "VqIUuAIZ38T0f8";
$db = "if0_42226342_allonone";
$conn = new mysqli($host, $user, $pass, $db);

// Check for connection error
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    error_log("Connection failed: " . $conn->connect_error);
    exit;
}

// Ensure only POST requests are allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST requests allowed']);
    exit;
}

// Read and decode JSON input
$rawData = file_get_contents('php://input');
$data = json_decode($rawData, true);

// Validate JSON and input parameters
if (json_last_error() !== JSON_ERROR_NONE || !isset($data['packageId'], $data['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid JSON or missing parameters']);
    exit;
}

$packageId = intval($data['packageId']);
$action = $data['action'];

// Validate action
if (!in_array($action, ['approve', 'reject'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

try {
    // Update package status
 $stmt = $conn->prepare("UPDATE all_in_one_packages SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $packageId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'newStatus' => $newStatus,
            'message' => "Package status updated to '$newStatus'"
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No package found or no change made'
        ]);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
