<?php
session_start();

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}

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

// Determine service type and table
$serviceMap = [
    'hotel_owners' => ['service' => 'hotel', 'table' => 'hotels', 'icon' => 'bi-building'],
    'ride_owners' => ['service' => 'ride', 'table' => 'rides', 'icon' => 'bi-car-front'],
    'tour_owners' => ['service' => 'tour', 'table' => 'tours', 'icon' => 'bi-signpost-split']
];
$serviceType = $serviceMap[$ownerType]['service'];
$listingTable = $serviceMap[$ownerType]['table'];
$serviceIcon = $serviceMap[$ownerType]['icon'];

// Fetch business owner details
$ownerDetails = [];
$res = $conn->query("SELECT * FROM $ownerType WHERE ownerID = $accountID");
if ($res && $res->num_rows > 0) {
    $ownerDetails = $res->fetch_assoc();
}

// Fetch business stats
$stats = [
    'listings' => 0,
    'rating' => 0,
    'reviews' => 0,
    'bookings' => 0,
    'revenue' => 0
];

// Total Listings
$result = $conn->query("SELECT COUNT(*) as total FROM $listingTable WHERE ownerID = $accountID");
if ($result && $row = $result->fetch_assoc()) {
    $stats['listings'] = $row['total'];
}

// Average Rating
$res = $conn->query("SELECT AVG(r.rating) as avg_rating
                     FROM reviews r
                     JOIN bookings b ON r.serviceID = (SELECT serviceID FROM bookings WHERE bookingID = r.reviewID)
                     WHERE r.serviceType = '$serviceType' AND 
                     EXISTS (SELECT 1 FROM $listingTable WHERE ownerID = $accountID AND {$serviceType}ID = r.serviceID)");
if ($res && $r = $res->fetch_assoc()) {
    $stats['rating'] = round($r['avg_rating'], 1);
}

// Total Reviews
$res = $conn->query("SELECT COUNT(*) as total_reviews
                     FROM reviews r
                     WHERE r.serviceType = '$serviceType' AND 
                     EXISTS (SELECT 1 FROM $listingTable WHERE ownerID = $accountID AND {$serviceType}ID = r.serviceID)");
if ($res && $r = $res->fetch_assoc()) {
    $stats['reviews'] = $r['total_reviews'];
}

// Total Bookings
$res = $conn->query("SELECT COUNT(*) as total_bookings
                     FROM bookings
                     WHERE serviceType = '$serviceType' AND 
                     EXISTS (SELECT 1 FROM $listingTable WHERE ownerID = $accountID AND {$serviceType}ID = serviceID)");
if ($res && $r = $res->fetch_assoc()) {
    $stats['bookings'] = $r['total_bookings'];
}

// Total Revenue (last 30 days, excluding cancelled bookings)
$res = $conn->query("
    SELECT SUM(totalAmount) as total_revenue
    FROM bookings
    WHERE serviceType = '$serviceType'
      AND bookingDate >= DATE_SUB(NOW(), INTERVAL 30 DAY)
      AND status != 'cancelled'
      AND EXISTS (
          SELECT 1 FROM $listingTable 
          WHERE ownerID = $accountID AND {$serviceType}ID = bookings.serviceID
      )
");

if ($res && $r = $res->fetch_assoc()) {
    $stats['revenue'] = $r['total_revenue'] ? number_format($r['total_revenue'], 2) : '0.00';
}


// Recent Bookings (last 5)
$recentBookings = [];
$res = $conn->query("SELECT b.*, t.fullName as touristName 
                     FROM bookings b
                     JOIN tourists t ON b.touristID = t.touristID
                     WHERE b.serviceType = '$serviceType' AND 
                     EXISTS (SELECT 1 FROM $listingTable WHERE ownerID = $accountID AND {$serviceType}ID = b.serviceID)
                     ORDER BY b.bookingDate DESC LIMIT 5");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $recentBookings[] = $row;
    }
}

// Recent Reviews (last 3)
$recentReviews = [];
$res = $conn->query("SELECT r.*, t.fullName as touristName 
                     FROM reviews r
                     JOIN tourists t ON r.touristID = t.touristID
                     WHERE r.serviceType = '$serviceType' AND 
                     EXISTS (SELECT 1 FROM $listingTable WHERE ownerID = $accountID AND {$serviceType}ID = r.serviceID)
                     ORDER BY r.createdAt DESC LIMIT 3");
if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $recentReviews[] = $row;
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />
  <style>
      body {
      background-color: #ebedf2;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }







  .stat-card {
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
  }
  

  
  .icon-wrapper {
    width: 50px;
    height: 50px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
  }
  
  .hover-scale:hover .icon-wrapper {
    transform: scale(1.1);
  }
  
  .rating-stars {
    display: flex;
    gap: 2px;
  }
  
  .progress {
    border-radius: 100px;
    background-color: #f0f2f5;
  }
  
  .transition-all {
    transition: all 0.3s ease;
  }






    .stat-value {
      font-size: 1.75rem;
      font-weight: 700;
    }
    
    .recent-activity-item {
      border-left: 3px solid var(--primary-color);
      transition: all 0.3s;
    }
    
    .recent-activity-item:hover {
      background-color: rgba(78, 115, 223, 0.05);
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











  <!-- Main -->
  <div class="p-4 flex-grow-1" style="margin-left: 250px;">
    <h4 class="fw-bold mb-4"></h4>



      <!-- Main Content -->
  <div class="main-content flex-grow-1">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold mb-1">Welcome to Your Business Dashboard</h2>
        <p class="text-muted mb-0">
          <i class="bi <?= $serviceIcon ?> me-1"></i> 
          <?= $ownerDetails['businessName'] ?? 'Your Business' ?>
        </p>
      </div>
      <div class="d-flex align-items-center">
        <div class="me-3">
          <small class="text-muted d-block">Last login</small>
          <span><?= date('M j, Y g:i A') ?></span>
        </div>
        <div class="dropdown">
          <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
            <i class="bi bi-person-circle"></i>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="business_setting.php"><i class="bi bi-person me-2"></i> Profile</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Business Status Card -->
    <div class="card shadow-sm border-0 mb-4 animate__animated animate__fadeIn">
      <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <h5 class="card-title mb-1">Business Status</h5>
            <p class="text-muted mb-0">Manage your business visibility and operations</p>
          </div>
          <div>

          </div>
        </div>
        <div class="row mt-3">
          <div class="col-md-6">
            <div class="d-flex align-items-center">
              <div class="bg-light rounded p-2 me-3">
                <i class="bi bi-calendar2-check fs-4 text-primary"></i>
              </div>
              <div>
                <small class="text-muted d-block">Last Booking</small>
                <span class="fw-bold">
                  <?= count($recentBookings) > 0 ? 
                      date('M j, Y', strtotime($recentBookings[0]['bookingDate'])) : 
                      'No bookings yet' ?>
                </span>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="d-flex align-items-center">
              <div class="bg-light rounded p-2 me-3">
                <i class="bi bi-star fs-4 text-warning"></i>
              </div>
              <div>
                <small class="text-muted d-block">Last Review</small>
                <span class="fw-bold">
                  <?= count($recentReviews) > 0 ? 
                      date('M j, Y', strtotime($recentReviews[0]['createdAt'])) : 
                      'No reviews yet' ?>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

<!-- Stats Cards -->
<div class="row mb-4 g-4">
  <!-- Listings Card -->
  <div class="col-xl-2 col-md-4">
    <div class="card stat-card border-0 h-100 transition-all hover-scale">
      <div class="card-body p-3 text-center">
        <div class="icon-wrapper bg-primary bg-opacity-10 mx-auto mb-3">
          <i class="bi bi-house-door-fill text-primary fs-4"></i>
        </div>
        <h3 class="fw-bold mb-1"><?= $stats['listings'] ?></h3>
        <p class="text-muted mb-0">Listings</p>
        <div class="progress mt-3" style="height: 4px;">
          <div class="progress-bar bg-primary" role="progressbar" style="width: <?= min(100, ($stats['listings']/10)*100) ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Revenue Card -->
  <div class="col-xl-2 col-md-4">
    <div class="card stat-card border-0 h-100 transition-all hover-scale">
      <div class="card-body p-3 text-center">
        <div class="icon-wrapper bg-success bg-opacity-10 mx-auto mb-3">
          <i class="bi bi-currency-exchange text-success fs-4"></i>
        </div>
        <h3 class="fw-bold mb-1">ETB <?= $stats['revenue'] ?></h3>
        <p class="text-muted mb-0">30-Day Revenue</p>
        <div class="progress mt-3" style="height: 4px;">
          <div class="progress-bar bg-success" role="progressbar" style="width: <?= min(100, ($stats['revenue']/50000)*100) ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bookings Card -->
  <div class="col-xl-2 col-md-4">
    <div class="card stat-card border-0 h-100 transition-all hover-scale">
      <div class="card-body p-3 text-center">
        <div class="icon-wrapper bg-info bg-opacity-10 mx-auto mb-3">
          <i class="bi bi-calendar-check-fill text-info fs-4"></i>
        </div>
        <h3 class="fw-bold mb-1"><?= $stats['bookings'] ?></h3>
        <p class="text-muted mb-0">Bookings</p>
        <div class="progress mt-3" style="height: 4px;">
          <div class="progress-bar bg-info" role="progressbar" style="width: <?= min(100, ($stats['bookings']/20)*100) ?>%"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Rating Card -->
  <div class="col-xl-2 col-md-4">
    <div class="card stat-card border-0 h-100 transition-all hover-scale">
      <div class="card-body p-3 text-center">
        <div class="icon-wrapper bg-warning bg-opacity-10 mx-auto mb-3">
          <i class="bi bi-star-fill text-warning fs-4"></i>
        </div>
        <h3 class="fw-bold mb-1"><?= $stats['rating'] ?: 'N/A' ?></h3>
        <p class="text-muted mb-0">Avg. Rating</p>
        <div class="rating-stars mt-2 justify-content-center">
          <?php 
          $rating = $stats['rating'] ? round($stats['rating']) : 0;
          echo str_repeat('<i class="bi bi-star-fill text-warning"></i>', $rating);
          echo str_repeat('<i class="bi bi-star text-warning"></i>', 5 - $rating);
          ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Reviews Card -->
  <div class="col-xl-2 col-md-4">
    <div class="card stat-card border-0 h-100 transition-all hover-scale">
      <div class="card-body p-3 text-center">
        <div class="icon-wrapper bg-danger bg-opacity-10 mx-auto mb-3">
          <i class="bi bi-chat-square-text-fill text-danger fs-4"></i>
        </div>
        <h3 class="fw-bold mb-1"><?= $stats['reviews'] ?></h3>
        <p class="text-muted mb-0">Reviews</p>
        <div class="progress mt-3" style="height: 4px;">
          <div class="progress-bar bg-danger" role="progressbar" style="width: <?= min(100, ($stats['reviews']/50)*100) ?>%"></div>
        </div>
      </div>
    </div>
  </div>

<!-- Quick Actions Card -->
<div class="col-xl-2 col-md-4">
  <div class="card stat-card border-0 h-100 transition-all hover-scale">
    <div class="card-body p-3 d-flex flex-column">
      <div class="d-flex align-items-center mb-3">
        <div class="icon-wrapper bg-primary bg-opacity-10 me-3">
          <i class="bi bi-lightning-fill text-primary fs-4"></i>
        </div>
        <h5 class="fw-bold mb-0">Quick Actions</h5>
      </div>
      
      <div class="mt-auto">
        <div class="d-grid gap-2">
          <a href="manage_listings.php?action=add" class="btn btn-primary btn-sm d-flex align-items-center justify-content-center">
            <i class="bi bi-plus-lg me-2"></i>
            <span>Add Listing</span>
          </a>
          <a href="booking_dashboard.php" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center">
            <i class="bi bi-calendar-check me-2"></i>
            <span>View Bookings</span>
          </a>
          <a href="payment_management.php" class="btn btn-outline-primary btn-sm d-flex align-items-center justify-content-center">
            <i class="bi bi-cash-coin me-2"></i>
            <span>Payments</span>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>

</div>
    <!-- Recent Activity Section -->
    <div class="row">
      <!-- Recent Bookings -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Recent Bookings</h5>
          </div>
          <div class="card-body">
            <?php if (count($recentBookings) > 0): ?>
              <div class="list-group list-group-flush">
                <?php foreach ($recentBookings as $booking): ?>
                  <div class="list-group-item border-0 px-0 py-3 recent-activity-item">
                    <div class="d-flex align-items-center">
                      <div class="activity-icon bg-primary bg-opacity-10 text-primary me-3">
                        <i class="bi bi-calendar-check"></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                          <h6 class="mb-1">Booking #<?= $booking['bookingID'] ?></h6>
                          <small class="text-muted"><?= date('M j', strtotime($booking['bookingDate'])) ?></small>
                        </div>
                        <p class="mb-1 small">
                          <span class="text-muted">From:</span> <?= $booking['touristName'] ?>
                          <span class="mx-2">|</span>
                          <span class="text-muted">Amount:</span> ETB <?= number_format($booking['totalAmount'], 2) ?>
                        </p>
                        <span class="badge bg-<?= $booking['status'] === 'completed' ? 'success' : ($booking['status'] === 'pending' ? 'warning' : 'info') ?>">
                          <?= ucfirst($booking['status']) ?>
                        </span>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="text-end mt-3">
                <a href="booking_dashboard.php" class="btn btn-sm btn-outline-primary">View All Bookings</a>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="bi bi-calendar-x fs-1 text-muted"></i>
                <p class="mt-2">No recent bookings</p>
                <a href="manage_listings.php" class="btn btn-sm btn-primary">Add Listings</a>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Recent Reviews -->
      <div class="col-lg-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
          <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="bi bi-star me-2"></i>Recent Reviews</h5>
          </div>
          <div class="card-body">
            <?php if (count($recentReviews) > 0): ?>
              <div class="list-group list-group-flush">
                <?php foreach ($recentReviews as $review): ?>
                  <div class="list-group-item border-0 px-0 py-3 recent-activity-item">
                    <div class="d-flex align-items-start">
                      <div class="activity-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-star-fill"></i>
                      </div>
                      <div class="flex-grow-1">
                        <div class="d-flex justify-content-between">
                          <h6 class="mb-1"><?= $review['touristName'] ?></h6>
                          <div class="rating-stars">
                            <?= str_repeat('<i class="bi bi-star-fill"></i>', $review['rating']) ?>
                            <?= str_repeat('<i class="bi bi-star"></i>', 5 - $review['rating']) ?>
                          </div>
                        </div>
                        <p class="mb-2 small"><?= $review['content'] ?></p>
                        <small class="text-muted"><?= date('M j, Y', strtotime($review['createdAt'])) ?></small>
                        <?php if (!empty($review['reply'])): ?>
                          <div class="bg-light p-2 mt-2 rounded">
                            <small class="text-primary fw-bold">Your reply:</small>
                            <p class="mb-0 small"><?= $review['reply'] ?></p>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
              <div class="text-end mt-3">
                <a href="review_and_rating.php" class="btn btn-sm btn-outline-primary">View All Reviews</a>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="bi bi-star fs-1 text-muted"></i>
                <p class="mt-2">No reviews yet</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>


  </div>
</div>



    





<script>
  // Business status toggle
  const businessStatus = document.getElementById('businessStatus');
  businessStatus.addEventListener('change', function() {
    const label = document.querySelector('label[for="businessStatus"]');
    if (this.checked) {
      label.textContent = 'ACTIVE';
      label.classList.remove('text-danger');
      label.classList.add('text-success');
    } else {
      label.textContent = 'INACTIVE';
      label.classList.remove('text-success');
      label.classList.add('text-danger');
    }
    
    // In a real app, you would send an AJAX request to update the status
    console.log('Business status changed to:', this.checked ? 'active' : 'inactive');
  });
</script>


</body>
</html>
