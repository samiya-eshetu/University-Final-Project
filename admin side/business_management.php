<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../user sign up.php");
    exit();
}

// Database connection
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle approval/rejection
// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $ownerID = $_POST['ownerID'];
    $type = $_POST['type'];
    
    $table = $type . "_owners";
    
    if ($action === 'approve') {
        $status = 'approved';
        $message = "Business owner approved successfully";
        
        // Get the owner's email to update their role
        $emailStmt = $conn->prepare("SELECT email FROM $table WHERE ownerID = ?");
        $emailStmt->bind_param("i", $ownerID);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();
        $ownerData = $emailResult->fetch_assoc();
        $emailStmt->close();
        
        if ($ownerData) {
            $ownerEmail = $ownerData['email'];
            // Map table names to role values
            $roleMap = [
                'hotel_owners' => 'hotel_owner',
                'tour_owners' => 'tour_owner', 
                'ride_owners' => 'ride_owner'
            ];
            $newRole = $roleMap[$table];
            
            // Update user role in accounts table
            $roleStmt = $conn->prepare("UPDATE accounts SET role = ? WHERE accountID = ?");
            $roleStmt->bind_param("si", $newRole, $ownerID);
            $roleStmt->execute();
            $roleStmt->close();
        }
    } elseif ($action === 'reject') {
        $status = 'rejected';
        $message = "Business owner rejected";
    }
    
    // Update owner status
    $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE ownerID = ?");
    $stmt->bind_param("si", $status, $ownerID);
    
    if ($stmt->execute()) {
        // If it's a tour operator, also update the tours table
        if ($type === 'tour') {
            $tourStmt = $conn->prepare("UPDATE tours SET status = ? WHERE ownerID = ?");
            $tourStmt->bind_param("si", $status, $ownerID);
            $tourStmt->execute();
            $tourStmt->close();
        }
        
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "Error updating status: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: business_management.php");
    exit();

    
    // Update owner status
    $stmt = $conn->prepare("UPDATE $table SET status = ? WHERE ownerID = ?");
    $stmt->bind_param("si", $status, $ownerID);
    
    if ($stmt->execute()) {
        // If it's a tour operator, also update the tours table
        if ($type === 'tour') {
            $tourStmt = $conn->prepare("UPDATE tours SET status = ? WHERE ownerID = ?");
            $tourStmt->bind_param("si", $status, $ownerID);
            $tourStmt->execute();
            $tourStmt->close();
        }
        
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = "Error updating status: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: business_management.php");
    exit();
}

// Fetch pending owners
$pendingOwners = [];
$types = ['hotel', 'ride', 'tour'];

foreach ($types as $type) {
    $table = $type . "_owners";
    $result = $conn->query("SELECT * FROM $table WHERE status = 'pending'");
    
    while ($row = $result->fetch_assoc()) {
        $row['type'] = $type;
        $pendingOwners[] = $row;
    }
}

// Fetch approved owners
$approvedOwners = [];
foreach ($types as $type) {
    $table = $type . "_owners";
    $result = $conn->query("SELECT * FROM $table WHERE status = 'approved'");
    
    while ($row = $result->fetch_assoc()) {
        $row['type'] = $type;
        $approvedOwners[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Business Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    :root {
      --hotel-color: #4e73df;
      --ride-color: #f6c23e;
      --tour-color: #1cc88a;
      --pending-color: #6c757d;
      --approved-color: #1cc88a;
      --rejected-color: #e74a3b;
      --card-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
      --card-hover-shadow: 0 0.5rem 1.5rem 0 rgba(58, 59, 69, 0.2);
    }
    
    .document-preview {
      max-height: 300px;
      overflow-y: auto;
      border: 1px solid #dee2e6;
      border-radius: 0.5rem;
      padding: 15px;
      background-color: #f8f9fa;
      transition: all 0.3s ease;
    }
    
    .document-preview:hover {
      box-shadow: var(--card-shadow);
    }
    
    .owner-card {
      transition: all 0.3s ease;
      border-left: 5px solid;
      border-radius: 0.5rem;
      box-shadow: var(--card-shadow);
      overflow: hidden;
    }
    
    .owner-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--card-hover-shadow);
    }
    
    .hotel-card {
      border-left-color: var(--hotel-color);
    }
    
    .ride-card {
      border-left-color: var(--ride-color);
    }
    
    .tour-card {
      border-left-color: var(--tour-color);
    }
    
    .badge-hotel {
      background-color: var(--hotel-color);
    }
    
    .badge-ride {
      background-color: var(--ride-color);
    }
    
    .badge-tour {
      background-color: var(--tour-color);
    }
    
    .badge-pending {
      background-color: var(--pending-color);
    }
    
    .badge-approved {
      background-color: var(--approved-color);
    }
    
    .badge-rejected {
      background-color: var(--rejected-color);
    }
    
    .card-header {
      border-bottom: none;
      background: rgba(255, 255, 255, 0.9);
      padding: 1.25rem 1.5rem;
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .action-buttons .btn {
      border-radius: 0.375rem;
      font-weight: 500;
      padding: 0.5rem 1rem;
      transition: all 0.2s;
    }
    
    .action-buttons .btn:hover {
      transform: translateY(-2px);
    }
    
    .section-title {
      position: relative;
      padding-bottom: 0.75rem;
      margin-bottom: 1.5rem;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 50px;
      height: 3px;
      background: var(--hotel-color);
      border-radius: 3px;
    }
    
    .search-box {
      max-width: 300px;
      transition: all 0.3s;
    }
    
    .search-box:focus-within {
      max-width: 350px;
    }
    
    .empty-state {
      padding: 3rem;
      text-align: center;
      background-color: #f8f9fa;
      border-radius: 0.5rem;
      box-shadow: var(--card-shadow);
    }
    
    .empty-state i {
      font-size: 3rem;
      color: #d1d3e2;
      margin-bottom: 1rem;
    }
    
    .tab-content {
      padding: 1.5rem 0;
    }
    
    .nav-tabs .nav-link {
      border: none;
      color: #6c757d;
      font-weight: 500;
      padding: 0.75rem 1.5rem;
      transition: all 0.3s;
    }
    
    .nav-tabs .nav-link.active {
      color: var(--hotel-color);
      border-bottom: 3px solid var(--hotel-color);
      background: transparent;
    }
    
    .nav-tabs .nav-link:hover:not(.active) {
      color: var(--hotel-color);
    }
    
    .status-indicator {
      width: 10px;
      height: 10px;
      border-radius: 50%;
      display: inline-block;
      margin-right: 5px;
    }
    
    .status-pending {
      background-color: var(--pending-color);
    }
    
    .status-approved {
      background-color: var(--approved-color);
    }
    
    .status-rejected {
      background-color: var(--rejected-color);
    }
    
    .table-hover tbody tr {
      transition: all 0.2s;
    }
    
    .table-hover tbody tr:hover {
      transform: translateX(5px);
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .type-icon {
      font-size: 1.25rem;
      margin-right: 8px;
      vertical-align: middle;
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
          <li><a href="business_management.php" class="nav-link active"><i class="bi bi-people me-2"></i> Business Management</a></li>
          <li><a href="admin_payment_approval.php" class="nav-link text-dark"><i class="bi bi-cash-coin me-2"></i> Payment Approval</a></li>
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

    <div class="container-fluid p-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold section-title">Pending Business Applications</h4>
            <div class="input-group search-box">
                <input type="text" id="searchPending" class="form-control border-end-0" placeholder="Search applications...">
                <span class="input-group-text bg-white border-start-0">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </div>
        
        <?php if (empty($pendingOwners)): ?>
            <div class="empty-state animate__animated animate__fadeIn">
                <i class="bi bi-inbox"></i>
                <h5 class="text-muted">No pending applications</h5>
                <p class="text-muted">When new business applications arrive, they'll appear here.</p>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-5" id="pendingOwnersContainer">
                <?php foreach ($pendingOwners as $owner): 
                    $typeClass = $owner['type'] . '-card';
                    $typeLabel = ucfirst($owner['type']);
                    $documentPath = $owner['business_document'];
                    $documentName = basename($documentPath);
                    $icon = $owner['type'] === 'hotel' ? 'building' : 
                           ($owner['type'] === 'ride' ? 'car-front' : 'signpost');
                ?>
                <div class="col animate__animated animate__fadeInUp">
                    <div class="card h-100 owner-card <?php echo $typeClass; ?>">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-<?php echo $icon; ?> me-2"></i>
                                    <?php echo htmlspecialchars($owner['businessName']); ?>
                                </h5>
                            </div>
                            <span class="badge badge-pending">
                                <span class="status-indicator status-pending"></span>
                                Pending
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <div class="mb-3">
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong><i class="bi bi-person me-2"></i>Owner:</strong></p>
                                        <p><?php echo htmlspecialchars($owner['fullName']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong><i class="bi bi-geo-alt me-2"></i>Location:</strong></p>
                                        <p><?php echo htmlspecialchars($owner['location']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong><i class="bi bi-telephone me-2"></i>Phone:</strong></p>
                                        <p><?php echo htmlspecialchars($owner['phoneNumber']); ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p class="mb-2"><strong><i class="bi bi-envelope me-2"></i>Email:</strong></p>
                                        <p><?php echo htmlspecialchars($owner['email']); ?></p>
                                    </div>
                                    <div class="col-12">
                                        <p class="mb-2"><strong><i class="bi bi-calendar me-2"></i>Applied On:</strong></p>
                                        <p><?php echo date('M j, Y', strtotime($owner['date_applied'])); ?></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="d-flex align-items-center mb-3">
                                    <i class="bi bi-file-earmark-text me-2"></i> Business Document
                                </h6>
                                <div class="document-preview">
                                    <?php if (pathinfo($documentPath, PATHINFO_EXTENSION) === 'pdf'): ?>
                                        <embed src="<?php echo htmlspecialchars($documentPath); ?>" type="application/pdf" width="100%" height="200px">
                                    <?php elseif (in_array(pathinfo($documentPath, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                                        <img src="<?php echo htmlspecialchars($documentPath); ?>" class="img-fluid rounded" alt="Business document">
                                    <?php else: ?>
                                        <div class="text-center py-3">
                                            <i class="bi bi-file-earmark-text fs-1 text-muted"></i>
                                            <p class="mt-2">Document: <a href="<?php echo htmlspecialchars($documentPath); ?>" target="_blank"><?php echo htmlspecialchars($documentName); ?></a></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="action-buttons d-flex justify-content-between">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="reject">
                                    <input type="hidden" name="ownerID" value="<?php echo $owner['ownerID']; ?>">
                                    <input type="hidden" name="type" value="<?php echo $owner['type']; ?>">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="bi bi-x-lg me-1"></i> Reject
                                    </button>
                                </form>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="ownerID" value="<?php echo $owner['ownerID']; ?>">
                                    <input type="hidden" name="type" value="<?php echo $owner['type']; ?>">
                                    <button type="submit" class="btn btn-success">
                                        <i class="bi bi-check-lg me-1"></i> Approve
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <h4 class="fw-bold section-title">Approved Businesses</h4>
            <div class="input-group search-box">
                <input type="text" id="searchApproved" class="form-control border-end-0" placeholder="Search businesses...">
                <span class="input-group-text bg-white border-start-0">
                    <i class="bi bi-search"></i>
                </span>
            </div>
        </div>
        
        <?php if (empty($approvedOwners)): ?>
            <div class="empty-state animate__animated animate__fadeIn">
                <i class="bi bi-check-circle"></i>
                <h5 class="text-muted">No approved businesses yet</h5>
                <p class="text-muted">Approved businesses will appear here for management.</p>
            </div>
        <?php else: ?>
            <ul class="nav nav-tabs" id="approvedTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-approved" type="button" role="tab">
                        All Businesses
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="hotels-tab" data-bs-toggle="tab" data-bs-target="#hotels-approved" type="button" role="tab">
                        <i class="bi bi-building type-icon"></i>Hotels
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="rides-tab" data-bs-toggle="tab" data-bs-target="#rides-approved" type="button" role="tab">
                        <i class="bi bi-car-front type-icon"></i>Rides
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tours-tab" data-bs-toggle="tab" data-bs-target="#tours-approved" type="button" role="tab">
                        <i class="bi bi-signpost type-icon"></i>Tours
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="approvedTabContent">
                <div class="tab-pane fade show active" id="all-approved" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Business</th>
                                    <th>Type</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Date Approved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="approvedOwnersContainer">
                                <?php foreach ($approvedOwners as $owner): 
                                    $typeLabel = ucfirst($owner['type']);
                                    $icon = $owner['type'] === 'hotel' ? 'building' : 
                                           ($owner['type'] === 'ride' ? 'car-front' : 'signpost');
                                ?>
                                <tr class="animate__animated animate__fadeIn">
                                    <td>
                                        <strong><?php echo htmlspecialchars($owner['businessName']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['fullName']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $owner['type']; ?>">
                                            <i class="bi bi-<?php echo $icon; ?> me-1"></i>
                                            <?php echo $typeLabel; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($owner['phoneNumber']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($owner['location']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($owner['date_applied'])); ?></td>
                                    <td>

                                    <button class="btn btn-sm btn-outline-primary view-document" 
                                            data-document="<?php echo htmlspecialchars($owner['business_document']); ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#documentModal">
                                        <i class="bi bi-file-earmark-text"></i> View
                                    </button>

                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="hotels-approved" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Hotel</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Date Approved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedOwners as $owner): 
                                    if ($owner['type'] !== 'hotel') continue;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($owner['businessName']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['fullName']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($owner['phoneNumber']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($owner['location']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($owner['date_applied'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-document" 
                                                data-document="<?php echo htmlspecialchars($owner['business_document']); ?>">
                                            <i class="bi bi-file-earmark-text"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="rides-approved" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Ride Service</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Date Approved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedOwners as $owner): 
                                    if ($owner['type'] !== 'ride') continue;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($owner['businessName']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['fullName']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($owner['phoneNumber']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($owner['location']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($owner['date_applied'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-document" 
                                                data-document="<?php echo htmlspecialchars($owner['business_document']); ?>">
                                            <i class="bi bi-file-earmark-text"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="tours-approved" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tour Operator</th>
                                    <th>Contact</th>
                                    <th>Location</th>
                                    <th>Date Approved</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($approvedOwners as $owner): 
                                    if ($owner['type'] !== 'tour') continue;
                                ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($owner['businessName']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['fullName']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($owner['phoneNumber']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($owner['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($owner['location']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($owner['date_applied'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-document" 
                                                data-document="<?php echo htmlspecialchars($owner['business_document']); ?>">
                                            <i class="bi bi-file-earmark-text"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title d-flex align-items-center">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    <span id="documentModalTitle">Business Document</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" id="documentPreview">
                <!-- Document will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Close
                </button>
                <a href="#" id="downloadDocument" class="btn btn-primary" download>
                    <i class="bi bi-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Search functionality for pending owners
    $('#searchPending').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#pendingOwnersContainer .col').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Search functionality for approved owners
    $('#searchApproved').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#approvedOwnersContainer tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Document preview modal
    $('.view-document').on('click', function() {
        const documentPath = $(this).data('document');
        const documentName = documentPath.split('/').pop();
        const extension = documentName.split('.').pop().toLowerCase();
        
        $('#downloadDocument').attr('href', documentPath);
        $('#downloadDocument').attr('download', documentName);
        $('#documentModalTitle').text(documentName);
        
        let previewContent = '';
        if (extension === 'pdf') {
            previewContent = `
                <div class="ratio ratio-16x9">
                    <embed src="${documentPath}" type="application/pdf" width="100%" height="100%">
                </div>
            `;
        } else if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) {
            previewContent = `
                <div class="text-center p-4">
                    <img src="${documentPath}" class="img-fluid rounded shadow" alt="Business document" style="max-height: 70vh;">
                </div>
            `;
        } else {
            previewContent = `
                <div class="text-center p-5">
                    <i class="bi bi-file-earmark-text fs-1 text-muted mb-3"></i>
                    <h5>Document Preview Not Available</h5>
                    <p class="text-muted">Please download the document to view it.</p>
                </div>
            `;
        }
        
        $('#documentPreview').html(previewContent);
        $('#documentModal').modal('show');
    });
    
    // Confirm before rejecting
    $('form[action="reject"]').on('submit', function(e) {
        if (!confirm('Are you sure you want to reject this business application? This action cannot be undone.')) {
            e.preventDefault();
        }
    });
    
    // Animation for empty states
    $('.empty-state').hover(
        function() {
            $(this).addClass('animate__pulse');
        },
        function() {
            $(this).removeClass('animate__pulse');
        }
    );
});
</script>
</body>
</html>