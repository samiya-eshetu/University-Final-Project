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
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .service-card {
      transition: transform 0.2s;
    }
    .service-card:hover {
      transform: translateY(-5px);
    }
    .status-badge {
      font-size: 0.85rem;
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
          <li><a href="admin dashboard.php" class="nav-link active"><i class="bi bi-grid me-2"></i> Dashboard</a></li>
          <li><a href="business_management.php" class="nav-link text-dark"><i class="bi bi-people me-2"></i> Business Management</a></li>
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
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold">New Affiliates</h4>
        <div class="input-group" style="width: 300px;">
          <input type="text" id="searchInput" class="form-control" placeholder="Search...">
          <button class="btn btn-outline-secondary" type="button">
            <i class="bi bi-search"></i>
          </button>
        </div>
      </div>
      
      <div class="table-responsive">
        <table class="table table-bordered table-hover">
          <thead class="table-light">
            <tr>
              <th>Name</th>
              <th>Location</th>
              <th>Price</th>
              <th>Description</th>
              <th>Type</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="newAffiliates">
           
          </tbody>
        </table>
      </div>
<h4 class="fw-bold mt-5">Approved Affiliates</h4>
<ul class="nav nav-tabs mb-3" id="affiliateTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="hotels-tab" data-bs-toggle="tab" data-bs-target="#hotels" type="button" role="tab">Hotels</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="rides-tab" data-bs-toggle="tab" data-bs-target="#rides" type="button" role="tab">Rides</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="tours-tab" data-bs-toggle="tab" data-bs-target="#tours" type="button" role="tab">Tours</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="packages-tab" data-bs-toggle="tab" data-bs-target="#packages" type="button" role="tab">Packages</button>
  </li>
</ul>

<div class="tab-content mt-3">
  <div class="tab-pane fade show active" id="all" role="tabpanel">
    <div id="affiliateListAll" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
  </div>
  <div class="tab-pane fade" id="hotels" role="tabpanel">
    <div id="affiliateListHotels" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
  </div>
  <div class="tab-pane fade" id="rides" role="tabpanel">
    <div id="affiliateListRides" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
  </div>
  <div class="tab-pane fade" id="tours" role="tabpanel">
    <div id="affiliateListTours" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
  </div>
  <div class="tab-pane fade" id="packages" role="tabpanel">
    <div id="affiliateListPackages" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
  </div>
</div>

  <!-- Service Details Modal -->
  <div class="modal fade" id="serviceDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Service Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="serviceDetailsContent">
          <!-- Content will be loaded here -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->


  <script>
// Entire script with toggle fix

$(document).ready(function () {
    loadAffiliates();

    $('#searchInput').on('keyup', function () {
        const searchText = $(this).val().toLowerCase();
        $('#affiliateList .col').each(function () {
            const cardText = $(this).text().toLowerCase();
            $(this).toggle(cardText.includes(searchText));
        });
    });
});

function showLoading(show) {
    $('#loadingOverlay').toggle(show);
}

function loadAffiliates() {
    showLoading(true);

    $.ajax({
        url: 'handle_affiliates.php',
        type: 'POST',
        data: { action: 'get_affiliates' },
        dataType: 'json',
        success: function (response) {
            if (response.success) {
                renderAffiliates(response);
            } else {
                showError(response.error || 'Failed to load data');
            }
        },
        error: function (xhr, status, error) {
            showError('Network error: ' + error);
            console.error("AJAX Error:", xhr.responseText);
        },
        complete: function () {
            showLoading(false);
        }
    });
}

function renderAffiliates(data) {
    $('#newAffiliates').empty();
    $('#affiliateListAll').empty();
    $('#affiliateListHotels').empty();
    $('#affiliateListRides').empty();
    $('#affiliateListTours').empty();

   const getTypeConfig = (type) => ({
    hotel: { 
        label: 'Hotel', 
        badgeClass: 'bg-primary', 
        icon: 'bi-building', 
        container: '#affiliateListHotels' 
    },
    ride: { 
        label: 'Ride', 
        badgeClass: 'bg-warning', 
        icon: 'bi-car-front', 
        container: '#affiliateListRides',
        getLocation: (service) => service.location || 'Pickup location not specified'
    },
    tour: { 
        label: 'Tour', 
        badgeClass: 'bg-success', 
        icon: 'bi-signpost', 
        container: '#affiliateListTours' 
    },
    package: {  // Add this configuration
        label: 'Package', 
        badgeClass: 'bg-info', 
        icon: 'bi-box-seam', 
        container: '#affiliateListPackages'
    }
}[type] || { 
    label: 'Service', 
    badgeClass: 'bg-secondary', 
    icon: 'bi-question-circle', 
    container: '#affiliateListAll',
    getLocation: (service) => service.location || 'Location not specified'
});
    // Pending Affiliates
    if (data.newAffiliates?.length > 0) {
        data.newAffiliates.forEach(service => {
            const typeConfig = getTypeConfig(service.type);
            const locationText = typeConfig.getLocation ? typeConfig.getLocation(service) : service.location;
            
            $('#newAffiliates').append(`
                <tr data-id="${service.id}" data-type="${service.type}">
                    <td>${service.name || '—'}</td>
                    <td>${locationText || '—'}</td>
                    <td>${service.price ? '$' + service.price : 'N/A'}</td>
                    <td title="${service.description || ''}">
                        ${(service.description || '').substring(0, 50)}${service.description?.length > 50 ? '...' : ''}
                    </td>
                    <td><span class="badge ${typeConfig.badgeClass}">${typeConfig.label}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary review-btn" title="Review"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-success accept-btn" title="Approve"><i class="bi bi-check-lg"></i></button>
                            <button class="btn btn-danger reject-btn" title="Reject"><i class="bi bi-x-lg"></i></button>
                        </div>
                    </td>
                </tr>
            `);
        });
    }

    // Active Affiliates
    if (data.activeAffiliates?.length > 0) {
        data.activeAffiliates.forEach(service => {
            const typeConfig = getTypeConfig(service.type);
            const isDisabled = service.status === 'Disabled'; // Note: Now checking for 'Disabled'
            const locationText = typeConfig.getLocation ? typeConfig.getLocation(service) : service.location;
            
            const cardHtml = `
    <div class="col" data-id="${service.id}" data-type="${service.type}">
        <div class="card h-100 service-card ${isDisabled ? 'bg-light' : ''}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0">
                        <i class="bi ${typeConfig.icon} me-1"></i>
                        ${service.name || 'Unnamed Service'}
                    </h5>
                    <span class="badge ${isDisabled ? 'bg-secondary' : 'bg-success'} status-badge">
                        ${service.status || 'Unknown'}
                    </span>
                </div>
                <div class="card-text text-muted mb-3">
                    <p class="mb-1"><i class="bi bi-geo-alt"></i> ${locationText || 'No location specified'}</p>
                    ${service.price ? `<p class="mb-1"><i class="bi "></i> $${service.price}</p>` : ''}
                    <p class="mb-1"><i class="bi bi-tag"></i> ${typeConfig.label}</p>
                    <!-- Add hidden description element -->
                    <p class="description-content d-none">${service.description || 'No description available'}</p>
                </div>
                <div class="d-flex justify-content-between">
                    <button class="btn btn-sm toggle-btn ${isDisabled ? 'btn-success' : 'btn-warning'}">
                        <i class="bi ${isDisabled ? 'bi-power' : 'bi-slash-circle'}"></i>
                        ${isDisabled ? 'Enable' : 'Disable'}
                    </button>
                    <button class="btn btn-sm btn-outline-primary view-btn">
                        <i class="bi bi-eye"></i> View
                    </button>
                </div>
            </div>
        </div>
    </div>
`;
            
            // Add to specific type container
            $(typeConfig.container).append(cardHtml);
            // Also add to "All" container
            $('#affiliateListAll').append(cardHtml);
        });
    }
}

function showError(message) {
    const alertId = 'error-alert-' + Date.now();
    $('body').append(`
        <div id="${alertId}" class="position-fixed top-0 start-50 translate-middle-x mt-3 alert alert-danger alert-dismissible fade show"
             style="z-index: 9999; max-width: 80%;">
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            <strong>Error:</strong> ${message}
        </div>
    `);
    setTimeout(() => $(`#${alertId}`).alert('close'), 5000);
}

function handleAffiliateAction(action, id, type, triggerElement = null) {
    const actionLabels = {
        accept: 'approve',
        reject: 'reject',
        toggle: 'toggle status'
    };
    const typeLabels = {
        hotel: 'Hotel',
        ride: 'Ride',
        tour: 'Tour'
    };

    const typeLabel = typeLabels[type] || 'Service';
    const actionLabel = actionLabels[action] || action;

    if (confirm(`Are you sure you want to ${actionLabel} this ${typeLabel}?`)) {
        showLoading(true);
        $.ajax({
            url: 'handle_affiliates.php',
            type: 'POST',
            data: { action, id, type },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    if (action === 'toggle' && triggerElement) {
                        const card = triggerElement.closest('.col');
                        const badge = card.find('.status-badge');
                        const button = triggerElement;

                       const wasDisabled = badge.text().trim() === 'Enabled';
                        const newStatus = wasDisabled ? 'Disabled' : 'Enabled';


                        badge.text(newStatus);
                        badge.removeClass('bg-success bg-secondary').addClass(newStatus === 'Enabled' ? 'bg-success' : 'bg-secondary');

                        const cardBody = card.find('.service-card');
                        cardBody.toggleClass('bg-light', newStatus === 'Disabled');

                        button.removeClass('btn-warning btn-success');
                        button.addClass(newStatus === 'Disabled' ? 'btn-success' : 'btn-warning');
                        button.html(`
                            <i class="bi ${newStatus === 'Disabled' ? 'bi-power' : 'bi-slash-circle'}"></i>
                            ${newStatus === 'Disabled' ? 'Enable' : 'Disable'}
                        `);
                    } else {
                        loadAffiliates();
                    }
                } else {
                    showError(response.error || `Failed to ${actionLabel} ${typeLabel}`);
                }
            },
            error: function (xhr, status, error) {
                showError('Network error: ' + error);
            },
            complete: function () {
                showLoading(false);
            }
        });
    }
}

// Event handlers
$(document).on('click', '.accept-btn', function () {
    const row = $(this).closest('tr');
    handleAffiliateAction('accept', row.data('id'), row.data('type'));
});

$(document).on('click', '.reject-btn', function () {
    const row = $(this).closest('tr');
    handleAffiliateAction('reject', row.data('id'), row.data('type'));
});

$(document).on('click', '.toggle-btn', function () {
    const card = $(this).closest('.col');
    handleAffiliateAction('toggle', card.data('id'), card.data('type'), $(this));
});

$(document).on('click', '.view-btn, .review-btn', function() {
    const parent = $(this).closest('[data-id]');
    const isCardView = parent.hasClass('col'); // Check if card view (approved) or table row (pending)
    
    let service = {
        id: parent.data('id'),
        type: parent.data('type'),
        name: isCardView ? 
            parent.find('.card-title').text().trim() : 
            parent.find('td:first').text().trim(),
        location: isCardView ? 
            parent.find('.card-text [class*="bi-geo-alt"]').parent().text().trim() : 
            parent.find('td:nth-child(2)').text().trim(),
        price: isCardView ? 
            parent.find('.card-text [class*="bi-currency-dollar"]').parent().text().trim() : 
            parent.find('td:nth-child(3)').text().trim(),
        description: isCardView ?
            parent.find('.description-content').text().trim() : 
            (parent.find('td:nth-child(4)').attr('title') || 
             parent.find('td:nth-child(4)').text().trim() || 'No description available'),
        status: isCardView ? parent.find('.status-badge').text().trim() : 'Pending'
    };

    // Get additional details via AJAX
    showLoading(true);
    $.ajax({
        url: 'handle_affiliates.php',
        type: 'POST',
        data: { 
            action: 'get_service_details',
            id: service.id,
            type: service.type
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                showServiceDetailsModal(service, response.data);
            } else {
                showError(response.error || 'Failed to load service details');
                showServiceDetailsModal(service, null);
            }
        },
        error: function(xhr, status, error) {
            showError('Network error: ' + error);
            showServiceDetailsModal(service, null);
        },
        complete: function() {
            showLoading(false);
        }
    });
});

function showServiceDetailsModal(service, details) {
    let detailsHtml = `
        <div class="mb-3">
            <h5>${service.name}</h5>
            <p class="text-muted"><i class="bi bi-geo-alt"></i> ${service.location || 'Location not specified'}</p>
        </div>
        <div class="row">
            <div class="col-md-6">
                ${service.price && service.price !== 'N/A' ? `
                <div class="mb-3">
                    <strong><i class="bi bi-currency-dollar"></i> Price:</strong> ${service.price.includes('$') ? service.price : '$'+service.price}
                </div>` : ''}
                <div class="mb-3">
                    <strong><i class="bi bi-tag"></i> Type:</strong>
                    <span class="badge ${getTypeBadgeClass(service.type)}">${getTypeLabel(service.type)}</span>
                </div>
                <div class="mb-3">
                    <strong><i class="bi bi-info-circle"></i> Status:</strong>
                    <span class="badge ${service.status === 'Enabled' ? 'bg-success' : service.status === 'Disabled' ? 'bg-secondary' : 'bg-warning'}">
                        ${service.status}
                    </span>
                </div>
            </div>
            <div class="col-md-6">
                ${details && details.owner ? `
                <div class="mb-3">
                    <strong><i class="bi bi-person"></i> Owner Info:</strong>
                    <p class="mb-1"><strong>Name:</strong> ${details.owner.fullName || 'N/A'}</p>
                    <p class="mb-1"><strong>Email:</strong> ${details.owner.email || 'N/A'}</p>
                    <p class="mb-1"><strong>Business:</strong> ${details.owner.businessName || 'N/A'}</p>
                    <p class="mb-1"><strong>Phone:</strong> ${details.owner.phoneNumber || 'N/A'}</p>
                    <p class="mb-1"><strong>Location:</strong> ${details.owner.location || 'N/A'}</p>
                    <p class="mb-1"><strong>Applied:</strong> ${details.owner.date_applied || 'N/A'}</p>
                </div>
                ` : ''}
            </div>
        </div>
        <div class="mb-3">
            <strong><i class="bi bi-card-text"></i> Description:</strong>
            <p class="mt-2">${service.description}</p>
        </div>
    `;

    // Add additional details if available
    if (details) {
        switch(service.type) {
            case 'hotel':
                detailsHtml += `
                    <div class="mb-3">
                        <strong><i class="bi bi-building"></i> Hotel Details:</strong>
                        <p class="mb-1">Availability: ${details.availability || 'N/A'}</p>
                        <p class="mb-1">Rating: ${details.averageRating || 'Not rated yet'}</p>
                    </div>
                `;
                break;
            case 'ride':
                detailsHtml += `
                    <div class="mb-3">
                        <strong><i class="bi bi-car-front"></i> Ride Details:</strong>
                        <p class="mb-1">Contact: ${details.contact_info || 'N/A'}</p>
                        <p class="mb-1">Rating: ${details.rating || 'Not rated yet'}</p>
                    </div>
                `;
                break;
            case 'tour':
                detailsHtml += `
                    <div class="mb-3">
                        <strong><i class="bi bi-signpost"></i> Tour Details:</strong>
                        <p class="mb-1">Duration: ${details.duration || 'N/A'}</p>
                        <p class="mb-1">Rating: ${details.averageRating || 'Not rated yet'}</p>
                    </div>
                `;
                break;
            case 'package':
                detailsHtml += `
                    <div class="mb-3">
                        <strong><i class="bi bi-box-seam"></i> Package Details:</strong>
                        <p class="mb-1">Duration: ${details.duration || 'N/A'}</p>
                        <p class="mb-1">Includes: 
                            ${details.rideID ? 'Ride, ' : ''}
                            ${details.hotelID ? 'Hotel, ' : ''}
                            ${details.tourID ? 'Tour' : ''}
                        </p>
                    </div>
                `;
                break;
        }
    }

    $('#serviceDetailsContent').html(detailsHtml);
    $('#serviceDetailsModal').modal('show');
}


// Helper functions
function getTypeLabel(type) {
    return {
        hotel: 'Hotel',
        ride: 'Ride',
        tour: 'Tour',
        package: 'Package'
    }[type] || 'Service';
}

function getTypeBadgeClass(type) {
    return {
        hotel: 'bg-primary',
        ride: 'bg-warning',
        tour: 'bg-success',
        package: 'bg-info'
    }[type] || 'bg-secondary';
}
  </script>
</body>
</html>