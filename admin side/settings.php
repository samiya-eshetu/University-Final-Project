<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
          <li><a href="payment integration mgmt.php" class="nav-link text-dark"><i class="bi bi-currency-exchange me-2"></i> Payment Integration</a></li>
          <li><a href="BookingCancellation.php" class="nav-link text-dark"><i class="bi bi-trash me-2"></i> Booking Cancellations</a></li>
          <li><a href="settings.php" class="nav-link active"><i class="bi bi-gear me-2"></i> Settings</a></li>
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
      <h4 class="fw-bold">Settings</h4>
      <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
        <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#generalSettings" role="tab">General</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#securitySettings" role="tab">Security</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#adminManagement" role="tab">Admin Management</a></li>
        <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#affiliateSettings" role="tab">Affiliate Settings</a></li>
      </ul>

      <div class="tab-content mt-3">
        <!-- General Settings -->
        <div class="tab-pane fade show active" id="generalSettings" role="tabpanel">
          <h4 class="fw-bold mb-4">General Settings</h4>
          <form>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="siteName" class="form-label">Website Name</label>
                <input type="text" class="form-control" id="siteName" value="Explore Ethiopia" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="contactEmail" class="form-label">Contact Email</label>
                <input type="email" class="form-control" id="contactEmail" value="contact@exploreethiopia.com" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="defaultLanguage" class="form-label">Default Language</label>
                <select class="form-select" id="defaultLanguage">
                  <option selected>English</option>
                  <option>Amharic</option>
                  <option>French</option>
                </select>
              </div>
              <div class="col-md-6 mb-3">
                <label for="defaultCurrency" class="form-label">Default Currency</label>
                <select class="form-select" id="defaultCurrency">
                  <option selected>ETB (Ethiopian Birr)</option>
                  <option>USD</option>
                  <option>EUR</option>
                </select>
              </div>
              <div class="col-md-12 mb-3">
                <label for="homepageTitle" class="form-label">Homepage Main Title</label>
                <input type="text" class="form-control" id="homepageTitle" value="Explore Ethiopia" />
              </div>
              <div class="col-md-12 mb-3">
                <label for="homepageSubtitle" class="form-label">Homepage Subtitle</label>
                <input type="text" class="form-control" id="homepageSubtitle" value="Your adventure begins here" />
              </div>
              <div class="col-md-12 mb-4">
                <label class="form-label">Enabled Modules</label>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="enableRides" checked />
                  <label class="form-check-label" for="enableRides">Ride Booking</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="enableHotels" checked />
                  <label class="form-check-label" for="enableHotels">Hotel Listings</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" id="enableAgents" checked />
                  <label class="form-check-label" for="enableAgents">Travel Agent Directory</label>
                </div>
              </div>
            </div>
            <div class="text-end">
              <button type="submit" class="btn btn-success">Save Settings</button>
            </div>
          </form>
        </div>

        <!-- Security Settings -->
        <div class="tab-pane fade" id="securitySettings" role="tabpanel">
          <h4 class="fw-bold mb-4">Security Settings</h4>
          <div class="card mb-4">
            <div class="card-header bg-light fw-semibold">Two-Factor Authentication</div>
            <div class="card-body">
              <p>Enhance your account security by enabling 2FA.</p>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="enable2FA" />
                <label class="form-check-label" for="enable2FA">Enable 2FA via SMS or App</label>
              </div>
            </div>
          </div>
          <div class="card">
            <div class="card-header bg-light fw-semibold">Recent Login Activity</div>
            <div class="card-body table-responsive">
              <table class="table table-bordered">
                <thead class="table-light">
                  <tr>
                    <th>Device</th>
                    <th>Location</th>
                    <th>Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Chrome - Windows</td>
                    <td>Addis Ababa, Ethiopia</td>
                    <td>2025-04-29 10:24 AM</td>
                    <td><span class="badge bg-success">Success</span></td>
                  </tr>
                  <tr>
                    <td>Mobile - Android</td>
                    <td>Mekelle, Ethiopia</td>
                    <td>2025-04-27 7:10 PM</td>
                    <td><span class="badge bg-danger">Failed</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Admin Management -->
        <div class="tab-pane fade" id="adminManagement" role="tabpanel">
          <h4 class="fw-bold mb-4">Admin Profile</h4>
          <form>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="adminName" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="adminName" value="Amen" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="adminEmail" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="adminEmail" value="admin@example.com" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="adminPhone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="adminPhone" value="+251911234567" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="adminRole" class="form-label">Role</label>
                <input type="text" class="form-control" id="adminRole" value="Super Admin" readonly />
              </div>
            </div>
            <h5 class="mt-4">Change Password</h5>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="currentPassword" class="form-label">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="newPassword" class="form-label">New Password</label>
                <input type="password" class="form-control" id="newPassword" />
              </div>
              <div class="col-md-6 mb-3">
                <label for="confirmPassword" class="form-label">Confirm New Password</label>
                <input type="password" class="form-control" id="confirmPassword" />
              </div>
            </div>
            <div class="text-end mt-3">
              <button type="submit" class="btn btn-primary">Update Profile</button>
            </div>
          </form>
        </div>

        <!-- Affiliate Settings -->
        <div class="tab-pane fade" id="affiliateSettings" role="tabpanel">
          <h4 class="fw-bold mb-3">Affiliate Settings</h4>
          <div class="card mb-4">
            <div class="card-header bg-light fw-semibold">Pending Affiliate Requests</div>
            <div class="card-body table-responsive">
              <table class="table table-bordered align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Type</th>
                    <th>Date Applied</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>Demeke</td>
                    <td>jdem101@example.com</td>
                    <td>Driver</td>
                    <td>2023-01-15</td>
                    <td>
                      <button class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text"></i> Review</button>
                      <button class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Approve</button>
                      <button class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Reject</button>
                    </td>
                  </tr>
                  <tr>
                    <td>Selam Hotel</td>
                    <td>selami@example.com</td>
                    <td>Hotel</td>
                    <td>2023-02-20</td>
                    <td>
                      <button class="btn btn-outline-primary btn-sm"><i class="bi bi-file-earmark-text"></i> Review</button>
                      <button class="btn btn-success btn-sm"><i class="bi bi-check-circle"></i> Approve</button>
                      <button class="btn btn-danger btn-sm"><i class="bi bi-x-circle"></i> Reject</button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="card">
            <div class="card-header bg-light fw-semibold">Active Affiliates</div>
            <div class="mb-3">
                <input type="text" id="searchInput" class="form-control mt-2 mb-2" placeholder="Search by Affiliate ...">
            </div>
            <div class="card-body">
              <div class="row g-3">
                <div class="col-md-6 col-lg-4">
                  <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                      <h5 class="card-title fw-bold mb-1">Ararat Hotel</h5>
                      <p class="text-muted mb-2">Hotel Affiliate<br /><small>Joined: Jan 2023</small></p>
                      <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i> Details</button>
                        <button class="btn btn-danger btn-sm"><i class="bi bi-slash-circle"></i> Disable</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6 col-lg-4">
                  <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                      <h5 class="card-title fw-bold mb-1">Telebirr</h5>
                      <p class="text-muted mb-2">Payment Partner<br /><small>Joined: Jan 2023</small></p>
                      <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-eye"></i> Details</button>
                        <button class="btn btn-danger btn-sm"><i class="bi bi-slash-circle"></i> Disable</button>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Add more affiliates as needed -->
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
