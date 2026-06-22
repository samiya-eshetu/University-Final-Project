<?php
session_start();
include 'db_connection.php';

// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}

$ownerID = $_SESSION['accountID'];
$serviceType = ''; // This should be determined based on the user's role

// Determine the service type based on user role
if ($_SESSION['role'] == 'hotel_owner') {
    $serviceType = 'hotel';
} elseif ($_SESSION['role'] == 'tour_owner') {
    $serviceType = 'tour';
} elseif ($_SESSION['role'] == 'ride_owner') {
    $serviceType = 'ride';
} else {
    // Not a business owner, redirect or handle appropriately
    header("Location: ../index.php");
    exit;
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="reviews_export.csv"');

// Create output file pointer
$output = fopen('php://output', 'w');

// Write CSV headers
fputcsv($output, ['Customer', 'Service Type', 'Review', 'Rating', 'Date', 'Reply', 'Replied At', 'Reported']);

// Build query to get reviews for this owner's services
$query = "
    SELECT r.*, t.fullName, 
           CASE 
               WHEN r.serviceType = 'hotel' THEN h.name
               WHEN r.serviceType = 'tour' THEN tp.title
               WHEN r.serviceType = 'ride' THEN rd.provider_name
           END as service_name
    FROM reviews r
    JOIN tourists t ON r.touristID = t.touristID
    LEFT JOIN hotels h ON r.serviceType = 'hotel' AND r.serviceID = h.hotelID AND h.ownerID = ?
    LEFT JOIN tour_packages tp ON r.serviceType = 'tour' AND r.serviceID = tp.packageID 
    LEFT JOIN tours tr ON tp.tourID = tr.tourID AND tr.ownerID = ?
    LEFT JOIN rides rd ON r.serviceType = 'ride' AND r.serviceID = rd.rideID AND rd.ownerID = ?
    WHERE (h.ownerID = ? OR tr.ownerID = ? OR rd.ownerID = ?)
    ORDER BY r.createdAt DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiiiii", $ownerID, $ownerID, $ownerID, $ownerID, $ownerID, $ownerID);
$stmt->execute();
$result = $stmt->get_result();

// Write data rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['fullName'],
        $row['service_name'] . ' (' . $row['serviceType'] . ')',
        $row['content'],
        str_repeat('★', $row['rating']) . str_repeat('☆', 5 - $row['rating']),
        date('Y-m-d H:i', strtotime($row['createdAt'])),
        $row['reply'] ? $row['reply'] : 'No reply',
        $row['replied_at'] ? date('Y-m-d H:i', strtotime($row['replied_at'])) : 'N/A',
        $row['reported'] ? 'Yes' : 'No'
    ]);
}

// Close connections
$stmt->close();
$conn->close();
fclose($output);
exit;
?>