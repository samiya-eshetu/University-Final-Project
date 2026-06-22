<?php


$host = "sql207.infinityfree.com";
$user = "if0_42226342";
$pass = "VqIUuAIZ38T0f8";
$db = "if0_42226342_allonone";

$conn = new mysqli($host, $user, $pass, $db);
header('Content-Type: application/json');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';
$statusFilter = $_GET['status'] ?? 'pending'; // Get status filter from URL

switch ($action) {
    case 'fetch':
        // Only fetch packages with the specified status (default is 'pending')
        $query = "SELECT p.*, h.name AS hotelName, r.provider_name AS rideName, t.provider_name AS tourName
                  FROM all_in_one_packages p
                  LEFT JOIN hotels h ON p.hotelID = h.hotelID
                  LEFT JOIN rides r ON p.rideID = r.rideID
                  LEFT JOIN tours t ON p.tourID = t.tourID";
        
        // Add WHERE clause if status filter is provided
        if ($statusFilter === 'pending') {
            $query .= " WHERE p.status = 'pending'";
        } elseif ($statusFilter === 'processed') {
            $query .= " WHERE p.status IN ('approved', 'rejected')";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        $result = $conn->query($query);

        if (!$result) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
            exit;
        }

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        echo json_encode($rows);
        break;

    case 'create':
    $data = $_POST;
    $title = $conn->real_escape_string($data['title']);
    $location = $conn->real_escape_string($data['location']);
    $description = $conn->real_escape_string($data['description']);
    $duration = $conn->real_escape_string($data['duration']);
    $price = floatval($data['price']);
    $availability = $conn->real_escape_string($data['availability']); // Fixed this line
    $rideID = intval($data['ride_service']);
    $hotelID = intval($data['hotel_name']);
    $tourID = intval($data['travel_agent']);

    $imagePath = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $target = 'uploads/' . uniqid('pkg_', true) . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], $target);
        $imagePath = $target;
    }

    $stmt = $conn->prepare("INSERT INTO all_in_one_packages 
        (title, location, description, duration, price, availability, rideID, hotelID, tourID, image_path, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

    $stmt->bind_param('ssssdsiiss', $title, $location, $description, $duration, $price, $availability, $rideID, $hotelID, $tourID, $imagePath);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Package created']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Creation failed']);
    }
    break;

    case 'update_status':
        $data = json_decode(file_get_contents('php://input'), true);
        $id = intval($data['id']);
        $status = $conn->real_escape_string($data['status']);

        $stmt = $conn->prepare("UPDATE all_in_one_packages SET status = ? WHERE packageID = ?");
        $stmt->bind_param('si', $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Status updated']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Status update failed']);
        }
        break;


case 'details':
    $id = intval($_GET['id'] ?? 0);
    
    $stmt = $conn->prepare("SELECT p.*, h.name AS hotelName, r.provider_name AS rideName, t.provider_name AS tourName
                          FROM all_in_one_packages p
                          LEFT JOIN hotels h ON p.hotelID = h.hotelID
                          LEFT JOIN rides r ON p.rideID = r.rideID
                          LEFT JOIN tours t ON p.tourID = t.tourID
                          WHERE p.packageID = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $package = $result->fetch_assoc();
        echo json_encode(['success' => true, 'package' => $package]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Package not found']);
    }
    break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}