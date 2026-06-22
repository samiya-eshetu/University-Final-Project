<?php
session_start();

$connection = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}


// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}


$accountID = $_SESSION['accountID'];

// Check business ownership
$ownsHotel = $connection->query("SELECT 1 FROM hotel_owners WHERE ownerID = $accountID")->num_rows > 0;
$ownsRide  = $connection->query("SELECT 1 FROM ride_owners WHERE ownerID = $accountID")->num_rows > 0;
$ownsTour  = $connection->query("SELECT 1 FROM tour_owners WHERE ownerID = $accountID")->num_rows > 0;

$ownerType = null;
$ownerTables = ['hotel_owners', 'ride_owners', 'tour_owners'];

foreach ($ownerTables as $table) {
    $check = $connection->query("SELECT ownerID FROM $table WHERE ownerID = $accountID");
    if ($check && $check->num_rows > 0) {
        $ownerType = $table;
        break;
    }
}

if (!$ownerType) {
    header("Location: ../index.php");
    exit;
}



$statusMap = [
    'Upcoming' => 'pending',
    'Completed' => 'completed',
    'Cancelled' => 'cancelled'
];

// Defaults
$businessType = $_GET['type'] ?? 'hotel';
$statusKey = $_GET['status'] ?? 'Upcoming';
$search = $_GET['search'] ?? '';

// Handle updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bookingID']) && isset($_POST['action'])) {
    $id = intval($_POST['bookingID']);
    $newStatus = $_POST['action'] === 'complete' ? 'completed' : 'cancelled';
    $connection->query("UPDATE bookings SET status = '$newStatus' WHERE bookingID = $id");

    // Add confirmation message
    $msg = $newStatus === 'completed' ? 'Booking marked as completed!' : 'Booking has been cancelled.';
    header("Location: booking_dashboard.php?type=$businessType&status=$statusKey&search=" . urlencode($search) . "&statusMsg=" . urlencode($msg));
    exit;
}


$status = $statusMap[$statusKey] ?? 'pending';
$query = "SELECT b.*, t.fullName, t.phoneNumber, 
                 h.name AS hotelName, 
                 r.provider_name AS rideName, 
                 tp.title AS tourPackageTitle 
          FROM bookings b
          JOIN tourists t ON b.touristID = t.touristID
          LEFT JOIN hotels h ON b.serviceType = 'hotel' AND b.serviceID = h.hotelID
          LEFT JOIN rides r ON b.serviceType = 'ride' AND b.serviceID = r.rideID
          LEFT JOIN tour_packages tp ON b.serviceType = 'tour' AND b.serviceID = tp.packageID
          WHERE b.status = ? 
            AND b.serviceType = ? 
            AND (
                (b.serviceType = 'hotel' AND b.serviceID IN (SELECT hotelID FROM hotels WHERE ownerID = ?)) OR
                (b.serviceType = 'ride' AND b.serviceID IN (SELECT rideID FROM rides WHERE ownerID = ?)) OR
                (b.serviceType = 'tour' AND b.serviceID IN (
                    SELECT tp.packageID FROM tour_packages tp 
                    JOIN tours t ON tp.tourID = t.tourID 
                    WHERE t.ownerID = ?
                ))
            )";


if ($search !== '') {
    $query .= " AND t.fullName LIKE ?";
    $stmt = $connection->prepare($query);
    $like = "%$search%";
    $stmt->bind_param("ssiiis", $status, $businessType, $accountID, $accountID, $accountID, $like);
} else {
    $stmt = $connection->prepare($query);
    if (!$stmt) {
        die("SQL Error: " . $connection->error);
    }
    $stmt->bind_param("ssiii", $status, $businessType, $accountID, $accountID, $accountID);
}


$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Booking Management </title>

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

  <h2 class="mb-4 text-primary"><i class="bi bi-suitcase-lg me-2"></i>Booking Management</h2>

      <?php if (isset($_GET['statusMsg'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($_GET['statusMsg']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
      <?php endif; ?>



  <!-- Filter & Search Form -->
  <form class="row g-3 mb-4 align-items-end bg-light p-3 rounded shadow-sm" method="GET">
<div class="col-md-3">
  <label class="form-label fw-semibold">Business Type</label>
  <select name="type" class="form-select shadow-sm">
    <?php if ($ownsHotel): ?>
      <option value="hotel" <?= $businessType === 'hotel' ? 'selected' : '' ?>>Hotel</option>
    <?php endif; ?>
    
    <?php if ($ownsRide): ?>
      <option value="ride" <?= $businessType === 'ride' ? 'selected' : '' ?>>Ride</option>
    <?php endif; ?>
    
    <?php if ($ownsTour): ?>
      <option value="tour" <?= $businessType === 'tour' ? 'selected' : '' ?>>Tour</option>
    <?php endif; ?>
  </select>
</div>




    <div class="col-md-3">
      <label class="form-label fw-semibold">Status</label>
      <select name="status" class="form-select shadow-sm">
        <option value="Upcoming" <?= $statusKey === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
        <option value="Completed" <?= $statusKey === 'Completed' ? 'selected' : '' ?>>Completed</option>
        <option value="Cancelled" <?= $statusKey === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label fw-semibold">Search Customer</label>
      <div class="input-group shadow-sm">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" name="search" class="form-control" placeholder="Customer name..." value="<?= htmlspecialchars($search) ?>">
      </div>
    </div>
    <div class="col-md-2 d-grid">
      <button type="submit" class="btn btn-primary shadow-sm">
        <i class="bi bi-filter-circle me-1"></i> Filter
      </button>
    </div>
  </form>

  <!-- Booking Table -->
  <div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
      <h5 class="mb-0"><i class="bi bi-calendar-check me-2 text-info"></i><?= $statusKey ?> Bookings</h5>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light text-center">
            <tr>
              <th>#</th>
              <th>Customer</th>
              <th>Service</th>
              <th>Scheduled Date</th>
              <th>Status</th>
              <th>Contact</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php $i = 1; while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= htmlspecialchars($row['fullName']) ?></td>
                <td>
                  <?php
                    if ($row['serviceType'] === 'hotel') {
                        echo htmlspecialchars($row['hotelName']);
                    } elseif ($row['serviceType'] === 'ride') {
                        echo htmlspecialchars($row['rideName']);
                    } elseif ($row['serviceType'] === 'tour') {
                        echo htmlspecialchars($row['tourPackageTitle']);
                    } else {
                        echo "Unknown Service";
                    }
                  ?>
                </td>

                <td><?= $row['scheduledFor'] ?></td>
                <td>
                  <span class="badge bg-<?= 
                      $row['status'] === 'completed' ? 'success' : 
                      ($row['status'] === 'cancelled' ? 'danger' : 'warning text-dark') ?>">
                    <?= ucfirst($row['status']) ?>
                  </span>
                </td>
                <td><?= htmlspecialchars($row['phoneNumber']) ?></td>
                <td>
                  <?php if ($row['status'] === 'pending'): ?>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="bookingID" value="<?= $row['bookingID'] ?>">
                      <input type="hidden" name="action" value="complete">
                      <button type="submit" class="btn btn-success btn-sm">
                        <i class="bi bi-check-circle"></i> Complete
                      </button>
                    </form>
                    <form method="POST" class="d-inline">
                      <input type="hidden" name="bookingID" value="<?= $row['bookingID'] ?>">
                      <input type="hidden" name="action" value="cancel">
                      <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-circle"></i> Cancel
                      </button>
                    </form>
                  <?php else: ?>
                    <span class="text-muted">-</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Optional: Add Toast or Modal for actions if needed -->

</body>
</html>
