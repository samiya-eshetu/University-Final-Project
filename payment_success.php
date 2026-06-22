<?php
session_start();
$connection = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($connection->connect_error) {
    die("Database error: " . $connection->connect_error);
}

// Check session booking data
if (!isset($_SESSION['chapa_booking_data'])) {
    die("Booking session expired. Please try again.");
}

$data = $_SESSION['chapa_booking_data'];

// Get package ID from GET
$packageID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch package
$packageQuery = "SELECT 
    p.*, 
    h.hotelID, h.name AS hotel_name, h.pricePerNight,
    tp.packageID AS tour_packageID, tp.title AS tour_title, tp.price AS tour_price,
    r.rideID
FROM all_in_one_packages p
LEFT JOIN hotels h ON p.hotelID = h.hotelID
LEFT JOIN tour_packages tp ON p.tourID = tp.packageID
LEFT JOIN rides r ON p.rideID = r.rideID
WHERE p.packageID = ?";
$stmt = $connection->prepare($packageQuery);
$stmt->bind_param("i", $packageID);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$package) {
    die("Invalid package.");
}

// Booking variables
$touristID = $data['accountID'];
$startDate = $data['start_date'];
$endDate = $data['end_date'];
$quantity = $data['quantity'];
$paymentMethod = 'chapa';
$tx_ref = $data['tx_ref'];
$bookingDate = date("Y-m-d");
$cancelRequest = 'none'; // Default value
$days = (new DateTime($endDate))->diff(new DateTime($startDate))->days;
$days = max(1, $days);

// Start DB transaction
$connection->begin_transaction();
try {
    if ($package['hotel_name']) {
        $hotelAmount = $package['pricePerNight'] * $days * $quantity;
        $stmt = $connection->prepare("INSERT INTO bookings 
            (touristID, serviceType, serviceID, bookingDate, tx_ref, scheduledFor, endDate, quantity, totalAmount, paymentMethod, paymentStatus, status, cancelRequest)
            VALUES (?, 'hotel', ?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'pending', ?)");
        $stmt->bind_param("iisssssdss", $touristID, $package['hotelID'], $bookingDate, $tx_ref, $startDate, $endDate, $quantity, $hotelAmount, $paymentMethod, $cancelRequest);
        $stmt->execute();
        $stmt->close();
    }

    if ($package['tour_title']) {
        $tourAmount = $package['tour_price'] * $quantity;
        $stmt = $connection->prepare("INSERT INTO bookings 
            (touristID, serviceType, serviceID, bookingDate, tx_ref, scheduledFor, endDate, quantity, totalAmount, paymentMethod, paymentStatus, status, cancelRequest)
            VALUES (?, 'tour', ?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'pending', ?)");
        $stmt->bind_param("iisssssdss", $touristID, $package['tourID'], $bookingDate, $tx_ref, $startDate, $endDate, $quantity, $tourAmount, $paymentMethod, $cancelRequest);
        $stmt->execute();
        $stmt->close();
    }



    $connection->commit();
    unset($_SESSION['chapa_booking_data']);
    header("Location: history.php?status=success");
    exit;

} catch (Exception $e) {
    $connection->rollback();
    die("Booking failed: " . $e->getMessage());
}
?>
