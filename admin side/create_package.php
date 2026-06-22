<?php
header('Content-Type: application/json');
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/package_approval_errors.log');


$host = "sql207.infinityfree.com";
$db = "if0_42226342_allonone";
$user = 'if0_42226342';
$pass = 'VqIUuAIZ38T0f8';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$imagePath = null;

try {
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($_FILES['image']['tmp_name']);

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception('Invalid image type. Only JPG, PNG, and GIF are allowed.');
        }

        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            $imagePath = $fileName;
        } else {
            throw new Exception('Failed to upload image.');
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO all_in_one_packages (
            title, location, description, duration, 
            price, availability, ride_service, 
            hotel, travel_agent, image_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "ssssdsssss",
        $_POST['title'],
        $_POST['location'],
        $_POST['description'],
        $_POST['duration'],
        $_POST['price'],
        $_POST['availability'],
        $_POST['ride_service'],
        $_POST['hotel_name'],
        $_POST['travel_agent'],
        $imagePath
    );

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Package created successfully']);
    } else {
        throw new Exception($stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}