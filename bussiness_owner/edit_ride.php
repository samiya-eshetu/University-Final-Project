<?php
session_start();

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}
// Check if ride_id is provided
if (!isset($_GET['ride_id'])) die("Ride ID not provided.");
$ride_id = intval($_GET['ride_id']);

//  Fetch ride FIRST for comparison
$result = $conn->query("SELECT * FROM rides WHERE rideID = $ride_id");
if ($result->num_rows == 0) die("Ride not found.");
$ride = $result->fetch_assoc();

//  Handle update request logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $provider_name = $conn->real_escape_string($_POST['provider_name']);
    $contact_info = $conn->real_escape_string($_POST['contact_info']);
    $description = $conn->real_escape_string($_POST['description']);

    $updates = [];

    if ($provider_name !== $ride['provider_name']) $updates['provider_name'] = $provider_name;
    if ($contact_info !== $ride['contact_info']) $updates['contact_info'] = $contact_info;
    if ($description !== $ride['description']) $updates['description'] = $description;

    if (!empty($updates)) {
        $jsonUpdates = $conn->real_escape_string(json_encode($updates));
        $sql = "UPDATE rides SET update_request = '$jsonUpdates' WHERE rideID = $ride_id";
        if ($conn->query($sql)) {
            echo "<div class='alert alert-info text-center p-3'>Update request submitted for admin approval.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
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
    <title>Edit Ride Service</title>
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
    <h2>Edit Ride</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Provider Name</label>
            <input type="text" name="provider_name" class="form-control" value="<?= htmlspecialchars($ride['provider_name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Contact Info</label>
            <input type="text" name="contact_info" class="form-control" value="<?= htmlspecialchars($ride['contact_info']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?= htmlspecialchars($ride['description']) ?></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update Ride</button>
        <a href="manage_listings.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>

<?php $conn->close(); ?>
