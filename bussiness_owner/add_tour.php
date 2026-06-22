<?php 
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);



// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}



$accountID = $_SESSION['accountID'];

// Get the correct tourID for the logged-in user
$tourID = null;
$getTourID = $conn->query("SELECT tourID FROM tours WHERE ownerID = $accountID LIMIT 1");

if ($getTourID && $getTourID->num_rows > 0) {
    $tourID = $getTourID->fetch_assoc()['tourID'];
} else {
    die(" No tour found for this user. Please register your tour business first.");
}

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
    $title = trim($_POST['provider_name']); // used as title
    $location = trim($_POST['location']);
    $price = floatval($_POST['price']);
    $duration = trim($_POST['duration']);
    $description = trim($_POST['description']);
    $availability = trim($_POST['availability']);
    $rating = rand(30, 50) / 10; // Random rating between 3.0 and 5.0

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

    if ($title && $location && $price && $duration && $description) {
        $stmt = $conn->prepare("INSERT INTO tour_packages (
            tourID, title, location, duration, price, availability, rating, description, image_path
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "isssdssss", // Changed from "issssdsss" to "isssdssss"
            $tourID, $title, $location, $duration, $price,
            $availability, $rating, $description, $image_path
        );

        $success = $stmt->execute() ? "Tour package added successfully!" : "Error: " . $stmt->error;
        $stmt->close();
    } else {
        $error = "All fields are required.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Tour Package</title>
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
            <li><a href="dashboard.php" class="nav-link text-dark"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li><a href="manage_listings.php" class="nav-link text-dark"><i class="bi bi-card-list me-2"></i>Manage Listings</a></li>
            <li><a href="payment_management.php" class="nav-link text-dark"><i class="bi bi-currency-exchange me-2"></i>Payment</a></li>
            <li><a href="booking_dashboard.php" class="nav-link text-dark"><i class="bi bi-suitcase me-2"></i>Bookings</a></li>
            <li><a href="review_and_rating.php" class="nav-link text-dark"><i class="bi bi-star me-2"></i>Reviews & Ratings</a></li>
            <li><a href="business_setting.php" class="nav-link text-dark"><i class="bi bi-gear me-2"></i>Profile Settings</a></li>
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

    <!-- Main Content -->
    <div class="container-fluid p-4" style="margin-left: 250px;">
        <h3 class="mb-4">Add New Tour Package</h3>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label">Tour Title</label>
                <input type="text" class="form-control" name="provider_name" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" class="form-control" name="location" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Price (ETB)</label>
                <input type="number" step="0.01" class="form-control" name="price" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Duration</label>
                <input type="text" class="form-control" name="duration" required placeholder="e.g., 3 Days, 1 Week">
            </div>

            <div class="mb-3">
                <label class="form-label">Availability</label>
                <select name="availability" class="form-select" required>
                    <option value="Always Available">Always Available</option>
                    <option value="Monthly Departures">Monthly Departures</option>
                    <option value="Weekends">Weekends</option>
                    <option value="Bi-weekly">Bi-weekly</option>
                    <option value="Daily">Daily</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3" required></textarea>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Image (optional)</label>
                <input type="file" class="form-control" name="image" accept="image/*">
            </div>

            <button type="submit" class="btn btn-primary">Add Tour Package</button>
            <a href="manage_listings.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<?php $conn->close(); ?>
