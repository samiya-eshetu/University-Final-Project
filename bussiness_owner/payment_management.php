<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}

$accountID = $_SESSION['accountID'];

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Find which type of owner they are (hotel, ride, or tour)
$ownerTables = ['hotel_owners', 'ride_owners', 'tour_owners'];
$ownerType = null;

foreach ($ownerTables as $table) {
    $result = $conn->query("SELECT ownerID FROM $table WHERE ownerID = $accountID");
    if ($result && $result->num_rows > 0) {
        $ownerType = $table;
        break;
    }
}

if (!$ownerType) {
    header("Location: ../index.php");
}

// Fetch business owner ID (same as accountID in your DB structure)
$business_owner_id = $accountID;

// Get all service IDs (hotels/rides/tours) this owner owns
$serviceType = str_replace('_owners', '', $ownerType); // 'hotel', 'ride', or 'tour'
$serviceIDField = $serviceType . "ID";

// For tours, we need to get packageIDs instead of tourIDs
if ($serviceType == 'tour') {
    $services = $conn->query("
        SELECT p.packageID 
        FROM tour_packages p
        JOIN tours t ON p.tourID = t.tourID
        WHERE t.ownerID = $business_owner_id
    ");
} else {
    $services = $conn->query("SELECT $serviceIDField FROM {$serviceType}s WHERE ownerID = $business_owner_id");
}

$serviceIDs = [];
while ($row = $services->fetch_assoc()) {
    $serviceIDs[] = $row[$serviceType == 'tour' ? 'packageID' : $serviceIDField];
}
$serviceIDList = implode(',', $serviceIDs);

// Initialize values
$totalCompletedEarnings = 0;
$totalPayout = 0;
$pendingPayout = 0;
$paidOut = 0;

// Get earnings only from this owner's services
if (!empty($serviceIDList)) {
    $result = $conn->query("
        SELECT SUM(totalAmount) as total 
        FROM bookings 
        WHERE status = 'completed' 
          AND paymentStatus = 'paid'
          AND serviceType = '$serviceType'
          AND serviceID IN ($serviceIDList)
    ");
    $totalCompletedEarnings = $result->fetch_assoc()['total'] ?? 0;
}

// Get total payouts
$result = $conn->query("SELECT SUM(amount) as total_payout FROM payout_history WHERE business_owner_id = $business_owner_id");
$totalPayout = $result->fetch_assoc()['total_payout'] ?? 0;

// Withdrawable balance
$withdrawableBalance = $totalCompletedEarnings - $totalPayout;
    
// Pending payout
$result = $conn->query("SELECT SUM(amount) as pending FROM payout_history WHERE business_owner_id = $business_owner_id AND status = 'Pending'");
$pendingPayout = $result->fetch_assoc()['pending'] ?? 0;

// Paid out
$result = $conn->query("SELECT SUM(amount) as paid FROM payout_history WHERE business_owner_id = $business_owner_id AND status = 'Paid'");
$paidOut = $result->fetch_assoc()['paid'] ?? 0;

// Handle payout request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_payout'])) {
    $amount = (float)$_POST['amount'];
    if ($amount > $withdrawableBalance || $amount <= 0) {
        $errorMessage = "Invalid amount. You can only request up to ETB " . number_format($withdrawableBalance, 2);
    } else {
        $method = $conn->real_escape_string($_POST['method']);
        $today = date('Y-m-d');
        $conn->query("
            INSERT INTO payout_history (business_owner_id, payout_date, amount, method, status)
            VALUES ($business_owner_id, '$today', $amount, '$method', 'Pending')
        ");
        $successMessage = "Payout request of ETB " . number_format($amount, 2) . " submitted!";
        header("Location: payment_management.php?success=" . urlencode($successMessage));
        exit();
    }
}

// Get recent paid bookings for this owner's services
$customerPayments = [];
if (!empty($serviceIDList)) {
    $result = $conn->query("
        SELECT b.bookingID, b.serviceType, b.bookingDate, b.totalAmount, b.paymentStatus, b.status, t.fullName
        FROM bookings b
        JOIN tourists t ON b.touristID = t.touristID
        WHERE b.status = 'completed' AND b.paymentStatus = 'paid'
          AND b.serviceType = '$serviceType'
          AND b.serviceID IN ($serviceIDList)
        ORDER BY b.bookingDate DESC
        LIMIT 5
    ");
    while ($row = $result->fetch_assoc()) {
        $customerPayments[] = $row;
    }
}

// Get payout history
$payoutHistory = $conn->query("SELECT * FROM payout_history WHERE business_owner_id = $business_owner_id ORDER BY payout_date DESC");
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Payment Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
</head>
<body>

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
        <i class="bi bi-calendar-check me-2"></i> Bookings
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

<!-- Main content -->
<div class="p-4 flex-grow-1" style="margin-left: 250px;">



<!-- Earnings Overview Cards -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card border-primary shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-uppercase"><i class="bi bi-bar-chart-fill me-2"></i>Total Completed Earnings</h5>
        <p class="fs-4 fw-bold text-primary mb-0">ETB <?= number_format($totalCompletedEarnings, 2) ?></p>
        <small class="text-muted">From completed & paid bookings</small>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-success shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-uppercase"><i class="bi bi-wallet2 me-2"></i>Withdrawable Balance</h5>
        <p class="fs-4 fw-bold text-success mb-0">ETB <?= number_format($withdrawableBalance, 2) ?></p>
        <small class="text-muted">You can request this amount</small>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card border-secondary shadow-sm">
      <div class="card-body">
        <h5 class="card-title text-uppercase"><i class="bi bi-cash-coin me-2"></i>Total Requested</h5>
        <p class="fs-4 fw-bold text-secondary mb-0">ETB <?= number_format($totalPayout, 2) ?></p>
        <small class="text-muted">All-time payout requests</small>
      </div>
    </div>
  </div>
</div>


<!-- Alerts -->
<?php if (isset($_GET['success'])): ?>
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($_GET['success']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php elseif (isset($errorMessage)): ?>
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($errorMessage) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>




<!-- Animated Payout Request -->
<div class="card mb-4 animate__animated animate__fadeIn">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Request a Payout</h5>
    <span class="badge bg-light text-dark"><i class="bi bi-info-circle me-1"></i> Minimum: ETB 100.00</span>
  </div>
  <div class="card-body">
    <form method="POST" class="row g-3 payout-form">
      <div class="col-md-5">
        <label for="amount" class="form-label fw-medium">Amount (ETB)</label>
        <div class="input-group">
          <span class="input-group-text bg-light"><i class="bi bi-currency-exchange"></i></span>
          <input type="number" name="amount" step="0.01" min="100" max="<?= $withdrawableBalance ?>"
                 class="form-control" placeholder="Enter amount" required
                 value="$withdrawableBalance) ?>">
          <span class="input-group-text bg-light">Max: ETB <?= number_format($withdrawableBalance, 2) ?></span>
        </div>
      </div>
      <div class="col-md-5">
        <label for="method" class="form-label fw-medium">Payout Method</label>
        <select name="method" class="form-select" required>
          <option value="">-- Select Method --</option>
          <option value="Telebirr">Telebirr</option>
          <option value="CBE" selected>CBE</option>
          <option value="Cash Pickup">Cash Pickup</option>
          <option value="Bank Transfer">Bank Transfer</option>
        </select>
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" name="request_payout" class="btn btn-primary w-100">
          <i class="bi bi-send-check me-1"></i> Request
        </button>

      </div>
    </form>
  </div>
</div>







<div class="row">
  <!-- Recent Customer Payments -->
  <div class="col-lg-6 mb-4">
    <div class="card h-100 animate__animated animate__fadeInLeft shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-cash-coin me-2 text-success"></i>Recent Customer Payments</h5>
        <a href="#" class="btn btn-sm btn-outline-secondary"><i class="bi bi-eye"></i> View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-borderless mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>#Booking</th>
                <th>Customers Name</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $recentPayments = array_filter($customerPayments, function($p) {
                    return $p['paymentStatus'] === 'paid';
                });
              $sliced = array_slice($recentPayments, 0, 5);
              ?>
              <?php foreach ($sliced as $payment): ?>
                <tr>
                  <td>#<?= htmlspecialchars($payment['bookingID']) ?></td>
                  <td><?= htmlspecialchars($payment['fullName']) ?></td>

                  <td><?= date('M j, Y', strtotime($payment['bookingDate'])) ?></td>
                  <td class="fw-medium">ETB <?= number_format($payment['totalAmount'], 2) ?></td>
                  <td>
                    <span class="badge bg-success"><?= ucfirst($payment['paymentStatus']) ?></span>
                  
                    </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($sliced)): ?>
                <tr>
                  <td colspan="4" class="text-center py-4 text-muted">
                    <i class="bi bi-emoji-frown fs-1 opacity-50 d-block mb-2"></i>
                    No paid bookings yet.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Payout History -->
  <div class="col-lg-6 mb-4">
    <div class="card h-100 animate__animated animate__fadeInRight shadow-sm">
      <div class="card-header bg-white">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Payout History</h5>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover table-striped mb-0 align-middle">
            <thead class="table-light">
              <tr>
                <th>#</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Method</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php $index = 1; ?>
              <?php while ($payout = $payoutHistory->fetch_assoc()): ?>
                <tr>
                  <td><?= $index++ ?></td>
                  <td><?= htmlspecialchars($payout['payout_date']) ?></td>
                  <td><strong>ETB <?= number_format($payout['amount'], 2) ?></strong></td>
                  <td><i class="bi bi-bank"></i> <?= htmlspecialchars($payout['method']) ?></td>
                  <td>
                    <span class="badge bg-<?= 
                      $payout['status'] === 'Paid' ? 'success' : 
                      ($payout['status'] === 'Pending' ? 'warning text-dark' : 'secondary') ?>">
                      <?= htmlspecialchars($payout['status']) ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
              <?php if ($index === 1): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">No payout history found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
