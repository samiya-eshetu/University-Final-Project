<?php
session_start();

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB Connection
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}
// Manualy added ownerID (should be from session Natis work)
$ownerID = $_SESSION['accountID'];



$accountID = $_SESSION['accountID'];
$ownerType = null;
$ownerTables = ['hotel_owners', 'ride_owners', 'tour_owners'];

foreach ($ownerTables as $table) {
    $check = $conn->query("SELECT ownerID FROM $table WHERE ownerID = $accountID");
    if ($check && $check->num_rows > 0) {
        $ownerType = $table;
        break;
    }
}

if (!$ownerType) {
    header("Location: ../index.php");
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider_name = $conn->real_escape_string($_POST['provider_name']);
    $contact_info = $conn->real_escape_string($_POST['contact_info']);
    $description = $conn->real_escape_string($_POST['description']);
    $rating = rand(30, 50) / 10; // Random between 3.0 to 5.0
    $status = 'pending';
    $exclusive_offer = null;
    $featured = null;

    // Handle image upload
    $image_path = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadDir = "uploads/";
        if (!is_dir($uploadDir)) mkdir($uploadDir);
        $filename = basename($_FILES['image']['name']);
        $targetFile = $uploadDir . time() . "_" . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image_path = $conn->real_escape_string($targetFile);
        }
    }


    $sql = "INSERT INTO rides (
        ownerID, provider_name, contact_info, image, description, 
        rating, exclusive_offer, featured, status, created_at
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
    die("SQL Prepare failed: " . $conn->error);
}

    $stmt->bind_param("issssdds", 
        $ownerID, $provider_name, $contact_info, $image_path, 
        $description, $rating, $exclusive_offer, $featured
    );



    if ($stmt->execute()) {
        $success = "Ride added successfully!";
    } else {
        $error = "Insert failed: " . $stmt->error;
    }

    $stmt->close();
}
?> 


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Ride Service</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
        <!-- Sidebar -->
        <div class="flex-shrink-0 p-3 bg-light border-end" style="width: 250px; height: 100vh; position: fixed;">
            <a href="#" class="d-flex align-items-center mb-3 text-decoration-none">
                <i class="bi bi-speedometer2 me-2 fs-4"></i>
                <span class="fs-5 fw-bold">Dashboard</span>
            </a>
            <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li>
            <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <li>
            <a href="manage_listings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_listings.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-card-list me-2"></i> Manage Listings
            </a>
        </li>

        <li>
            <a href="payment_management.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payment_management.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-currency-exchange me-2"></i> Payment
            </a>
        </li>
        <li>
            <a href="booking_dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'booking_dashboard.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-suitcase me-2"></i> Bookings
            </a>
        </li>
        <li>
            <a href="review_and_rating.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'review_and_rating.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-star me-2"></i> Reviews & Ratings
            </a>
        </li>
        <li>
            <a href="business_setting.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'business_setting.html' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-gear me-2"></i> Profile Settings
            </a>
        </li>
    </ul>
    <!-- Return to Home Button -->
    <div class="mt-auto pt-3 border-top">
        <a href="../index.php" 
           class="btn btn-outline-primary d-flex align-items-center w-100 py-2">
            <i class="bi bi-house-door me-2"></i>
            <span>Main Page</span>
        </a>
    </div>

</div>




<div class="container-fluid p-4" style="margin-left: 250px;">
    <h3 class="mb-4">Add New Ride Service</h3>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Provider Name</label>
            <input type="text" class="form-control" name="provider_name" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contact Info</label>
            <input type="text" class="form-control" name="contact_info" required>
        </div>


        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Upload Vehicle Image (optional)</label>
            <input type="file" class="form-control" name="image" accept="image/*">
        </div>

        <button type="submit" class="btn btn-primary">Add Ride</button>
        <a href="manage_listings.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>

<?php $conn->close(); ?>
