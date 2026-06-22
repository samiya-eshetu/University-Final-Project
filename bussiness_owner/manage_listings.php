<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// DB connection
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Ensure user is logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}
$accountID = $_SESSION['accountID'];

// Detect business roles
$ownsHotel = $conn->query("SELECT 1 FROM hotel_owners WHERE ownerID = $accountID AND status = 'approved'")->num_rows > 0;
$ownsRide  = $conn->query("SELECT 1 FROM ride_owners WHERE ownerID = $accountID AND status = 'approved'")->num_rows > 0;
$ownsTour  = $conn->query("SELECT 1 FROM tour_owners WHERE ownerID = $accountID AND status = 'approved'")->num_rows > 0;

// Handle deletions securely
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_hotel']) && $ownsHotel) {
        $id = intval($_POST['hotel_id']);
        $conn->query("DELETE FROM hotels WHERE hotelID = $id AND ownerID = $accountID");
    } elseif (isset($_POST['delete_ride']) && $ownsRide) {
        $id = intval($_POST['ride_id']);
        $conn->query("DELETE FROM rides WHERE rideID = $id AND ownerID = $accountID");
    } elseif (isset($_POST['delete_package']) && $ownsTour) {
        $id = intval($_POST['package_id']);
        $conn->query("
            DELETE tp FROM tour_packages tp
            JOIN tours t ON tp.tourID = t.tourID
            WHERE tp.packageID = $id AND t.ownerID = $accountID
        ");
    }
}

// Fetch listings owned by user
$hotels = $ownsHotel ? $conn->query("SELECT * FROM hotels WHERE ownerID = $accountID") : false;
$rides = $ownsRide ? $conn->query("SELECT * FROM rides WHERE ownerID = $accountID") : false;
$tour_packages = $ownsTour ? $conn->query("
    SELECT tp.*, t.provider_name 
    FROM tour_packages tp
    JOIN tours t ON tp.tourID = t.tourID
    WHERE t.ownerID = $accountID
") : false;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Listings - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
  /* General table styling */
  .table {
    width: 100%;
    margin-bottom: 1rem;
    color: #212529;
    border-collapse: separate;
    border-spacing: 0;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    border-radius: 0.5rem;
    overflow: hidden;
  }
  
  .table thead th {
    background-color: #2c3e50;
    color: white;
    font-weight: 600;
    padding: 1rem;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    border: none;
  }
  
  .table tbody tr {
    transition: all 0.3s ease;
  }
  

  
  .table td {
    padding: 1rem;
    vertical-align: middle;
    border-top: 1px solid #e9ecef;
  }
  
  /* Badge styling */
  .badge {
    padding: 0.5em 0.75em;
    font-weight: 600;
    letter-spacing: 0.5px;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    text-transform: uppercase;
  }
  
  .badge.bg-success {
    background-color: #28a745 !important;
  }
  
  .badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529;
  }
  
  .badge.bg-danger {
    background-color: #dc3545 !important;
  }
  
  /* Button styling */
  .btn {
    border-radius: 0.25rem;
    font-weight: 500;
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
    transition: all 0.3s ease;
  }
  
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
  }
  
  .btn-outline-secondary {
    border-color: #6c757d;
    color: #6c757d;
  }
  
  .btn-outline-secondary:hover {
    background-color: #6c757d;
    color: white;
  }
  
  .btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
  }
  
  .btn-danger:hover {
    background-color: #c82333;
    border-color: #bd2130;
  }
  
  .btn-outline-success {
    border-color: #28a745;
    color: #28a745;
  }
  
  .btn-outline-success:hover {
    background-color: #28a745;
    color: white;
  }
  
  /* Action buttons container */
  .table td:last-child {
    white-space: nowrap;
  }
  
  /* Form buttons inline */
  .d-inline {
    display: inline-block;
    margin-left: 0.5rem;
  }
  
  /* Rating stars */
  .bi-star-fill {
    font-size: 1rem;
  }
  
  /* Tab content styling */
  .tab-pane {
    padding: 1.5rem;
    background-color: white;
    border-radius: 0.5rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
  }
  
  /* Header styling */
  .d-flex.justify-content-between.align-items-center.mb-3 {
    margin-bottom: 1.5rem !important;
    padding-bottom: 0.75rem;
    border-bottom: 1px solid #e9ecef;
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .table {
      display: block;
      overflow-x: auto;
    }
    
    .table thead {
      display: none;
    }
    
    .table tbody tr {
      display: block;
      margin-bottom: 1rem;
      border: 1px solid #e9ecef;
      border-radius: 0.25rem;
    }
    
    .table td {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0.75rem;
      border-top: none;
      border-bottom: 1px solid #e9ecef;
    }
    
    .table td:before {
      content: attr(data-label);
      font-weight: 600;
      text-transform: uppercase;
      font-size: 0.75rem;
      color: #6c757d;
      margin-right: 1rem;
    }
    
    .table td:last-child {
      border-bottom: none;
    }
  }
</style>



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
      <li><a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : 'text-dark' ?>"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
      <li><a href="manage_listings.php" class="nav-link active"><i class="bi bi-card-list me-2"></i> Manage Listings</a></li>
      <li><a href="payment_management.php" class="nav-link text-dark"><i class="bi bi-currency-exchange me-2"></i> Payment</a></li>
      <li><a href="booking_dashboard.php" class="nav-link text-dark"><i class="bi bi-suitcase me-2"></i> Bookings</a></li>
      <li><a href="review_and_rating.php" class="nav-link text-dark"><i class="bi bi-star me-2"></i> Reviews & Ratings</a></li>
      <li><a href="business_setting.php" class="nav-link text-dark"><i class="bi bi-gear me-2"></i> Profile Settings</a></li>
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

  <!-- Main -->
  <div class="container-fluid p-4" style="margin-left: 250px;">
    <h4 class="fw-bold">Manage Listings</h4>

    <ul class="nav nav-tabs">
      <?php if ($ownsHotel): ?>
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#hotels">Hotels</a></li>
      <?php endif; ?>
      <?php if ($ownsRide): ?>
        <li class="nav-item"><a class="nav-link <?= !$ownsHotel ? 'active' : '' ?>" data-bs-toggle="tab" href="#rides">Rides</a></li>
      <?php endif; ?>
      <?php if ($ownsTour): ?>
        <li class="nav-item"><a class="nav-link <?= (!$ownsHotel && !$ownsRide) ? 'active' : '' ?>" data-bs-toggle="tab" href="#tours">Tours</a></li>
      <?php endif; ?>
    </ul>

    <div class="tab-content mt-3">
      <?php if ($ownsHotel): ?>
      <div class="tab-pane fade show active" id="hotels">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5>Hotels</h5>
          <a href="add_hotel.php" class="btn btn-outline-success btn-sm">+ Add Hotel</a>
        </div>
        <table class="table table-bordered">
          <thead><tr><th>Name</th><th>Location</th><th>Price/Night</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php while ($row = $hotels->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td>ETB <?= number_format($row['pricePerNight'], 2) ?></td>
              <td><span class="badge bg-success"><?= $row['status'] ?></span></td>
              <td>
                <a href="edit_hotel.php?hotel_id=<?= $row['hotelID'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="hotel_id" value="<?= $row['hotelID'] ?>">
                  <button type="submit" name="delete_hotel" onclick="return confirm('Delete this hotel?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <?php if ($ownsRide): ?>
      <div class="tab-pane fade <?= !$ownsHotel ? 'show active' : '' ?>" id="rides">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5>Ride Services</h5>
          <a href="add_ride.php" class="btn btn-outline-success btn-sm">+ Add Ride</a>
        </div>
        <table class="table table-bordered">
          <thead><tr><th>Provider</th><th>Contact</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php while ($row = $rides->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['provider_name']) ?></td>
              <td><?= htmlspecialchars($row['contact_info']) ?></td>
              <td><?= htmlspecialchars($row['description']) ?></td>
              <td><span class="badge bg-warning"><?= $row['status'] ?></span></td>
              <td>
                <a href="edit_ride.php?ride_id=<?= $row['rideID'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="ride_id" value="<?= $row['rideID'] ?>">
                  <button type="submit" name="delete_ride" onclick="return confirm('Delete this ride?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>

      <?php if ($ownsTour): ?>
      <div class="tab-pane fade <?= (!$ownsHotel && !$ownsRide) ? 'show active' : '' ?>" id="tours">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h5>Tour Packages</h5>
          <a href="add_tour.php" class="btn btn-outline-success btn-sm">+ Add Tour</a>
        </div>
        <table class="table table-bordered">
          <thead><tr><th>Title</th><th>Location</th><th>Price</th><th>Duration</th><th>Status</th><th>Rating</th><th>Actions</th></tr></thead>
          <tbody>
          <?php while ($row = $tour_packages->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['title']) ?></td>
              <td><?= htmlspecialchars($row['location']) ?></td>
              <td>ETB <?= number_format($row['price'], 2) ?></td>
              <td><?= htmlspecialchars($row['duration']) ?></td>
              <td><span class="badge bg-<?= strtolower($row['availability']) == 'available' ? 'success' : 'danger' ?>"><?= htmlspecialchars($row['availability']) ?></span></td>
              <td><i class="bi bi-star-fill text-warning"></i> <?= $row['rating'] ?></td>
              <td>
                <a href="edit_tour.php?package_id=<?= $row['packageID'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                <form method="POST" class="d-inline">
                  <input type="hidden" name="package_id" value="<?= $row['packageID'] ?>">
                  <button type="submit" name="delete_package" onclick="return confirm('Delete this package?')" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Add data labels for mobile responsive tables
    const tables = document.querySelectorAll('.table');
    
    tables.forEach(table => {
      const headers = [];
      table.querySelectorAll('thead th').forEach(header => {
        headers.push(header.textContent.trim());
      });
      
      table.querySelectorAll('tbody tr').forEach(row => {
        const cells = row.querySelectorAll('td');
        cells.forEach((cell, index) => {
          cell.setAttribute('data-label', headers[index]);
        });
      });
    });
  });
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
