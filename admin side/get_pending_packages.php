<?php
header('Content-Type: application/json');

$host = "sql207.infinityfree.com";
$user = "if0_42226342";
$pass = "VqIUuAIZ38T0f8";
$db = "if0_42226342_allonone";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode([]);
    exit;
}

$query = "SELECT * FROM all_in_one_packages WHERE status = 'pending'";
$result = $conn->query($query);

$packages = [];
while ($row = $result->fetch_assoc()) {
    $packages[] = $row;
}

echo json_encode($packages);
$conn->close();
?>