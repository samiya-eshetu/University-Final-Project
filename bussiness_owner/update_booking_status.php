<?php
session_start();
$connection = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($connection->connect_error) die("Connection failed: " . $connection->connect_error);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingID = intval($_POST['id']);
    $newStatus = $_POST['status'];
    
    // Validate status
    $validStatuses = ['completed', 'cancelled'];
    if (!in_array($newStatus, $validStatuses)) {
        die("Invalid status");
    }
    
    // Map to database values
    $statusMap = [
        'completed' => 'completed',
        'cancelled' => 'cancelled'
    ];
    
    $stmt = $connection->prepare("UPDATE bookings SET status = ? WHERE bookingID = ?");
    $stmt->bind_param("si", $statusMap[$newStatus], $bookingID);
    
    if ($stmt->execute()) {
        echo "The Booking Have Been $statusMap[$newStatus] successfully!";
    } else {
        echo "Error updating status: " . $stmt->error;
    }
    
    $stmt->close();
    $connection->close();
    exit();
}

header("Location: booking_dashboard.php");
?>