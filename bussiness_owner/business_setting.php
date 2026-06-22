<?php
session_start();

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);


// Check authentication and authorization
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php?error=session_expired");
    exit;
}

$accountID = $_SESSION['accountID'];
$success = $error = null;

// Determine owner type with prepared statement
$ownerTables = ['hotel_owners', 'ride_owners', 'tour_owners'];
$ownerData = null;
$ownerType = null;

foreach ($ownerTables as $table) {
    $stmt = $conn->prepare("SELECT * FROM $table WHERE ownerID = ?");
    $stmt->bind_param("i", $accountID);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $ownerData = $result->fetch_assoc();
        $ownerType = $table;
        $stmt->close();
        break;
    }
    $stmt->close();
}

// Check if authorized business owner
if (!$ownerType || !$ownerData) {
    header("Location: ../index.php?error=not_authorized");
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $fullName = filter_input(INPUT_POST, 'full_name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $phone = filter_input(INPUT_POST, 'phone_number', FILTER_SANITIZE_STRING);
    $businessName = filter_input(INPUT_POST, 'business_name', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if (empty($fullName) || empty($email) || empty($phone) || empty($businessName)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid email address.";
    } else {
        // Update profile with prepared statement
        $stmt = $conn->prepare("UPDATE $ownerType SET fullName = ?, email = ?, phoneNumber = ?, businessName = ?, location = ? WHERE ownerID = ?");
        $stmt->bind_param("sssssi", $fullName, $email, $phone, $businessName, $location, $accountID);
        
        if (!$stmt->execute()) {
            error_log("Profile update failed: " . $stmt->error);
            $error = "Failed to update profile. Please try again.";
        }
        $stmt->close();

        // Change password if provided
        if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
            $current = $_POST['current_password'];
            $new = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];
            
            if (empty($current)) {
                $error = "Please enter your current password.";
            } elseif (empty($new) || empty($confirm)) {
                $error = "Please fill in both new password fields.";
            } elseif ($new !== $confirm) {
                $error = "New passwords do not match.";
            } elseif (strlen($new) < 8) {
                $error = "Password must be at least 8 characters long.";
            } else {
                $stmt = $conn->prepare("SELECT password FROM accounts WHERE accountID = ?");
                $stmt->bind_param("i", $accountID);
                $stmt->execute();
                $acc = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                
                if (password_verify($current, $acc['password'])) {
                    $hashed = password_hash($new, PASSWORD_DEFAULT);
                    $conn->query("UPDATE accounts SET password = '$hashed' WHERE accountID = $accountID");
                } else {
                    $error = "Current password is incorrect.";
                }
            }
        }

        // Handle document upload
        if (empty($error)) {
            if (!empty($_FILES['business_document']['name'])) {
                $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
                $maxSize = 5 * 1024 * 1024; // 5MB
                
                if ($_FILES['business_document']['size'] > $maxSize) {
                    $error = "File size exceeds 5MB limit.";
                } elseif (!in_array($_FILES['business_document']['type'], $allowedTypes)) {
                    $error = "Only PDF, JPEG, and PNG files are allowed.";
                } else {
                    $uploadDir = '../public/documents/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    
                    $fileName = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['business_document']['name']));
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['business_document']['tmp_name'], $filePath)) {
                        $stmt = $conn->prepare("UPDATE $ownerType SET business_document = ? WHERE ownerID = ?");
                        $stmt->bind_param("si", $filePath, $accountID);
                        $stmt->execute();
                        $stmt->close();
                    } else {
                        $error = "Failed to upload document.";
                    }
                }
            }
        }

        // Handle account closure request
        if (empty($error) && isset($_POST['close_account'])) {
            $conn->query("UPDATE accounts SET status = 'requested_closure' WHERE accountID = $accountID");
            $success = "Closure request submitted. An admin will review it.";
        }

        if (empty($error)) {
            $success = "Profile updated successfully.";
            // Refresh owner data
            $stmt = $conn->prepare("SELECT * FROM $ownerType WHERE ownerID = ?");
            $stmt->bind_param("i", $accountID);
            $stmt->execute();
            $ownerData = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Business Settings - <?= htmlspecialchars($ownerData['businessName']) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
  <style>
    :root {
      --primary-color: #0d6efd;
      --secondary-color: #6c757d;
      --success-color: #198754;
      --danger-color: #dc3545;
      --sidebar-width: 250px;
    }
    
    .sidebar {
      width: var(--sidebar-width);
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #f8f9fa;
      border-right: 1px solid #dee2e6;
      padding: 1rem;
      overflow-y: auto;
      transition: all 0.3s;
      z-index: 1000;
    }
    
    .content {
      margin-left: calc(var(--sidebar-width) + 10px);
      padding: 2rem;
      transition: all 0.3s;
    }
    
    @media (max-width: 992px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.active {
        transform: translateX(0);
      }
      .content {
        margin-left: 0;
      }
    }
    
    .nav-pills .nav-link {
      color: #333;
      border-radius: 0.375rem;
      margin-bottom: 0.25rem;
      transition: all 0.2s;
    }
    
    .nav-pills .nav-link.active, 
    .nav-pills .nav-link:hover {
      background-color: var(--primary-color);
      color: white;
    }
    
    .card {
      border-radius: 0.5rem;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
      border: none;
    }
    
    .form-control:focus, .form-select:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .btn-primary {
      background-color: var(--primary-color);
      border-color: var(--primary-color);
    }
    
    .section-header {
      position: relative;
      padding-bottom: 0.5rem;
      margin-bottom: 1.5rem;
    }
    
    .section-header:after {
      content: '';
      position: absolute;
      left: 0;
      bottom: 0;
      width: 50px;
      height: 3px;
      background-color: var(--primary-color);
    }
    
    .profile-picture {
      width: 120px;
      height: 120px;
      object-fit: cover;
      border-radius: 50%;
      border: 3px solid white;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .document-preview {
      max-width: 100%;
      max-height: 200px;
      border: 1px solid #dee2e6;
      border-radius: 0.25rem;
    }
    
    .sidebar-toggle {
      display: none;
      position: fixed;
      top: 10px;
      left: 10px;
      z-index: 1050;
    }
    
    @media (max-width: 992px) {
      .sidebar-toggle {
        display: block;
      }
    }
  </style>
</head>
<body>
  <!-- Mobile Sidebar Toggle -->
  <button class="btn btn-primary sidebar-toggle d-lg-none">
    <i class="bi bi-list"></i>
  </button>

  <!-- Sidebar -->
  <div class="sidebar">
    <a href="#" class="d-flex align-items-center mb-3 text-decoration-none">
      <i class="bi bi-speedometer2 me-2 fs-4"></i>
      <span class="fs-5 fw-bold">Dashboard</span>
    </a>
    <hr />
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
          <i class="bi bi-speedometer2 me-2"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a href="manage_listings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_listings.php' ? 'active' : '' ?>">
          <i class="bi bi-card-list me-2"></i> Manage Listings
        </a>
      </li>
      <li class="nav-item">
        <a href="payment_management.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payment_management.php' ? 'active' : '' ?>">
          <i class="bi bi-currency-exchange me-2"></i> Payment
        </a>
      </li>
      <li class="nav-item">
        <a href="booking_dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'booking_dashboard.php' ? 'active' : '' ?>">
          <i class="bi bi-suitcase me-2"></i> Bookings
        </a>
      </li>
      <li class="nav-item">
        <a href="review_and_rating.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'review_and_rating.php' ? 'active' : '' ?>">
          <i class="bi bi-star me-2"></i> Reviews & Ratings
        </a>
      </li>
      <li class="nav-item">
        <a href="business_setting.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'business_setting.php' ? 'active' : '' ?>">
          <i class="bi bi-gear me-2"></i> Profile Settings
        </a>
      </li>
    </ul>

    <div class="mt-auto pt-3 border-top">
      <a href="../index.php" class="btn btn-outline-primary d-flex align-items-center w-100 py-2">
        <i class="bi bi-house-door me-2"></i>
        <span>Main Page</span>
      </a>
    </div>
  </div>

  <!-- Main Content -->
  <div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h4 class="fw-bold text-primary">
        <i class="bi bi-person-gear me-2"></i>Profile Management
      </h4>
      <div class="d-flex align-items-center">
        <span class="badge bg-secondary me-2">
          <?= ucwords(str_replace('_', ' ', str_replace('_owners', '', $ownerType))) ?>
        </span>
        <span class="text-muted"><?= htmlspecialchars($ownerData['businessName']) ?></span>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeIn">
        <i class="bi bi-check-circle-fill me-2"></i> <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php elseif ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <div class="row">
      <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100">
          <div class="card-body text-center">

            
            <h6 class="text-start mb-3">Business Documents</h6>
            <?php if (!empty($ownerData['business_document'])): ?>
              <div class="mb-3">
                <a href="<?= htmlspecialchars($ownerData['business_document']) ?>" target="_blank" class="d-block mb-2">
                  <i class="bi bi-file-earmark-text me-1"></i> View Current Document
                </a>
                <img src="<?= htmlspecialchars($ownerData['business_document']) ?>" class="document-preview img-thumbnail">
              </div>
            <?php else: ?>
              <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-1"></i> No business document uploaded
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      
      <div class="col-lg-8">
        <div class="card shadow-sm">
          <div class="card-body">
            <form action="business_setting.php" method="POST" class="needs-validation" novalidate enctype="multipart/form-data">
              <ul class="nav nav-tabs mb-4" id="settingsTab" role="tablist">
                <li class="nav-item" role="presentation">
                  <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab">Profile</button>
                </li>

              </ul>
              
              <div class="tab-content" id="settingsTabContent">
                <!-- Profile Tab -->
                <div class="tab-pane fade show active" id="profile" role="tabpanel">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-person"></i></span>
                          <input type="text" name="full_name" class="form-control" 
                                 value="<?= htmlspecialchars($ownerData['fullName']) ?>" required>
                        </div>
                        <div class="invalid-feedback">Please provide your full name.</div>
                      </div>
                      
                      <div class="mb-3">
                        <label class="form-label fw-medium">Email Address <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                          <input type="email" name="email" class="form-control" 
                                 value="<?= htmlspecialchars($ownerData['email']) ?>" required>
                        </div>
                        <div class="invalid-feedback">Please provide a valid email address.</div>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label fw-medium">Phone Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                          <input type="tel" name="phone_number" class="form-control" 
                                 value="<?= htmlspecialchars($ownerData['phoneNumber']) ?>" required>
                        </div>
                        <div class="invalid-feedback">Please provide a valid phone number.</div>
                      </div>
                      
                      <div class="mb-3">
                        <label class="form-label fw-medium">Business Name <span class="text-danger">*</span></label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-building"></i></span>
                          <input type="text" name="business_name" class="form-control" 
                                 value="<?= htmlspecialchars($ownerData['businessName']) ?>" required>
                        </div>
                        <div class="invalid-feedback">Please provide your business name.</div>
                      </div>
                    </div>
                    
                    <div class="col-12">
                      <div class="mb-3">
                        <label class="form-label fw-medium">Business Location</label>
                        <div class="input-group">
                          <span class="input-group-text"><i class="bi bi-geo-alt"></i></span>
                          <input type="text" name="location" class="form-control" 
                                 value="<?= htmlspecialchars($ownerData['location']) ?>">
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                

                
                <!-- Documents Tab -->
                <div class="tab-pane fade" id="documents" role="tabpanel">
                  <div class="row g-3">
                    <div class="col-12">
                      <div class="mb-3">
                        <label class="form-label fw-medium">Business Document</label>
                        <input type="file" name="business_document" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted">Upload PDF, JPG, or PNG (max 5MB)</small>
                      </div>
                      
                      <?php if (!empty($ownerData['business_document'])): ?>
                        <div class="alert alert-info">
                          <i class="bi bi-info-circle me-1"></i> Uploading a new document will replace the existing one.
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                </div>
              </div>
              

                
                <div>
                  <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-save me-1"></i> Save Changes
                  </button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Form validation
    (function() {
      'use strict';
      const forms = document.querySelectorAll('.needs-validation');
      
      Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
          }
          
          form.classList.add('was-validated');
        }, false);
      });
    })();
    
    // Sidebar toggle for mobile
    document.querySelector('.sidebar-toggle').addEventListener('click', function() {
      document.querySelector('.sidebar').classList.toggle('active');
    });
    
    // Tab switching
    const triggerTabList = [].slice.call(document.querySelectorAll('#settingsTab button'));
    triggerTabList.forEach(triggerEl => {
      const tabTrigger = new bootstrap.Tab(triggerEl);
      
      triggerEl.addEventListener('click', event => {
        event.preventDefault();
        tabTrigger.show();
      });
    });
    
    // Confirm account closure
    document.getElementById('closeAccount').addEventListener('change', function() {
      if (this.checked) {
        if (!confirm('Are you sure you want to request account closure? This action will need admin approval.')) {
          this.checked = false;
        }
      }
    });
    
    // Preview image before upload
    document.querySelector('input[name="business_document"]')?.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
          // You could show a preview here if you want
          console.log('File selected:', file.name);
        };
        reader.readAsDataURL(file);
      }
    });
  </script>
</body>
</html>