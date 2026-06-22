<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user sign up.php");
    exit();
}

// Database connection
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle payment approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $payoutID = (int)$_POST['payout_id'];
        $newStatus = $_POST['new_status'] === 'Paid' ? 'Paid' : 'Rejected';
        
        $update = $conn->query("UPDATE payout_history SET status = '$newStatus' WHERE payoutID = $payoutID");
        
        if ($update) {
            $_SESSION['success'] = "Payout #$payoutID status updated to $newStatus";
        } else {
            $_SESSION['error'] = "Failed to update payout: " . $conn->error;
        }
        
        header("Location: admin_payment_approval.php");
        exit();
    }
}

// Get pending payout requests
$pendingPayouts = $conn->query("
    SELECT ph.*, 
           a.email AS owner_email,
           COALESCE(ho.businessName, ro.businessName, to_owners.businessName) AS business_name,
           COALESCE(ho.fullName, ro.fullName, to_owners.fullName) AS owner_name,
           CASE 
               WHEN ho.ownerID IS NOT NULL THEN 'Hotel'
               WHEN ro.ownerID IS NOT NULL THEN 'Ride'
               WHEN to_owners.ownerID IS NOT NULL THEN 'Tour'
               ELSE 'Unknown'
           END AS business_type
    FROM payout_history ph
    JOIN accounts a ON ph.business_owner_id = a.accountID
    LEFT JOIN hotel_owners ho ON ph.business_owner_id = ho.ownerID
    LEFT JOIN ride_owners ro ON ph.business_owner_id = ro.ownerID
    LEFT JOIN tour_owners to_owners ON ph.business_owner_id = to_owners.ownerID
    WHERE ph.status = 'Pending'
    ORDER BY ph.payout_date DESC
");

// Get payout history
$payoutHistory = $conn->query("
    SELECT ph.*, 
           a.email AS owner_email,
           COALESCE(ho.businessName, ro.businessName, to_owners.businessName) AS business_name,
           COALESCE(ho.fullName, ro.fullName, to_owners.fullName) AS owner_name,
           CASE 
               WHEN ho.ownerID IS NOT NULL THEN 'Hotel'
               WHEN ro.ownerID IS NOT NULL THEN 'Ride'
               WHEN to_owners.ownerID IS NOT NULL THEN 'Tour'
               ELSE 'Unknown'
           END AS business_type
    FROM payout_history ph
    JOIN accounts a ON ph.business_owner_id = a.accountID
    LEFT JOIN hotel_owners ho ON ph.business_owner_id = ho.ownerID
    LEFT JOIN ride_owners ro ON ph.business_owner_id = ro.ownerID
    LEFT JOIN tour_owners to_owners ON ph.business_owner_id = to_owners.ownerID
    WHERE ph.status != 'Pending'
    ORDER BY ph.payout_date DESC
    LIMIT 50
");

// Statistics for dashboard
$stats = $conn->query("
    SELECT 
        SUM(CASE WHEN status = 'Pending' THEN amount ELSE 0 END) AS pending_amount,
        SUM(CASE WHEN status = 'Paid' THEN amount ELSE 0 END) AS paid_amount,
        SUM(CASE WHEN status = 'Rejected' THEN amount ELSE 0 END) AS rejected_amount,
        COUNT(CASE WHEN status = 'Pending' THEN 1 END) AS pending_count,
        COUNT(CASE WHEN status = 'Paid' THEN 1 END) AS paid_count,
        COUNT(CASE WHEN status = 'Rejected' THEN 1 END) AS rejected_count
    FROM payout_history
")->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Payment Approval</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    :root {
      --primary-color: #4e73df;
      --success-color: #1cc88a;
      --warning-color: #f6c23e;
      --danger-color: #e74a3b;
      --secondary-color: #858796;
      --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
      --card-hover-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
    }
    
    .main-content {
      background-color: #f8f9fa;
      min-height: 100vh;
    }
    
    .stat-card {
      border-left: 4px solid;
      transition: all 0.3s;
      border-radius: 0.5rem;
    }
    
    .stat-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--card-hover-shadow);
    }
    
    .stat-pending {
      border-left-color: var(--warning-color);
      background: linear-gradient(to right, #fff, #fff8e6);
    }
    
    .stat-paid {
      border-left-color: var(--success-color);
      background: linear-gradient(to right, #fff, #e6f7f0);
    }
    
    .stat-rejected {
      border-left-color: var(--danger-color);
      background: linear-gradient(to right, #fff, #fce8e6);
    }
    
    .method-badge {
      font-size: 0.75rem;
      padding: 0.35em 0.65em;
      border-radius: 0.25rem;
    }
    
    .method-telebirr {
      background-color: rgba(0, 123, 255, 0.1);
      color: #007bff;
    }
    
    .method-cbe {
      background-color: rgba(40, 167, 69, 0.1);
      color: #28a745;
    }
    
    .method-cash {
      background-color: rgba(108, 117, 125, 0.1);
      color: #6c757d;
    }
    
    .method-paypal {
      background-color: rgba(0, 123, 255, 0.1);
      color: #003087;
    }
    
    .payout-card {
      border-left: 4px solid var(--warning-color);
      border-radius: 0.5rem;
      transition: all 0.3s;
    }
    
    .payout-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--card-hover-shadow);
    }
    
    .payout-card.paid {
      border-left-color: var(--success-color);
    }
    
    .payout-card.rejected {
      border-left-color: var(--danger-color);
    }
    
    .nav-tabs .nav-link {
      border: none;
      color: #6c757d;
      font-weight: 500;
      padding: 0.75rem 1.5rem;
    }
    
    .nav-tabs .nav-link.active {
      color: var(--primary-color);
      border-bottom: 3px solid var(--primary-color);
      background-color: transparent;
    }
    
    .table th {
      border-top: none;
      font-weight: 600;
      color: #6c757d;
    }
    
    .icon-circle {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .card-header {
      background-color: white;
      border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    .badge {
      font-weight: 500;
      padding: 0.35em 0.65em;
    }
  </style>
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
          <li><a href="admin_payment_approval.php" class="nav-link active"><i class="bi bi-cash-coin me-2"></i> Payment Approval</a></li>
          <li><a href="web content.php" class="nav-link text-dark"><i class="bi bi-pencil-square me-2"></i> Website Content</a></li>
          <li><a href="edit request.php" class="nav-link text-dark"><i class="bi bi-pencil me-2"></i> Edit Requests</a></li> 
          <li><a href="package managment.php" class="nav-link text-dark"><i class="bi bi-box2-heart-fill me-2"></i> Package Management</a></li>
          <li><a href="BookingCancellation.php" class="nav-link text-dark"><i class="bi bi-trash me-2"></i> Booking Cancellations</a></li>
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

   <div class="main-content flex-grow-1 p-4">
        <!-- Alerts -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn mb-4">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeIn mb-4">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Payment Approvals</h2>
            <div class="d-flex">
                <input type="text" id="searchInput" class="form-control" placeholder="Search payouts..." style="width: 250px;">
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card stat-card stat-pending shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted mb-0">Pending Payouts</h6>
                                <h3 class="mb-0"><?php echo $stats['pending_count']; ?></h3>
                            </div>
                            <div class="icon-circle bg-warning text-white">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 text-muted">
                            <span class="fw-bold">ETB <?php echo number_format($stats['pending_amount'], 2); ?></span> pending approval
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card stat-paid shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted mb-0">Approved Payouts</h6>
                                <h3 class="mb-0"><?php echo $stats['paid_count']; ?></h3>
                            </div>
                            <div class="icon-circle bg-success text-white">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 text-muted">
                            <span class="fw-bold">ETB <?php echo number_format($stats['paid_amount'], 2); ?></span> paid to businesses
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card stat-card stat-rejected shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="text-uppercase text-muted mb-0">Rejected Payouts</h6>
                                <h3 class="mb-0"><?php echo $stats['rejected_count']; ?></h3>
                            </div>
                            <div class="icon-circle bg-danger text-white">
                                <i class="bi bi-x-circle"></i>
                            </div>
                        </div>
                        <p class="mt-3 mb-0 text-muted">
                            <span class="fw-bold">ETB <?php echo number_format($stats['rejected_amount'], 2); ?></span> rejected requests
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="card shadow-sm mb-4">
            <div class="card-body p-0">
                <ul class="nav nav-tabs" id="payoutTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                            <i class="bi bi-hourglass-split me-1"></i> Pending Approval
                            <?php if ($pendingPayouts->num_rows > 0): ?>
                                <span class="badge bg-danger ms-1"><?php echo $pendingPayouts->num_rows; ?></span>
                            <?php endif; ?>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i> Approval History
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content p-4" id="payoutTabContent">
                    <!-- Pending Approvals Tab -->
                    <div class="tab-pane fade show active" id="pending" role="tabpanel">
                        <?php if ($pendingPayouts->num_rows > 0): ?>
                            <div class="row row-cols-1 row-cols-md-2 g-4" id="pendingPayoutsContainer">
                                <?php while ($payout = $pendingPayouts->fetch_assoc()): 
                                    $methodClass = 'method-' . strtolower(str_replace(' ', '-', $payout['method']));
                                ?>
                                <div class="col">
                                    <div class="card h-100 payout-card">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0">Payout #<?php echo $payout['payoutID']; ?></h5>
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <small class="text-muted">Business</small>
                                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($payout['business_name'] ?? 'N/A'); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Owner</small>
                                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($payout['owner_name'] ?? 'N/A'); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Type</small>
                                                        <p class="mb-2 fw-medium"><?php echo htmlspecialchars($payout['business_type']); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Request Date</small>
                                                        <p class="mb-2 fw-medium"><?php echo date('M j, Y', strtotime($payout['payout_date'])); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Amount</small>
                                                        <p class="mb-2 fw-bold text-primary">ETB <?php echo number_format($payout['amount'], 2); ?></p>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">Method</small>
                                                        <p class="mb-2">
                                                            <span class="badge <?php echo $methodClass; ?>">
                                                                <?php echo htmlspecialchars($payout['method']); ?>
                                                            </span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between mt-3">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="payout_id" value="<?php echo $payout['payoutID']; ?>">
                                                    <input type="hidden" name="new_status" value="Rejected">
                                                    <button type="submit" name="update_status" class="btn btn-outline-danger">
                                                        <i class="bi bi-x-lg me-1"></i> Reject
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="payout_id" value="<?php echo $payout['payoutID']; ?>">
                                                    <input type="hidden" name="new_status" value="Paid">
                                                    <button type="submit" name="update_status" class="btn btn-success">
                                                        <i class="bi bi-check-lg me-1"></i> Approve
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                <i class="bi bi-check-circle me-2"></i> No pending payout requests at this time.
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Approval History Tab -->
                    <div class="tab-pane fade" id="history" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Business</th>
                                        <th>Owner</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Request Date</th>
                                        <th>Status</th>
                                        <th>Processed Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($payoutHistory->num_rows > 0): ?>
                                        <?php $payoutHistory->data_seek(0); ?>
                                        <?php while ($payout = $payoutHistory->fetch_assoc()): 
                                            $methodClass = 'method-' . strtolower(str_replace(' ', '-', $payout['method']));
                                        ?>
                                        <tr>
                                            <td class="fw-medium">#<?php echo $payout['payoutID']; ?></td>
                                            <td><?php echo htmlspecialchars($payout['business_name'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($payout['owner_name'] ?? 'N/A'); ?></td>
                                            <td class="fw-bold">ETB <?php echo number_format($payout['amount'], 2); ?></td>
                                            <td>
                                                <span class="badge <?php echo $methodClass; ?>">
                                                    <?php echo htmlspecialchars($payout['method']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($payout['payout_date'])); ?></td>
                                            <td>
                                                <?php if ($payout['status'] === 'Paid'): ?>
                                                    <span class="badge bg-success">Approved</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Rejected</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo isset($payout['processed_date']) ? date('M j, Y', strtotime($payout['processed_date'])) : 'N/A'; ?></td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">
                                                <i class="bi bi-clock-history fs-4 d-block mb-2"></i>
                                                No payout history found
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#pendingPayoutsContainer .col, #payoutHistoryContainer tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
});
</script>
</body>
</html>