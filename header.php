<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Your Website'; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <style>
    
            /* Custom Styles */
        .navbar {
            transition: all 0.3s ease;
        }

        .logo-hover {
            transition: transform 0.3s ease;
        }

        .logo-hover:hover {
            transform: scale(1.05);
        }

        .nav-link {
            color: #495057;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #0d6efd;
        }

        .nav-link-text {
            position: relative;
            z-index: 1;
        }

        .nav-link-underline {
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: #0d6efd;
            transition: width 0.3s ease;
        }

        .nav-link:hover .nav-link-underline,
        .nav-link.active .nav-link-underline {
            width: 100%;
        }

        .avatar {
            width: 32px;
            height: 32px;
            font-size: 14px;
        }

        .dropdown-menu {
            border-radius: 0.5rem;
            min-width: 220px;
        }

        .dropdown-item {
            border-radius: 0.25rem;
            margin: 0 0.25rem;
        }

        @media (max-width: 991.98px) {
            .navbar-nav {
                padding-top: 1rem;
                padding-bottom: 1rem;
            }
    
            .nav-item {
                margin-bottom: 0.5rem;
            }
        }

        /* Footer styles */
        footer a {
            text-decoration: none;
            transition: color 0.3s ease;
        }

        footer a:hover {
            color: #0d6efd !important;
        }
    
    
    </style>
</head>
<body>
    <!-- Modern Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm py-2">
        <div class="container">
            <!-- Brand Logo with subtle hover effect -->
            <a class="navbar-brand" href="index.php">
                <img src="assets/images/logo.png" alt="Logo" height="35" class="d-inline-block align-top logo-hover">
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navigation Links -->
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto gap-lg-4">
                    <li class="nav-item">
                        <a class="nav-link position-relative px-0 <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <span class="nav-link-text">Home</span>
                            <span class="nav-link-underline"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative px-0 <?php echo basename($_SERVER['PHP_SELF']) == 'hotel booking.php' ? 'active' : ''; ?>" href="hotel booking.php">
                            <span class="nav-link-text">Hotel Booking</span>
                            <span class="nav-link-underline"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative px-0 <?php echo basename($_SERVER['PHP_SELF']) == 'ride booking.php' ? 'active' : ''; ?>" href="ride booking.php">
                            <span class="nav-link-text">Ride Booking</span>
                            <span class="nav-link-underline"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative px-0 <?php echo basename($_SERVER['PHP_SELF']) == 'travel agents.php' ? 'active' : ''; ?>" href="travel agents.php">
                            <span class="nav-link-text">Travel Agents</span>
                            <span class="nav-link-underline"></span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative px-0 <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>" href="events.php">
                            <span class="nav-link-text">Our Packages</span>
                            <span class="nav-link-underline"></span>
                        </a>
                    </li>
                </ul>
                
                <!-- User Actions -->
                <div class="d-flex align-items-center gap-3">
                    <?php if(isset($_SESSION['accountID'])): ?>
                        <!-- Logged In User Section -->
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="avatar avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center">
                                    <?php echo strtoupper(substr(htmlspecialchars($_SESSION['username']), 0, 1)); ?>
                                </div>
                                <span class="ms-2 d-none d-lg-inline text-dark fw-medium"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2" aria-labelledby="userDropdown">
                                <li>
                                    <div class="px-3 py-2">
                                        <div class="fw-semibold"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                                        <div class="small text-muted"><?php echo htmlspecialchars($_SESSION['email']); ?></div>
                                    </div>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item d-flex align-items-center py-2" href="profile_mgmt.php"><i class="bi bi-person me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item d-flex align-items-center py-2" href="history.php"><i class="bi bi-clock-history me-2"></i>History</a></li>
                                <?php if($_SESSION['role'] === 'admin'): ?>
                                    <li><a class="dropdown-item d-flex align-items-center py-2" href="admin%20side/admin%20dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</a></li>
                                <?php elseif(in_array($_SESSION['role'], ['hotel_owner', 'tour_owner', 'ride_owner'])): ?>
                                    <li><a class="dropdown-item d-flex align-items-center py-2" href="bussiness_owner/dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Business Dashboard</a></li>
                                <?php endif; ?>
                                
                                <li><hr class="dropdown-divider my-1"></li>
                                <li><a class="dropdown-item d-flex align-items-center py-2 text-danger" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Guest User Section -->
                        <div class="d-flex gap-2">
                            <a href="user login.php" class="btn btn-outline-primary px-3 rounded-pill fw-medium">Login</a>
                            <a href="register.php" class="btn btn-primary px-3 rounded-pill fw-medium">Register</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="container-fluid px-0">