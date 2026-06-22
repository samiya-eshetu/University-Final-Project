<?php
session_start();

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);
// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}

if (!isset($_GET['hotel_id'])) die("Hotel ID not provided.");
$hotel_id = intval($_GET['hotel_id']);


$result = $conn->query("SELECT * FROM hotels WHERE hotelID = $hotel_id");
if ($result->num_rows == 0) die("Hotel not found.");
$hotel = $result->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    $name = $conn->real_escape_string($_POST['name']);
    $location = $conn->real_escape_string($_POST['location']);
    $price = floatval($_POST['pricePerNight']);
    $description = $conn->real_escape_string($_POST['description']);
    $availability = $conn->real_escape_string($_POST['availability']);


    if ($name !== $hotel['name']) $updates['name'] = $name;
    if ($location !== $hotel['location']) $updates['location'] = $location;
    if ($price != $hotel['pricePerNight']) $updates['pricePerNight'] = $price;
    if ($description != $hotel['description']) $updates['description'] = $description;
    if ($availability !== $hotel['availability']) $updates['availability'] = $availability;

    if (!empty($updates)) {
        $jsonUpdates = $conn->real_escape_string(json_encode($updates));
        $sql = "UPDATE hotels SET update_request = '$jsonUpdates' WHERE hotelID = $hotel_id";
        if ($conn->query($sql)) {
            echo "<div class='alert alert-info'>Update request submitted for admin approval.</div>";
        } else {
            echo "Failed to save update request: " . $conn->error;
        }
    } else {
        echo "<div class='alert alert-warning'>No changes detected.</div>";
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
    <h2>Edit Hotel</h2>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Hotel Name</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($hotel['name']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($hotel['location']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Price Per Night</label>
            <input type="number" step="0.01" name="pricePerNight" class="form-control" value="<?= $hotel['pricePerNight'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Description</label>
            <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($hotel['description']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Availability</label>
            <select name="availability" class="form-select" required>
                <option value="available" <?= $hotel['availability'] === 'available' ? 'selected' : '' ?>>Available</option>
                <option value="unavailable" <?= $hotel['availability'] === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
            </select>
        </div>
        <button href="manage_listings.php" type="submit" class="btn btn-primary">Update Hotel</button>
        <a href="manage_listings.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

</body>
</html>

<?php $conn->close(); ?>
