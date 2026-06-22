<?php
session_start();
require 'db.php';

echo $_SESSION['chapa_booking_data'];

if (!isset($_SESSION['accountID']) || !isset($_SESSION['chapa_booking_data'])) {
    die("Booking session data missing.");
}

$bookingData = $_SESSION['chapa_booking_data'];
$tx_ref = $_GET['tx_ref'] ?? '';

if ($bookingData['tx_ref'] !== $tx_ref) {
    die("Transaction reference mismatch.");
}

// Extract values
$type       = $bookingData['type'];
$serviceID  = $bookingData['service_id'];
$startDate  = $bookingData['start_date'];
$endDate    = $bookingData['end_date'];
$quantity   = $bookingData['quantity'];
$total      = $bookingData['total'];
$method     = $bookingData['payment_method'];
$userID     = $bookingData['user_id'];

// Validate again
if (empty($type) || $serviceID <= 0 || empty($startDate) || empty($endDate) || $quantity <= 0 || $total <= 0) {
    die("Invalid booking data from session.");
}

// Insert booking
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$sql = "INSERT INTO bookings (
    touristID, serviceType, serviceID, scheduledFor, endDate,
    quantity, totalAmount, paymentMethod, paymentStatus, bookingDate, tx_ref
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'paid', NOW(), ?)";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("isssssdss",
    $userID, $type, $serviceID, $startDate, $endDate, $quantity, $total, $method, $tx_ref
);

if ($stmt->execute()) {
    unset($_SESSION['chapa_booking_data']); // clear after use
    $bookingID = $conn->insert_id;
    header("Location: receipt.php?booking_id=$bookingID");
    exit;
} else {
    die("Booking failed: " . $stmt->error);
}

?>
