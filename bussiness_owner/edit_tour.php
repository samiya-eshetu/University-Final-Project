<?php
session_start();

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}
// Check if package_id is provided
if (!isset($_GET['package_id'])) {
    die("Tour package ID not provided.");
}
$package_id = intval($_GET['package_id']);

// Fetch tour package
$result = $conn->query("SELECT * FROM tour_packages WHERE packageID = $package_id");
if ($result->num_rows == 0) die("Tour package not found.");
$package = $result->fetch_assoc();

// Handle update request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];

    // Capture submitted data
    $title = $conn->real_escape_string($_POST['title']);
    $location = $conn->real_escape_string($_POST['location']);
    $description = $conn->real_escape_string($_POST['description']);
    $duration = $conn->real_escape_string($_POST['duration']);
    $price = floatval($_POST['price']);

    // Compare changes
    if ($title !== $package['title']) $updates['title'] = $title;
    if ($location !== $package['location']) $updates['location'] = $location;
    if ($description !== $package['description']) $updates['description'] = $description;
    if ($duration !== $package['duration']) $updates['duration'] = $duration;
    if ($price != $package['price']) $updates['price'] = $price;

    // Save update request
    if (!empty($updates)) {
        $json = $conn->real_escape_string(json_encode($updates));
        $sql = "UPDATE tour_packages SET update_request = '$json' WHERE packageID = $package_id";

        if ($conn->query($sql)) {
            echo "<div class='alert alert-info text-center p-3'>Update request submitted for admin approval.</div>";
        } else {
            echo "<div class='alert alert-danger text-center p-3'>Error: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-warning text-center p-3'>No changes detected.</div>";
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Hotel</title>
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
<div class="container mt-4">
    <h2>Edit Tour Package</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($package['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($package['location']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($package['description']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Duration</label>
            <input type="text" name="duration" class="form-control" value="<?= htmlspecialchars($package['duration']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Price (ETB)</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= $package['price'] ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Submit Changes</button>
        <a href="manage_listings.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>

<?php $conn->close(); ?>