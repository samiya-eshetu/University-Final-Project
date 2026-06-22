<?php
// Connect to DB
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

// Check if we're requesting featured hotels
if (isset($_GET['action']) && $_GET['action'] === 'featured') {
    $stmt = $conn->prepare("SELECT hotelID, name, location, pricePerNight FROM hotels WHERE status = 'featured'");
    $stmt->execute();
    $result = $stmt->get_result();
    $hotels = [];
    while ($row = $result->fetch_assoc()) {
        $hotels[] = $row;
    }

    echo json_encode($hotels);
    exit;
}

// Search query handler (for live search)
if (isset($_GET['q'])) {
    $q = '%' . $_GET['q'] . '%';
    $stmt = $conn->prepare("SELECT hotelID, name, location, pricePerNight FROM hotels WHERE name LIKE ? OR location LIKE ?");
    $stmt->bind_param("ss", $q, $q);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }

    echo json_encode($results);
    exit;

}
 if ($action == 'remove' && isset($_GET['hotelID'])) {
        $hotelID = intval($_GET['hotelID']);
        $query = "UPDATE hotels SET featured = 'NO' WHERE hotelID = $hotelID";

        if (mysqli_query($conn, $query)) {
            echo "Hotel unfeatured successfully.";
        } else {
            echo "Error updating hotel.";
        }
        exit;
    }
    //hello

echo json_encode(['error' => 'Invalid request']);
?>
