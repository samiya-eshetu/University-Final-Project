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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --light-bg: #f8f9fa;
        }
        
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
        }
        
        .table {
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        .table th {
            white-space: nowrap;
            font-weight: 600;
            color: #6c757d;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        .badge-status {
            font-size: 0.75rem;
            padding: 0.35em 0.65em;
            font-weight: 500;
        }
        
        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            margin: 0 2px;
        }
        
        .form-label {
            font-weight: 500;
            margin-bottom: 0.3rem;
            font-size: 0.9rem;
        }
        
        .form-label.required:after {
            content: " *";
            color: var(--danger-color);
        }
        
        .img-preview {
            max-width: 100px;
            max-height: 100px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
        
        #loadingSpinner {
            display: none;
        }
        
        .section-title {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            border-bottom: 3px solid var(--primary-color);
            background-color: transparent;
        }
        
        .alert-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar (unchanged) -->
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
              <li><a href="package managment.php" class="nav-link active"><i class="bi bi-box2-heart-fill me-2"></i> Package Management</a></li>
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

        <div class="main-content flex-grow-1">
            <!-- Alert Container -->
            <div class="alert-container" id="alertsContainer"></div>
            
            <h4 class="section-title">Requested Travel Packages</h4>
            
            <div class="card mb-4">
                <div class="card-body p-0">
                    <div id="loadingSpinner" class="text-center my-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading packages...</p>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Package Title</th>
                                    <th>Location</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="packagesTableBody">
                                <!-- Will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <h4 class="section-title">Processed Packages</h4>
            
            <div class="card mb-4">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Package Title</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="processedPackagesTableBody">
                                <!-- Will be populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <h4 class="section-title">Create New Package</h4>
            
            <div class="card">
                <div class="card-body">
                    <form id="packageForm" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label required">Package Title</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Location</label>
                                <input type="text" class="form-control" name="location" required>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label required">Description</label>
                                <textarea class="form-control" rows="3" name="description" required></textarea>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label required">Duration</label>
                                <input type="text" class="form-control" name="duration" placeholder="e.g., 3 days" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Price </label>
                                <input type="number" class="form-control" name="price" min="0" step="0.01" required>
                            </div>
                           <div class="col-md-4">
    <label class="form-label required">Availability Status</label>
    <select class="form-select" name="availability" required>
        <option value="" disabled selected>Select status</option>
        <option value="available">Available</option>
        <option value="unavailable">Unavailable</option>
        
    </select>
</div>
                            
                            <div class="col-md-4">
                                <label class="form-label required">Ride Service</label>
                                <select class="form-select" name="ride_service" required>
                                    <option value="" disabled selected>Select ride service</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Hotel</label>
                                <select class="form-select" name="hotel_name" required>
                                    <option value="" disabled selected>Select hotel</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label required">Travel Agent</label>
                                <select class="form-select" name="travel_agent" required>
                                    <option value="" disabled selected>Select agent</option>
                                </select>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Package Image</label>
                                <input type="file" class="form-control" name="image" accept="image/jpeg, image/png" id="packageImage">
                                <small class="text-muted">Max 2MB (JPEG, PNG only)</small>
                                <div class="invalid-feedback">Please upload a valid image file (JPEG/PNG, max 2MB)</div>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"></span>
                                    Create Package
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalTitle">Package Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="reviewModalBody">
                    <!-- Content loaded dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // JavaScript remains exactly the same as in your original file
    document.addEventListener('DOMContentLoaded', () => {
        loadPackages();
        loadProcessedPackages();
        loadServiceOptions();

        // Form submission with validation
        document.getElementById('packageForm').addEventListener('submit', e => {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const imageInput = document.getElementById('packageImage');
            
            // Validate image if uploaded
            if (imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const validTypes = ['image/jpeg', 'image/png'];
                const maxSize = 2 * 1024 * 1024; // 2MB
                
                if (!validTypes.includes(file.type)) {
                    imageInput.classList.add('is-invalid');
                    showAlert('Please upload a JPEG or PNG image only.', 'danger');
                    return;
                }
                
                if (file.size > maxSize) {
                    imageInput.classList.add('is-invalid');
                    showAlert('Image size must be less than 2MB.', 'danger');
                    return;
                }
                
                imageInput.classList.remove('is-invalid');
            }
            
            const btn = form.querySelector('button');
            const spinner = document.getElementById('submitSpinner');
            spinner.classList.remove('d-none');
            btn.disabled = true;

            fetch('package_handler.php?action=create', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                spinner.classList.add('d-none');
                btn.disabled = false;
                
                if (data.success) {
                    showAlert(data.message, 'success');
                    form.reset();
                    loadPackages();
                } else {
                    showAlert(data.message, 'danger');
                }
            })
            .catch(err => {
                spinner.classList.add('d-none');
                btn.disabled = false;
                showAlert('An error occurred. Please try again.', 'danger');
                console.error('Error:', err);
            });
        });
    });

    function loadProcessedPackages() {
        fetch('package_handler.php?action=fetch&status=processed')
            .then(res => res.json())
            .then(data => {
                const tableBody = document.getElementById('processedPackagesTableBody');
                tableBody.innerHTML = '';
                
                if (!data.length) {
                    tableBody.innerHTML = '<tr><td colspan="3" class="text-center py-4">No processed packages found.</td></tr>';
                    return;
                }

                data.forEach(pkg => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${pkg.title}</td>
                        <td>
                            <span class="badge rounded-pill badge-status 
                                ${pkg.status === 'approved' ? 'bg-success' : 'bg-danger'}">
                                ${pkg.status}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-info btn-sm" onclick="viewPackageDetails(${pkg.packageID})">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            })
            .catch(err => {
                console.error('Error loading processed packages:', err);
            });
    }

    function loadServiceOptions() {
        fetch('fetch_service_options.php')
            .then(res => res.json())
            .then(data => {
                const rideSelect = document.querySelector('select[name="ride_service"]');
                const hotelSelect = document.querySelector('select[name="hotel_name"]');
                const tourSelect = document.querySelector('select[name="travel_agent"]');

                rideSelect.innerHTML = '<option value="" disabled selected>Select ride service</option>' + 
                    data.rides.map(ride => `<option value="${ride.rideID}">${ride.provider_name}</option>`).join('');
                
                hotelSelect.innerHTML = '<option value="" disabled selected>Select hotel</option>' + 
                    data.hotels.map(hotel => `<option value="${hotel.hotelID}">${hotel.name}</option>`).join('');
                
                tourSelect.innerHTML = '<option value="" disabled selected>Select agent</option>' + 
                    data.tours.map(tour => `<option value="${tour.tourID}">${tour.provider_name}</option>`).join('');
            })
            .catch(err => {
                console.error('Failed to load service options:', err);
                showAlert('Failed to load service options. Please refresh the page.', 'danger');
            });
    }

    function loadPackages() {
        const loadingSpinner = document.getElementById('loadingSpinner');
        const tableBody = document.getElementById('packagesTableBody');
        
        loadingSpinner.style.display = 'block';
        tableBody.innerHTML = '';
        
        fetch('package_handler.php?action=fetch&status=pending')
            .then(res => res.json())
            .then(data => {
                loadingSpinner.style.display = 'none';
                
                if (!data.length) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No pending packages found.</td></tr>';
                    return;
                }

                data.forEach(pkg => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${pkg.title}</td>
                        <td>${pkg.location}</td>
                        <td>${pkg.duration}</td>
                        <td>$${parseFloat(pkg.price).toFixed(2)}</td>
                        <td>
                            <span class="badge rounded-pill badge-status bg-warning text-dark">
                                ${pkg.status}
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-success btn-action" onclick="changeStatus(${pkg.packageID}, 'approved')">
                                <i class="bi bi-check-lg"></i> Approve
                            </button>
                            <button class="btn btn-danger btn-action" onclick="changeStatus(${pkg.packageID}, 'rejected')">
                                <i class="bi bi-x-lg"></i> Reject
                            </button>
                            <button class="btn btn-info btn-action" onclick="viewPackageDetails(${pkg.packageID})">
                                <i class="bi bi-eye"></i> View
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(tr);
                });
            })
            .catch(err => {
                loadingSpinner.style.display = 'none';
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Failed to load packages. Please try again.</td></tr>';
                console.error('Error loading packages:', err);
            });
    }

    function changeStatus(id, status) {
        if (!confirm(`Are you sure you want to ${status} this package?`)) return;
        
        const row = document.querySelector(`tr[data-id="${id}"]`) || 
                    document.querySelector(`tr:has(button[onclick*="changeStatus(${id}, "])`);
        
        fetch('package_handler.php?action=update_status', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id, status})
        })
        .then(res => res.json())
        .then(data => {
            showAlert(data.message, data.success ? 'success' : 'danger');
            if (data.success && row) {
                row.remove();
                
                const tableBody = document.getElementById('packagesTableBody');
                if (tableBody.rows.length === 0) {
                    tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No pending packages found.</td></tr>';
                }
            }
        })
        .catch(err => {
            showAlert('Failed to update status. Please try again.', 'danger');
            console.error('Error updating status:', err);
        });
    }

    function viewPackageDetails(packageID) {
        fetch(`package_handler.php?action=details&id=${packageID}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Network response was not ok');
                }
                return res.json();
            })
            .then(data => {
                if (data.success) {
                    const pkg = data.package;
                    const modalBody = document.getElementById('reviewModalBody');
                    
                    modalBody.innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h5>${pkg.title}</h5>
                                <p><strong>Location:</strong> ${pkg.location}</p>
                                <p><strong>Duration:</strong> ${pkg.duration}</p>
                                <p><strong>Price:</strong> $${parseFloat(pkg.price).toFixed(2)}</p>
                                <p><strong>Availability:</strong> ${pkg.availability}</p>
                            </div>
                            <div class="col-md-6">
                                ${pkg.image_path ? `<img src="${pkg.image_path}" class="img-fluid rounded mb-3" alt="Package Image">` : ''}
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6>Description</h6>
                                <p>${pkg.description}</p>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-4">
                                <h6>Services Included</h6>
                                <p><strong>Ride:</strong> ${pkg.rideName || 'N/A'}</p>
                                <p><strong>Hotel:</strong> ${pkg.hotelName || 'N/A'}</p>
                                <p><strong>Tour Agent:</strong> ${pkg.tourName || 'N/A'}</p>
                            </div>
                            <div class="col-md-4">
                                <h6>Status</h6>
                                <span class="badge rounded-pill ${pkg.status === 'approved' ? 'bg-success' : 
                                    pkg.status === 'pending' ? 'bg-warning text-dark' : 'bg-danger'}">
                                    ${pkg.status}
                                </span>
                                <p class="mt-2"><small>Created: ${new Date(pkg.created_at).toLocaleString()}</small></p>
                            </div>
                        </div>
                    `;
                    
                    const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
                    modal.show();
                } else {
                    showAlert(data.message || 'Package details not found', 'danger');
                }
            })
            .catch(err => {
                console.error('Error loading package details:', err);
                showAlert('Failed to load package details. Please try again.', 'danger');
            });
    }

    function showAlert(message, type) {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.role = 'alert';
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        const container = document.getElementById('alertsContainer');
        container.appendChild(alert);
        
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }, 5000);
    }
    </script>
</body>
</html>