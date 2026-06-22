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
  <title>Booking Cancellation Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <style>
    .booking-card {
      transition: transform 0.2s;
    }
    .booking-card:hover {
      transform: translateY(-5px);
    }
    .status-badge {
      font-size: 0.85rem;
    }
    .cancellation-reason {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 200px;
    }
    .user-info {
      min-width: 200px;
    }
    /* Rejection Modal Styles */
    #rejectionModal .modal-content {
      border: none;
      border-radius: 0.5rem;
    }
    #rejectionModal .modal-header {
      border-bottom: 1px solid #dee2e6;
    }
    #rejectionReason {
      min-height: 120px;
      resize: vertical;
    }
    .service-badge {
      font-size: 0.9rem;
    }
    .detail-card {
      margin-bottom: 1rem;
    }
    .detail-card .card-header {
      font-weight: 600;
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
          <li><a href="web content.php" class="nav-link text-dark"><i class="bi bi-pencil-square me-2"></i> Website Content</a></li>
          <li><a href="edit request.php" class="nav-link text-dark"><i class="bi bi-pencil me-2"></i> Edit Requests</a></li> 
          <li><a href="package managment.php" class="nav-link text-dark"><i class="bi bi-box2-heart-fill me-2"></i> Package Management</a></li>
          <li><a href="BookingCancellation.php" class="nav-link active"><i class="bi bi-trash me-2"></i> Booking Cancellations</a></li>
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
        <h4 class="fw-bold">Cancellation Requests</h4>
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
              <th>Booking ID</th>
              <th>User</th>
              <th>Contact</th>
              <th>Service Type</th>
              <th>Service Name</th>
              <th>Request Date</th>
              <th>Reason</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="cancellationRequests">
            <!-- Data will be loaded here -->
          </tbody>
        </table>
      </div>

      <h4 class="fw-bold mt-5">Processed Requests</h4>
      <ul class="nav nav-tabs mb-3" id="processedTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">Approved</button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="rejected-tab" data-bs-toggle="tab" data-bs-target="#rejected" type="button" role="tab">Rejected</button>
        </li>
      </ul>

      <div class="tab-content mt-3">
        <div class="tab-pane fade show active" id="all" role="tabpanel">
          <div id="processedRequestsAll" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
        </div>
        <div class="tab-pane fade" id="approved" role="tabpanel">
          <div id="processedRequestsApproved" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
        </div>
        <div class="tab-pane fade" id="rejected" role="tabpanel">
          <div id="processedRequestsRejected" class="row row-cols-1 row-cols-md-3 g-4 mb-4"></div>
        </div>
      </div>

      <!-- Request Details Modal -->
      <div class="modal fade" id="requestDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Cancellation Request Details</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="requestDetailsContent">
              <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Rejection Reason Modal -->
      <div class="modal fade" id="rejectionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Provide Rejection Reason</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectionForm">
              <div class="modal-body">
                <input type="hidden" id="rejectBookingId">
                <input type="hidden" id="rejectUserId">
                <div class="mb-3">
                  <label for="rejectionReason" class="form-label">Reason for rejection:</label>
                  <textarea class="form-control" id="rejectionReason" rows="4" required></textarea>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-danger">Confirm Rejection</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <script>
      $(document).ready(function() {
          loadCancellationRequests();

          $('#searchInput').on('keyup', function() {
              const searchText = $(this).val().toLowerCase();
              $('.booking-row').each(function() {
                  const rowText = $(this).text().toLowerCase();
                  $(this).toggle(rowText.includes(searchText));
              });
          });
      });

      function loadCancellationRequests() {
          $.ajax({
              url: 'handle_cancellations.php',
              type: 'POST',
              data: { action: 'get_requests' },
              dataType: 'json',
              success: function(response) {
                  if (response && response.success) {
                      renderRequests(response);
                  } else {
                      const errorMsg = response?.error || 'Invalid response from server';
                      showError(errorMsg);
                      console.error('Server response:', response);
                  }
              },
              error: function(xhr, status, error) {
                  let errorMsg = 'Network error: ' + error;
                  if (xhr.responseText && xhr.responseText.startsWith('<')) {
                      const tempDiv = document.createElement('div');
                      tempDiv.innerHTML = xhr.responseText;
                      const textContent = tempDiv.textContent || tempDiv.innerText || '';
                      errorMsg += '\nServer said: ' + textContent.substring(0, 100) + (textContent.length > 100 ? '...' : '');
                  }
                  showError(errorMsg);
                  console.error('AJAX Error:', xhr.responseText);
              }
          });
      }

      function renderRequests(data) {
          $('#cancellationRequests').empty();
          $('#processedRequestsAll').empty();
          $('#processedRequestsApproved').empty();
          $('#processedRequestsRejected').empty();

          // Pending Requests
          if (data.pendingRequests?.length > 0) {
              data.pendingRequests.forEach(request => {
                  $('#cancellationRequests').append(`
                      <tr class="booking-row" data-id="${request.bookingID}" data-user="${request.user_id}">
                          <td>${request.bookingID || '—'}</td>
                          <td class="user-info">
                              <div class="d-flex flex-column">
                                  <strong>${request.fullName || 'User #'+request.user_id || '—'}</strong>
                                  <small class="text-muted">${request.email || ''}</small>
                              </div>
                          </td>
                          <td>${request.phoneNumber || '—'}</td>
                          <td>
                              <span class="badge ${getServiceTypeBadge(request.serviceType)} service-badge">
                                  ${request.serviceType || '—'}
                              </span>
                          </td>
                          <td>${request.serviceName || '—'}</td>
                          <td>${request.timestamp || '—'}</td>
                          <td class="cancellation-reason" title="${request.reason || 'No reason provided'}">
                              ${request.reason || '—'}
                          </td>
                          <td><span class="badge bg-warning">Pending</span></td>
                          <td>
                              <div class="btn-group btn-group-sm">
                                  <button class="btn btn-primary view-btn" title="View Details"><i class="bi bi-eye"></i></button>
                                  <button class="btn btn-success approve-btn" title="Approve"><i class="bi bi-check-lg"></i></button>
                                  <button class="btn btn-danger reject-btn" title="Reject"><i class="bi bi-x-lg"></i></button>
                              </div>
                          </td>
                      </tr>
                  `);
              });
          }

          // Processed Requests
          if (data.processedRequests?.length > 0) {
              data.processedRequests.forEach(request => {
                  const isApproved = request.status === 'Approved';
                  const cardHtml = `
    <div class="col" data-id="${request.bookingID}" data-user="${request.user_id}">
        <div class="card h-100 booking-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-x me-1"></i>
                        Booking #${request.bookingID}
                    </h5>
                    <span class="badge ${isApproved ? 'bg-success' : 'bg-danger'} status-badge">
                        ${request.status}
                    </span>
                </div>
                <div class="card-text mb-3">
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-person me-2"></i>
                        <div>
                            <strong>${request.fullName || 'User #'+request.user_id}</strong>
                            <div class="text-muted small">${request.email || ''}</div>
                        </div>
                    </div>
                    <div class="mb-2"><i class="bi bi-telephone me-2"></i> ${request.phoneNumber || '—'}</div>
                    <div class="mb-2">
                        <i class="bi bi-tag me-2"></i> 
                        <span class="badge ${getServiceTypeBadge(request.serviceType)} service-badge">
                            ${request.serviceType}
                        </span>
                        ${request.serviceName ? ' - ' + request.serviceName : ''}
                    </div>
                    <div class="mb-2"><i class="bi bi-calendar me-2"></i> ${request.timestamp}</div>
                    <div class="alert alert-info p-2 mb-2">
                        <strong>User's Reason:</strong> ${request.reason || 'No reason provided'}
                    </div>
                    ${request.admin_rejection_reason ? 
                        `<div class="alert alert-danger p-2 mb-2">
                            <strong>Rejection Reason:</strong> ${request.admin_rejection_reason}
                        </div>` : ''}
                </div>
                <button class="btn btn-sm btn-outline-primary view-btn w-100">
                    <i class="bi bi-eye"></i> View Details
                </button>
            </div>
        </div>
    </div>
`;
                  
                  $('#processedRequestsAll').append(cardHtml);
                  if (isApproved) {
                      $('#processedRequestsApproved').append(cardHtml);
                  } else {
                      $('#processedRequestsRejected').append(cardHtml);
                  }
              });
          }
      }

      function handleRequestAction(action, bookingID, userID) {
          const actionText = action === 'approve' ? 'approve' : 'reject';
          
          if (confirm(`Are you sure you want to ${actionText} this cancellation request?`)) {
              $.ajax({
                  url: 'handle_cancellations.php',
                  type: 'POST',
                  data: { 
                      action: 'process_request',
                      bookingID: bookingID,
                      userID: userID,
                      decision: action
                  },
                  dataType: 'json',
                  success: function(response) {
                      if (response.success) {
                          loadCancellationRequests();
                      } else {
                          showError(response.error || `Failed to ${actionText} request`);
                      }
                  },
                  error: function(xhr, status, error) {
                      showError('Network error: ' + error);
                  }
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

      function getServiceTypeBadge(type) {
          return {
              hotel: 'bg-primary',
              ride: 'bg-warning',
              tour: 'bg-success'
          }[type] || 'bg-secondary';
      }

      // Event handlers
      $(document).on('click', '.approve-btn', function() {
          const row = $(this).closest('tr');
          handleRequestAction('approve', row.data('id'), row.data('user'));
      });

      $(document).on('click', '.reject-btn', function() {
          const row = $(this).closest('tr');
          $('#rejectBookingId').val(row.data('id'));
          $('#rejectUserId').val(row.data('user'));
          $('#rejectionReason').val('');
          $('#rejectionModal').modal('show');
      });

      $('#rejectionForm').on('submit', function(e) {
          e.preventDefault();
          
          const bookingID = $('#rejectBookingId').val();
          const userID = $('#rejectUserId').val();
          const reason = $('#rejectionReason').val();
          
          if (!reason.trim()) {
              alert('Please provide a rejection reason');
              return;
          }
          
          $('#rejectionModal').modal('hide');
          
          $.ajax({
              url: 'handle_cancellations.php',
              type: 'POST',
              data: { 
                  action: 'process_request',
                  bookingID: bookingID,
                  userID: userID,
                  decision: 'reject',
                  rejection_reason: reason,
                  admin_rejection_reason: reason
              },
              dataType: 'json',
              success: function(response) {
                  if (response.success) {
                      loadCancellationRequests();
                  } else {
                      showError(response.error || 'Failed to reject request');
                  }
              },
              error: function(xhr, status, error) {
                  showError('Network error: ' + error);
              }
          });
      });

      $(document).on('click', '.view-btn', function() {
          const parent = $(this).closest('[data-id]');
          const isPending = parent.hasClass('booking-row');
          
          let request = {
              bookingID: parent.data('id'),
              userID: parent.data('user'),
              fullName: isPending ? 
                  parent.find('.user-info strong').text() : 
                  parent.find('.card-text strong').text(),
              phoneNumber: isPending ? 
                  parent.find('td:nth-child(3)').text().trim() : 
                  parent.find('.card-text [class*="bi-telephone"]').parent().text().replace('—', '').trim(),
              email: isPending ? 
                  parent.find('.user-info small').text().trim() : 
                  parent.find('.card-text .small').text().trim(),
              serviceType: isPending ? 
                  parent.find('td:nth-child(4)').text().trim() : 
                  parent.find('.card-text [class*="bi-tag"]').parent().text().trim(),
              serviceName: isPending ? 
                  parent.find('td:nth-child(5)').text().trim() : 
                  parent.find('.card-text [class*="bi-tag"]').parent().text().split('-')[1]?.trim() || '',
              timestamp: isPending ? 
                  parent.find('td:nth-child(6)').text().trim() : 
                  parent.find('.card-text [class*="bi-calendar"]').parent().text().trim(),
              status: isPending ? 
                  'Pending' : 
                  parent.find('.status-badge').text().trim(),
              reason: isPending ? 
    parent.find('.cancellation-reason').attr('title') : 
    parent.find('.alert-info').text().replace("User's Reason:", '').trim(),
rejectionReason: !isPending ? 
    (parent.find('.alert-danger').length ? parent.find('.alert-danger').text().replace('Rejection Reason:', '').trim() : null) : 
    null
          };

          $('#requestDetailsContent').html(`
              <div class="mb-4">
                  <h4>Cancellation Request Details</h4>
                  <p class="text-muted">Booking #${request.bookingID}</p>
              </div>
              
              <div class="row mb-4">
                  <div class="col-md-6">
                      <div class="card h-100 detail-card">
                          <div class="card-header bg-light">
                              <h5 class="mb-0">User Information</h5>
                          </div>
                          <div class="card-body">
                              <div class="mb-3">
                                  <strong><i class="bi bi-person"></i> Full Name:</strong>
                                  <p class="mt-1">${request.fullName}</p>
                              </div>
                              <div class="mb-3">
                                  <strong><i class="bi bi-envelope"></i> Email:</strong>
                                  <p class="mt-1">${request.email || 'Not available'}</p>
                              </div>
                              <div class="mb-3">
                                  <strong><i class="bi bi-telephone"></i> Phone:</strong>
                                  <p class="mt-1">${request.phoneNumber || 'Not available'}</p>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="col-md-6">
                      <div class="card h-100 detail-card">
                          <div class="card-header bg-light">
                              <h5 class="mb-0">Booking Information</h5>
                          </div>
                          <div class="card-body">
                              <div class="mb-3">
                                  <strong><i class="bi bi-tag"></i> Service Type:</strong>
                                  <p class="mt-1">
                                      <span class="badge ${getServiceTypeBadge(request.serviceType.toLowerCase())} service-badge">
                                          ${request.serviceType}
                                      </span>
                                      ${request.serviceName ? ' - ' + request.serviceName : ''}
                                  </p>
                              </div>
                              <div class="mb-3">
                                  <strong><i class="bi bi-calendar"></i> Request Date:</strong>
                                  <p class="mt-1">${request.timestamp}</p>
                              </div>
                              <div class="mb-3">
                                  <strong><i class="bi bi-info-circle"></i> Status:</strong>
                                  <p class="mt-1">
                                      <span class="badge ${request.status === 'Approved' ? 'bg-success' : request.status === 'Rejected' ? 'bg-danger' : 'bg-warning'}">
                                          ${request.status}
                                      </span>
                                  </p>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              
              <div class="card detail-card">
                  <div class="card-header bg-light">
                      <h5 class="mb-0">Cancellation Details</h5>
                  </div>
                  <div class="card-body">
                      <div class="mb-3">
                          <strong><i class="bi bi-chat-left-text"></i> User's Reason:</strong>
                          <p class="mt-2">${request.reason || 'No reason provided'}</p>
                      </div>
                      ${request.rejectionReason ? `
                      <div class="mb-3">
                          <strong><i class="bi bi-x-circle"></i> Admin Rejection Reason:</strong>
                          <p class="mt-2">${request.rejectionReason}</p>
                      </div>
                      ` : ''}
                  </div>
              </div>
          `);
          $('#requestDetailsModal').modal('show');
      });
      </script>
</body>
</html>