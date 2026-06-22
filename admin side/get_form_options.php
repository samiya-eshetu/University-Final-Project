<?php
header('Content-Type: application/json');

$host = "sql207.infinityfree.com";
$user = "if0_42226342";
$pass = "VqIUuAIZ38T0f8";
$db = "if0_42226342_allonone";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

try {
    // Fetch ride services from ride_services table (assuming you have one)
    $rideServices = [];
    $result = $conn->query("SELECT service_name FROM rides");
    while ($row = $result->fetch_assoc()) {
        $rideServices[] = $row['service_name'];
    }

    // Fetch hotels from hotels table
    $hotels = [];
    $result = $conn->query("SELECT hotel_name FROM hotels");
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row['hotel_name'];
    }

    // Fetch travel agents from travel_agents table
    $travelAgents = [];
    $result = $conn->query("SELECT agent_name FROM tour_owners");
    while ($row = $result->fetch_assoc()) {
        $travelAgents[] = $row['agent_name'];
    }

    echo json_encode([
        'success' => true,
        'rideServices' => $rideServices,
        'hotels' => $hotels,
        'travelAgents' => $travelAgents
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>