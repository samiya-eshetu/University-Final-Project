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
    <title>Website Content Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .content-card {
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .content-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            font-weight: 600;
        }
        .service-badge {
            font-size: 0.75rem;
            padding: 5px 8px;
        }
        .nav-tabs .nav-link.active {
            font-weight: 600;
            border-bottom: 2px solid #0d6efd;
        }
        .list-group-item {
            transition: background-color 0.2s;
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
          <li><a href="admin_payment_approval.php" class="nav-link text-dark"><i class="bi bi-cash-coin me-2"></i> Payment Approval</a></li>
          <li><a href="web content.php" class="nav-link active"><i class="bi bi-pencil-square me-2"></i> Website Content</a></li>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold">Website Content Management</h4>
            <div class="input-group" style="width: 300px;">
                <input type="text" class="form-control" placeholder="Search content...">
                <button class="btn btn-primary" type="button"><i class="bi bi-search"></i></button>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#promotions">Promotions</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#terms">Terms & Conditions</a>
            </li>
        </ul>

        <div class="tab-content">
            <!-- Promotions Tab -->
            <div class="tab-pane fade show active" id="promotions">
                <!-- Featured Hotels Section -->
                <div class="card content-card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Featured Hotels</span>

                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="mb-3">Available Hotels</h6>
                                <div class="mb-3">
                                    <input type="text" class="form-control mb-2" placeholder="Search hotels..." id="hotelSearch">
                                </div>
                                <ul id="hotelList" class="list-group" style="max-height: 400px; overflow-y: auto;">
                                    <!-- Hotel items will be injected here -->
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-3">Currently Featured</h6>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Name</th>
                                                <th>Location</th>
                                                <th>Price</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="featured-hotels-body">
                                            <!-- Featured hotels will appear here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Exclusive Offers Section -->
                <div class="card content-card">
                    <div class="card-header">Special Offers</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="text" id="exclusiveSearch" class="form-control mb-3" placeholder="Search exclusive offers...">
                        </div>
                        <ul id="exclusiveList" class="list-group" style="max-height: 400px; overflow-y: auto;">
                            <!-- Exclusive offers will appear here -->
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Terms & Conditions Tab -->
            <div class="tab-pane fade" id="terms">
                <div class="card content-card">
                    <div class="card-header">Terms & Conditions</div>
                    <div class="card-body">
                        <form id="termsForm">
                            <div class="mb-3">
                                <textarea class="form-control" id="termsContent" rows="15" style="font-family: monospace;">Welcome to Explore Ethiopia!

By using our platform to book hotels, rides, or travel agent services, you agree to the following terms:

1. General Use: This platform is intended for tourists seeking convenient travel planning in Ethiopia.
2. Bookings: All bookings are subject to confirmation and availability.
3. Payments: Payments must be completed through approved channels.
4. Cancellations: Each service provider may have unique cancellation policies.
5. User Conduct: Users must respect local laws and the dignity of hosts.
6. Liability: We are not liable for issues with third-party providers. 
7. Privacy: User data is handled according to our privacy policy.
8. Changes: Terms may change without prior notice.</textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Save Changes
                                </button>
                            </div>
                        </form>
                        <div id="saveStatus" class="alert alert-success mt-3" style="display: none;">
                            Terms & Conditions saved successfully!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Hotel Modal -->
<div class="modal fade" id="addHotelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="hotelForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Hotel Name</label>
                        <input type="text" class="form-control" id="hotelName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="hotelDescription" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Add Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let allHotels = [];
let exclusiveServices = [];

document.addEventListener('DOMContentLoaded', () => {
    loadAllHotels();
    loadFeaturedHotels();
    loadExclusiveList();

    document.getElementById('hotelSearch').addEventListener('input', filterHotels);
    document.getElementById('exclusiveSearch').addEventListener('input', filterExclusiveList);
});

// --------------------- HOTEL LIST ---------------------
function loadAllHotels() {
    fetch('search_services.php?action=all_hotels')
        .then(r => r.json())
        .then(hotels => {
            allHotels = hotels;
            renderHotelList(hotels);
        });
}

function renderHotelList(hotels) {
    const hotelList = document.getElementById('hotelList');
    hotelList.innerHTML = '';
    hotels.forEach(h => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';
        li.innerHTML = `
            <div>
                <strong>${h.name}</strong> 
                <small class="text-muted d-block">${h.location}</small>
                <span class="badge bg-light text-dark service-badge">$${h.pricePerNight}/night</span>
            </div>
            <div id="hotel-action-${h.hotelID}">
                ${h.featured === 'YES' ? 
                    '<span class="badge bg-success service-badge">Featured</span>' :
                    `<button class="btn btn-sm btn-outline-primary" onclick="addToFeatured(${h.hotelID})">
                        <i class="bi bi-star"></i> Feature
                    </button>`
                }
            </div>
        `;
        hotelList.appendChild(li);
    });
}

function filterHotels() {
    const input = document.getElementById('hotelSearch').value.toLowerCase();
    const filtered = allHotels.filter(h =>
        h.name.toLowerCase().includes(input) || h.location.toLowerCase().includes(input)
    );
    renderHotelList(filtered);
}

// --------------------- FEATURED HOTELS ---------------------
function loadFeaturedHotels() {
    fetch('search_services.php?action=featured')
        .then(r => r.json())
        .then(hotels => {
            const featuredBody = document.getElementById('featured-hotels-body');
            featuredBody.innerHTML = '';
            if (!hotels.length) {
                featuredBody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No featured hotels</td></tr>';
                return;
            }
            hotels.forEach(h => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${h.name}</td>
                    <td>${h.location}</td>
                    <td>$${h.pricePerNight}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-danger" onclick="removeFeatured(${h.hotelID})">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </td>
                `;
                featuredBody.appendChild(tr);
            });
        });
}

// --------------------- EXCLUSIVE OFFERS ---------------------
function loadExclusiveList() {
    fetch('search_services.php?action=get_all_for_exclusive')
        .then(r => r.json())
        .then(services => {
            exclusiveServices = services;
            renderExclusiveList(services);
        });
}

function renderExclusiveList(services) {
    const exclusiveList = document.getElementById('exclusiveList');
    exclusiveList.innerHTML = '';
    services.forEach(service => {
        const li = document.createElement('li');
        li.className = 'list-group-item d-flex justify-content-between align-items-center';

        const typeBadge = service.type === 'hotel' ? 'bg-primary' : 
                          service.type === 'tour' ? 'bg-success' : 'bg-warning';

        li.innerHTML = `
            <div>
                <strong>${service.name}</strong>
                <div class="d-flex gap-2 mt-1">
                    <span class="badge ${typeBadge} service-badge">${service.type}</span>
                    <span class="badge bg-light text-dark service-badge">$${service.price}</span>
                    ${service.exclusive_offer === 'YES' ? 
                        '<span class="badge bg-danger service-badge">Exclusive</span>' : ''}
                </div>
            </div>
            <div id="exclusive-action-${service.type}-${service.id}">
                ${service.exclusive_offer === 'YES' ?
                    `<button class="btn btn-sm btn-outline-danger" onclick="removeExclusive('${service.type}', ${service.id})">
                        <i class="bi bi-x-circle"></i> Remove
                    </button>` :
                    `<button class="btn btn-sm btn-outline-primary" onclick="addExclusive('${service.type}', ${service.id})">
                        <i class="bi bi-plus-circle"></i> Add
                    </button>`
                }
            </div>
        `;
        exclusiveList.appendChild(li);
    });
}

function filterExclusiveList() {
    const input = document.getElementById('exclusiveSearch').value.toLowerCase();
    const filtered = exclusiveServices.filter(s =>
        s.name.toLowerCase().includes(input) || s.type.toLowerCase().includes(input)
    );
    renderExclusiveList(filtered);
}

// --------------------- GLOBAL FUNCTIONS ---------------------
window.addToFeatured = function(hotelID) {
    fetch(`search_services.php?action=add_featured&hotelID=${hotelID}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                loadAllHotels();
                loadFeaturedHotels();
            } else {
                alert('❌ Failed to add hotel to featured list');
            }
        });
};

window.removeFeatured = function(hotelID) {
    if (!confirm('Remove this hotel from featured list?')) return;
    fetch(`search_services.php?action=remove_featured&hotelID=${hotelID}`)
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                loadAllHotels();
                loadFeaturedHotels();
            } else {
                alert('❌ Failed to remove hotel.');
            }
        });
};

window.addExclusive = function(type, id) {
    fetch(`search_services.php?action=add_exclusive&type=${type}&id=${id}`)
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'SUCCESS') loadExclusiveList();
            else alert('❌ Failed to add to exclusive offers');
        });
};


window.removeExclusive = function(type, id) {
    fetch(`search_services.php?action=remove_exclusive&type=${type}&id=${id}`)
        .then(r => r.text())
        .then(res => {
            if (res.trim() === 'SUCCESS') loadExclusiveList();
            else alert('❌ Failed to remove from exclusive offers');
        });
};




</script>

</body>
</html>