<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);



$host = "sql207.infinityfree.com";
$user = "if0_42226342";
$password = "VqIUuAIZ38T0f8";
$dbname = "if0_42226342_allonone";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => "Connection failed: " . $conn->connect_error]));
}

function errorResponse($msg) {
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

$action = $_POST['action'] ?? '';
$id = $_POST['id'] ?? null;
$type = $_POST['type'] ?? null;

// Tables config with corrected 'ride' location field
$tables = [
    'hotel' => [
        'table' => 'hotels',
        'pk' => 'hotelID',
        'price' => 'pricePerNight',
        'name' => 'name',
        'location' => 'location',
        'description' => 'description'
    ],
   'ride' => [
    'table' => 'rides',
    'pk' => 'rideID',
    'name' => 'provider_name',
    'location' => 'contact_info',
    'price' => 'Null',  // ✅ Using rating instead of nonexistent price column
    'description' => 'description'
],

    'tour' => [
        'table' => 'tours',
        'pk' => 'tourID',
        'price' => 'price',
        'name' => 'provider_name',
        'location' => 'location',
        'description' => 'description'
    ],
    'package' => [
        'table' => 'all_in_one_packages',
        'pk' => 'packageID',
        'price' => 'price',
        'name' => 'title',
        'location' => 'location',
        'description' => 'description'
    ]
];

// Get affiliates
if ($action === 'get_affiliates') {
    $response = ['success' => true, 'newAffiliates' => [], 'activeAffiliates' => []];

    foreach ($tables as $typeKey => $config) {
        $table = $config['table'];
        $pk = $config['pk'];
        $nameField = $config['name'];
        $locationField = $config['location'];
        $priceField = $config['price'];
        $descriptionField = $config['description'];

        // PENDING services
        $pendingSql = "SELECT 
            $pk as id,
            $nameField as name,
            $locationField as location,
            $priceField as price,
            $descriptionField as description,
            '$typeKey' as type
            FROM $table 
            WHERE LOWER(status) = 'pending'";

        $result = $conn->query($pendingSql);
        if ($result) {
            $response['newAffiliates'] = array_merge(
                $response['newAffiliates'],
                $result->fetch_all(MYSQLI_ASSOC)
            );
        }

        // ACTIVE (approved or disabled)
        $activeSql = "SELECT 
            $pk as id,
            $nameField as name,
            $locationField as location,
            $priceField as price,
            $descriptionField as description,
            '$typeKey' as type,
            CASE 
                WHEN LOWER(status) = 'approved' THEN 'Enabled'
                WHEN LOWER(status) = 'disabled' THEN 'Disabled'
                ELSE status
            END as status
            FROM $table
            WHERE LOWER(status) IN ('approved', 'disabled')";

        $result = $conn->query($activeSql);
        if ($result) {
            $response['activeAffiliates'] = array_merge(
                $response['activeAffiliates'],
                $result->fetch_all(MYSQLI_ASSOC)
            );
        }
    }

    echo json_encode($response);
    exit;
}

// Validate parameters for other actions
if (!$id || !$type || !isset($tables[$type])) {
    errorResponse("Invalid parameters - ID and type are required");
}

$config = $tables[$type];
$table = $config['table'];
$pk = $config['pk'];

// Accept
if ($action === 'accept') {
    $stmt = $conn->prepare("UPDATE $table SET status = 'approved' WHERE $pk = ? AND LOWER(status) = 'pending'");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        errorResponse("Failed to approve service");
    }
    $stmt->close();
    exit;
}
// Get service details including owner
if ($action === 'get_service_details') {
    if (!$id || !$type || !isset($tables[$type])) {
        errorResponse("Invalid parameters");
    }

    $config = $tables[$type];
    $table = $config['table'];
    $pk = $config['pk'];

    // Get service data
    $stmt = $conn->prepare("SELECT * FROM $table WHERE $pk = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $serviceResult = $stmt->get_result();
    if ($serviceResult->num_rows === 0) {
        $stmt->close();
        errorResponse("Service not found");
    }
    $serviceData = $serviceResult->fetch_assoc();
    $stmt->close();

    // Determine owner table
    $ownerTable = $type . "_owners"; // e.g., hotel_owners, ride_owners, etc.
    $ownerID = $serviceData['ownerID'] ?? null;

    if (!$ownerID) {
        echo json_encode(['success' => true, 'data' => ['service' => $serviceData, 'owner' => null]]);
        exit;
    }

    // Fetch owner info
    $stmt = $conn->prepare("SELECT * FROM $ownerTable WHERE ownerID = ?");
    $stmt->bind_param('i', $ownerID);
    $stmt->execute();
    $ownerResult = $stmt->get_result();
    $ownerData = $ownerResult->num_rows > 0 ? $ownerResult->fetch_assoc() : null;
    $stmt->close();

    echo json_encode(['success' => true, 'data' => [
        'service' => $serviceData,
        'owner' => $ownerData
    ]]);
    exit;
}

// Reject
if ($action === 'reject') {
    $reason = $_POST['reason'] ?? 'No reason provided';
    $stmt = $conn->prepare("UPDATE $table SET status = 'rejected' WHERE $pk = ?");
    $stmt->bind_param('i', $id);
    $success = $stmt->execute();
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Service rejected successfully' : 'Failed to reject service'
    ]);
    $stmt->close();
    exit;
}

// Toggle status
if ($action === 'toggle') {
    $stmt = $conn->prepare("SELECT status FROM $table WHERE $pk = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($currentStatus);
    if (!$stmt->fetch()) {
        $stmt->close();
        errorResponse("Service not found");
    }
    $stmt->close();

    $currentStatus = strtolower($currentStatus);
    if (!in_array($currentStatus, ['approved', 'disabled'])) {
        errorResponse("Cannot toggle status from current state: $currentStatus");
    }

    $newStatus = $currentStatus === 'approved' ? 'disabled' : 'approved';

    $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE $pk = ?");
    $stmt->bind_param('si', $newStatus, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        errorResponse("Failed to update status");
    }
    $stmt->close();
    exit;
}

// Fallback
errorResponse("Invalid action specified");

$conn->close();
