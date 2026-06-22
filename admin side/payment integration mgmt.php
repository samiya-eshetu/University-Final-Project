<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user sign up.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Payment Integration</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
</head>
<body>
   <div class="d-flex">
    <!-- Sidebar -->
    <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 250px; height: 100vh;">
        <a href="admin dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 text-decoration-none">
            <i class="bi bi-speedometer2 me-2 fs-4"></i>
            <span class="fs-5 fw-bold">Dashboard</span>
        </a>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
          <li><a href="admin dashboard.php" class="nav-link text-dark"><i class="bi bi-grid me-2"></i> Dashboard</a></li>
          <li><a href="business_management.php" class="nav-link text-dark"><i class="bi bi-people me-2"></i> Business Management</a></li>
          <li><a href="admin_payment_approval.php" class="nav-link text-dark"><i class="bi bi-cash-coin me-2"></i> Payment Approval</a></li>
          <li><a href="web content.php" class="nav-link text-dark"><i class="bi bi-pencil-square me-2"></i> Website Content</a></li>
          <li><a href="edit request.php" class="nav-link text-dark"><i class="bi bi-pencil me-2"></i> Edit Requests</a></li> 
          <li><a href="package managment.php" class="nav-link text-dark"><i class="bi bi-box2-heart-fill me-2"></i> Package Management</a></li>
          <li><a href="payment integration mgmt.php" class="nav-link active"><i class="bi bi-currency-exchange me-2"></i> Payment Integration</a></li>
          <li><a href="BookingCancellation.php" class="nav-link text-dark"><i class="bi bi-trash me-2"></i> Booking Cancellations</a></li>
          <li><a href="settings.php" class="nav-link text-dark"><i class="bi bi-gear me-2"></i> Settings</a></li>
        </ul>
        <hr>
        <div class="dropdown">
          <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle me-2 fs-4"></i>
            <strong>Admin Menu</strong>
          </a>
          <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="userDropdown">
            <li><span class="dropdown-item-text small">Logged in as <?php echo htmlspecialchars($_SESSION['email']); ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="../profile_mgmt.php"><i class="bi bi-person me-2"></i>Profile</a></li>
            <li><a class="dropdown-item" href="../index.php"><i class="bi bi-box me-2"></i>Main Homepage</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>
    </div>
  <!-- Main Content -->
  <div class="container-fluid p-4">
    <h4 class="fw-bold">Payment Integration Management</h4>

    <!-- Commission Settings -->
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title">Commission Settings</h5>
        <form class="row g-3">
          <div class="col-md-6">
            <label for="commissionRate" class="form-label">Commission Rate (%)</label>
            <input type="number" class="form-control" id="commissionRate" placeholder="e.g. 10">
          </div>
          <div class="col-md-6 d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Update Rate</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="table-responsive">
      <h5 class="fw-bold mb-3">Recent Transactions</h5>
      <table class="table table-bordered">
        <thead class="table-light">
          <tr>
            <th>Transaction ID</th>
            <th>Tourist</th>
            <th>Package Name</th>
            <th>Amount Paid</th>
            <th>Commission Taken</th>
            <th>Paid To</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>#T001</td>
            <td>John Doe</td>
            <td>Historic North Tour</td>
            <td>ETB 5,000</td>
            <td>ETB 500</td>
            <td>Heritage Tours</td>
            <td><span class="badge bg-warning text-dark">Pending</span></td>
            <td>2025-04-28</td>
            <td>
              <button class="btn btn-success btn-sm">Mark Paid</button>
              <button class="btn btn-danger btn-sm">Dispute</button>
            </td>
          </tr>
          <tr>
            <td>#T002</td>
            <td>Jane Smith</td>
            <td>Simien Trekking</td>
            <td>ETB 7,200</td>
            <td>ETB 720</td>
            <td>Simien Adventures</td>
            <td><span class="badge bg-success">Paid</span></td>
            <td>2025-04-27</td>
            <td>
              <button class="btn btn-outline-secondary btn-sm" disabled>Paid</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</body>
</html>
