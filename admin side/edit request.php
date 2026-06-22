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
    <title>Edit Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        /* Improved Main Content Styling */
        .main-content {
            background-color: #f8f9fa;
            padding: 2rem;
            min-height: 100vh;
        }
        
        .card-container {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            padding: 1.5rem;
        }
        
        .table-responsive {
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f1f5f9;
            border-bottom-width: 1px;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: #64748b;
        }
        
        .table td, .table th {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .data-pre {
            font-family: inherit;
            white-space: pre-wrap;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 0.375rem;
            padding: 0.75rem;
            margin-bottom: 0;
            max-height: 150px;
            overflow-y: auto;
        }
        
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .action-btns .btn {
            margin-right: 0.25rem;
            margin-bottom: 0.25rem;
        }
        
        /* Modal improvements */
        .modal-pre {
            max-height: 60vh;
            overflow-y: auto;
            background-color: #f8fafc;
            padding: 1rem;
            border-radius: 0.375rem;
        }
        
        /* Capitalization helper classes */
        .capitalize {
            text-transform: capitalize;
        }
        
        .title-case {
            text-transform: lowercase;
            display: inline-block;
        }
        
        .title-case::first-letter {
            text-transform: uppercase;
        }
    </style>
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
          <li><a href="edit request.php" class="nav-link active"><i class="bi bi-pencil me-2"></i> Edit Requests</a></li> 
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


        <div class="main-content flex-grow-1">
        <div class="container-fluid">
            <div class="card-container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Edit Requests</h4>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Affiliate Name</th>
                                <th>Current Data</th>
                                <th>Requested Changes</th>
                                <th>Date Requested</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="edit-requests-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for reviewing update request -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="reviewModalLabel">Edit Request Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <pre id="updateDetails" class="modal-pre"></pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    loadEditRequests();
});
function formatJsonString(jsonStr, idPrefix = '') {
    try {
        const obj = JSON.parse(jsonStr);
        return Object.entries(obj).map(([key, value], index) => {
            // Format key with proper capitalization
            const formattedKey = key.toLowerCase().replace(/_/g, ' ')
                .split(' ')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                .join(' ');
            
            if (typeof value === 'string') {
                // Clean up string values
                let formattedValue = value.trim();
                
                // Capitalize first letter if it's not an acronym
                if (formattedValue.length > 0 && !formattedValue.match(/^[A-Z0-9_]+$/)) {
                    formattedValue = formattedValue.charAt(0).toUpperCase() + formattedValue.slice(1);
                }
                
                // Handle long descriptions
                if (key.toLowerCase() === 'description' && formattedValue.length > 50) {
                    const uniqueId = `${idPrefix}-desc-${index}`;
                    return `
                        <div class="mb-2">
                            <strong>${formattedKey}:</strong> 
                            <span id="${uniqueId}-short">${formattedValue.substring(0, 50)}...</span>
                            <span id="${uniqueId}-full" style="display:none;">${formattedValue}</span>
                            <button onclick="toggleDesc('${uniqueId}')" class="btn btn-link btn-sm p-0">Show more</button>
                        </div>
                    `;
                }
                return `<div class="mb-2"><strong>${formattedKey}:</strong> ${formattedValue}</div>`;
            } else if (typeof value === 'object' && value !== null) {
                return `<div class="mb-2"><strong>${formattedKey}:</strong> ${JSON.stringify(value, null, 2)}</div>`;
            }
            return `<div class="mb-2"><strong>${formattedKey}:</strong> ${value}</div>`;
        }).join('');
    } catch {
        // Fallback for non-JSON strings
        return `<div class="title-case">${jsonStr}</div>`;
    }
}

// Rest of your JavaScript remains the same
document.addEventListener('DOMContentLoaded', () => {
    loadEditRequests();
});

function toggleDesc(id) {
    const shortSpan = document.getElementById(id + '-short');
    const fullSpan = document.getElementById(id + '-full');
    const btn = fullSpan.nextElementSibling;

    if (fullSpan.style.display === 'none') {
        fullSpan.style.display = 'inline';
        shortSpan.style.display = 'none';
        btn.textContent = 'Show less';
    } else {
        fullSpan.style.display = 'none';
        shortSpan.style.display = 'inline';
        btn.textContent = 'Show more';
    }
}

function loadEditRequests() {
    fetch('fetch_edit_requests.php')
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('edit-requests-body');
            tbody.innerHTML = '';

            if (!data.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">No pending edit requests found</td></tr>';
                return;
            }

            data.forEach(item => {
                const tr = document.createElement('tr');
                tr.dataset.id = item.id;
                tr.dataset.type = item.type;

                // Format name with proper capitalization
                const formattedName = item.name 
                    ? item.name.toLowerCase()
                        .split(' ')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ')
                    : 'N/A';

                tr.innerHTML = `
                    <td class="align-middle">${formattedName}</td>
                    <td><pre class="data-pre">${formatJsonString(item.oldValue)}</pre></td>
                    <td><pre class="data-pre">${formatJsonString(item.newValue)}</pre></td>
                    <td class="align-middle">${item.dateRequested || 'N/A'}</td>
                    <td class="align-middle action-btns">
                        <button class="btn btn-primary btn-sm" onclick="reviewFull(${item.id}, '${item.type}')">
                            <i class="bi bi-eye"></i> Review
                        </button>
                        <button class="btn btn-success btn-sm" onclick="approveRequest(${item.id}, '${item.type}')">
                            <i class="bi bi-check"></i> Approve
                        </button>
                        <button class="btn btn-danger btn-sm" onclick="rejectRequest(${item.id}, '${item.type}')">
                            <i class="bi bi-x"></i> Reject
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            console.error('Error loading requests:', err);
            document.getElementById('edit-requests-body').innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-danger py-4">
                        Error loading requests. Please try again.
                    </td>
                </tr>
            `;
        });
}


function approveRequest(id, type) {
    if (!confirm('Approve this update?')) return;
    fetch('handle_edit_request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, type, action: 'approve'})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Request approved');
            loadEditRequests();
        } else {
            alert('❌ ' + data.message);
        }
    });
}

function rejectRequest(id, type) {
    if (!confirm('Reject this update?')) return;
    fetch('handle_edit_request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, type, action: 'reject'})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('❌ Request rejected');
            loadEditRequests();
        } else {
            alert('❌ ' + data.message);
        }
    });
}

function reviewFull(id, type) {
    fetch('handle_edit_request.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, type, action: 'review'})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('updateDetails').textContent =
                Object.entries(data.data).map(([k,v]) => `${k}: ${v}`).join('\n');
            const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
            modal.show();
        } else {
            alert('❌ ' + data.message);
        }
    });
}

</script>

</body>
</html>
