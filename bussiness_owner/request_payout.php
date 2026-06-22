<?php
include 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $amount = $_POST['amount'];
    $method = $_POST['method'];
    $business_id = 1; // Replace with session value if logged in

    $stmt = $conn->prepare("INSERT INTO payout_requests (business_id, amount, method) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $business_id, $amount, $method);

    if ($stmt->execute()) {
        echo "<script>alert('Payout requested successfully!'); window.location.href='payment_management.php';</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
