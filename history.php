<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: user sign up.php");
    exit;
}

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$accountID = $_SESSION['accountID'];
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $bookingID = intval($_POST['booking_id']);
    $cancellationReason = trim($_POST['cancellation_reason'] ?? '');

    // Verify booking exists and belongs to user
    $verifySql = "SELECT touristID, status, paymentStatus, serviceType FROM bookings WHERE bookingID = ?";
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->bind_param("i", $bookingID);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();

    if ($verifyResult->num_rows > 0) {
        $booking = $verifyResult->fetch_assoc();
        
        if ($booking['touristID'] == $accountID) {
            // Check if booking can be cancelled
            if ($booking['status'] === 'pending') {
                
                // Determine request type based on payment status
                $requestType = ($booking['paymentStatus'] === 'paid') ? 'refund_requested' : 'cancel_requested';
                
                // Create structured JSON data
                $requestData = [
                    'status' => 'waiting_approval',
                    'type' => $requestType,
                    'reason' => $cancellationReason,
                    'timestamp' => date('Y-m-d H:i:s'),
                    'user_id' => $accountID,
                    'serviceType' => $booking['serviceType'] ?? ''
                ];

                $jsonData = json_encode($requestData);

                // Update booking with cancellation request
                $updateSql = "UPDATE bookings SET cancelRequest = ? WHERE bookingID = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("si", $jsonData, $bookingID);

                if ($updateStmt->execute()) {
                    $_SESSION['success_message'] = "Your cancellation request has been submitted and is waiting for approval.";
                    header("Location: history.php");
                    exit;
                } else {
                    $error = "Database error: " . $conn->error;
                }
            } else {
                $error = "This booking cannot be cancelled in its current state.";
            }
        } else {
            $error = "You are not authorized to cancel this booking.";
        }
    } else {
        $error = "Booking not found.";
    }
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    $bookingID = intval($_POST['booking_id']);
    $rating = intval($_POST['rating']);
    $content = trim($_POST['content'] ?? '');
    
    // Verify booking exists, is completed, belongs to user, and has no existing review
    $verifySql = "SELECT b.touristID, b.serviceType, b.serviceID 
                 FROM bookings b
                 LEFT JOIN reviews r ON b.serviceType = r.serviceType AND b.serviceID = r.serviceID AND r.touristID = b.touristID
                 WHERE b.bookingID = ? AND b.status = 'completed' AND b.touristID = ? AND r.reviewID IS NULL";
    $verifyStmt = $conn->prepare($verifySql);
    $verifyStmt->bind_param("ii", $bookingID, $accountID);
    $verifyStmt->execute();
    $verifyResult = $verifyStmt->get_result();
    
    if ($verifyResult->num_rows > 0) {
        $booking = $verifyResult->fetch_assoc();
        
        // Insert review
        $insertSql = "INSERT INTO reviews (touristID, serviceType, serviceID, rating, content) 
                     VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("issis", $accountID, $booking['serviceType'], 
                              $booking['serviceID'], $rating, $content);
        
        if ($insertStmt->execute()) {
            $_SESSION['success_message'] = "Thank you for your review!";
            header("Location: history.php");
            exit;
        } else {
            $error = "Failed to submit review: " . $conn->error;
        }
    } else {
        $error = "You've already reviewed this booking or it's not eligible for review.";
    }
}

// Check for success message from redirect
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Fetch booking history with review data
$sql = "SELECT b.bookingID, b.serviceType, b.bookingDate, b.scheduledFor, b.endDate, b.quantity, 
               b.totalAmount, b.status, b.paymentStatus, b.cancelRequest, b.serviceID,
               r.reviewID, r.rating as review_rating, r.content as review_content, 
               r.reply as review_reply, r.replied_at as review_replied_at,
               CASE 
                   WHEN b.serviceType = 'hotel' THEN h.name
                   WHEN b.serviceType = 'tour' THEN tp.title
                   ELSE 'Unknown'
               END AS service_name,
               CASE 
                   WHEN b.serviceType = 'hotel' THEN h.image_path
                   WHEN b.serviceType = 'tour' THEN tp.image_path
                   ELSE ''
               END AS image_path,
               CASE 
                   WHEN b.serviceType = 'hotel' THEN h.location
                   WHEN b.serviceType = 'tour' THEN t.provider_name
                   ELSE ''
               END AS extra_info
        FROM bookings b
        LEFT JOIN hotels h ON b.serviceType = 'hotel' AND b.serviceID = h.hotelID
        LEFT JOIN tour_packages tp ON b.serviceType = 'tour' AND b.serviceID = tp.packageID
        LEFT JOIN tours t ON tp.tourID = t.tourID
        LEFT JOIN reviews r ON b.serviceType = r.serviceType AND b.serviceID = r.serviceID AND r.touristID = b.touristID
        WHERE b.touristID = ?
        ORDER BY b.bookingDate DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $accountID);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Booking History - Explore Ethiopia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
  <style>
    :root {
      --primary-color: #4e73df;
      --success-color: #1cc88a;
      --info-color: #36b9cc;
      --warning-color: #f6c23e;
      --danger-color: #e74a3b;
      --light-color: #f8f9fc;
      --dark-color: #5a5c69;
    }
    
    body {
      background-color: #f8f9fa;
      font-family: 'Nunito', -apple-system, BlinkMacSystemFont, sans-serif;
    }
    
    .navbar {
      box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .booking-card {
      transition: all 0.3s ease;
      border-radius: 0.35rem;
      overflow: hidden;
      border: 1px solid rgba(0,0,0,0.05);
    }
    
    .booking-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
    }
    
    .booking-status {
      padding: 0.25rem 0.75rem;
      border-radius: 2rem;
      font-size: 0.75rem;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    
    .status-pending {
      background-color: rgba(254, 200, 75, 0.2);
      color: #fe8c00;
    }
    
    .status-confirmed {
      background-color: rgba(40, 167, 69, 0.2);
      color: #28a745;
    }
    
    .status-cancelled {
      background-color: rgba(220, 53, 69, 0.2);
      color: #dc3545;
    }
    
    .status-rejected {
      background-color: rgba(253, 126, 20, 0.2);
      color: #fd7e14;
    }
    
    .badge-light {
      background-color: rgba(46, 175, 240);
      color: #5a5c69;
      font-weight: 500;
    }
    
    .action-buttons .btn {
      min-width: 120px;
      font-weight: 600;
      border-radius: 2rem;
      padding: 0.375rem 1rem;
    }
    
    .alert-notification {
      border-left: 1px solid;
      border-radius: 0.25rem;
    }
    
    footer {
      background-color: #fff;
      box-shadow: 0 -0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    }
    
    .service-image {
      height: 100%;
      object-fit: cover;
      object-position: center;
    }
    
    .card-body {
      padding: 1.5rem;
    }
    
    .modal-content {
      border: none;
      border-radius: 0.5rem;
    }
    
    .modal-header {
      border-bottom: none;
      padding-bottom: 0;
    }
    
    .reason-textarea {
      min-height: 120px;
      border-radius: 0.5rem;
    }
    .alert-warning {
  border-left: 4px solid #ffc107;
  background-color: rgba(255, 193, 7, 0.1);
}
.rating-stars {
  font-size: 1.5rem;
  cursor: pointer;
}

.rating-stars .star-rating {
  color: #e4e5e9;
}

.rating-stars .star-rating.text-warning {
  color: #ffc107;
}

.rating-stars:hover .star-rating {
  color: #ffc107;
}

.rating-stars .star-rating:hover ~ .star-rating {
  color: #e4e5e9;
}
.admin-message {
  background-color: rgba(248, 249, 250, 0.8);
  border-left: 3px solid var(--warning-color);
  padding: 0.75rem;
  border-radius: 0.25rem;
  margin-top: 0.5rem;
}
.rating-stars-static {
  font-size: 1.5rem;
}

.rating-stars-static .bi-star-fill {
  color: #e4e5e9;
}

.rating-stars-static .bi-star-fill.text-warning {
  color: #ffc107;
}
 .review-response {
      background-color: #f8f9fa;
      border-left: 3px solid #4e73df;
      padding: 10px;
      margin-top: 15px;
      border-radius: 5px;
    }
    
    .review-response small {
      color: #6c757d;
      display: block;
      margin-top: 5px;
    }
  </style>
</head>
<body>


<?php
require_once 'header.php'; 
?>

    

<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h2 class="mb-1">Booking History</h2>
      <p class="text-muted mb-0">View and manage your bookings</p>
    </div>
    <div class="d-flex gap-2">
      <a href="hotel booking.php" class="btn btn-primary">
        <i class="bi bi-building me-1"></i> Book Hotel
      </a>
      <a href="travel agents.php" class="btn btn-outline-primary">
        <i class="bi bi-globe me-1"></i> Book Tour
      </a>
    </div>
  </div>

  <!-- Alerts -->
  <?php if (!empty($message)): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
      <i class="bi bi-check-circle-fill me-2"></i>
      <?= htmlspecialchars($message) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Empty State -->
  <?php if (empty($bookings)): ?>
    <div class="card border-0 shadow-sm">
      <div class="card-body text-center py-5">
        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
        <h4 class="mt-3">No bookings yet</h4>
        <p class="text-muted mb-4">You haven't made any bookings yet. Get started by booking a hotel or tour!</p>
        <div class="d-flex justify-content-center gap-3">
          <a href="hotel booking.php" class="btn btn-primary">
            <i class="bi bi-building me-1"></i> Book Hotel
          </a>
          <a href="travel agents.php" class="btn btn-outline-primary">
            <i class="bi bi-globe me-1"></i> Book Tour
          </a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($bookings as $booking): 
    $cancelRequest = json_decode($booking['cancelRequest'] ?? '', true);
    $cancelStatus = $cancelRequest['status'] ?? null;
    $cancelType = $cancelRequest['type'] ?? null;
    $isWaitingApproval = $cancelStatus === 'waiting_approval';
    $isApproved = $cancelStatus === 'Approved';
    $isRejected = $cancelStatus === 'Rejected';
    $showCancelButton = ($booking['status'] === 'confirmed' || $booking['status'] === 'pending') 
                      && !$isWaitingApproval && !$isApproved;
  ?>

        <div class="col-md-6">
          <div class="card booking-card h-100">
            <div class="row g-0 h-100">
              <div class="col-md-4">
                <img src="<?= htmlspecialchars($booking['image_path'] ?? 'public/images/default.jpg') ?>" 
                     class="service-image w-100" 
                     alt="<?= htmlspecialchars($booking['service_name']) ?>">
              </div>
              <div class="col-md-8">
                <div class="card-body d-flex flex-column h-100">
                  <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                      <h5 class="card-title mb-0"><?= htmlspecialchars($booking['service_name']) ?></h5>
                      <span class="booking-status status-<?= str_replace(' ', '-', strtolower($booking['status'])) ?>">
                        <?= ucfirst($booking['status']) ?>
                        <?= $isRejected ? '(Refund Rejected)' : '' ?>
                      </span>
                    </div>

                    <!-- Rejection Notice -->
                   <?php if ($isRejected): ?>
  <div class="alert alert-warning p-3 mb-3">
    <div class="d-flex align-items-center">
      <i class="bi bi-exclamation-triangle-fill me-2 fs-4"></i>
      <div>
        <h6 class="mb-1">Refund Request Rejected</h6>
        <?php if (!empty($cancelRequest['admin_message'])): ?>
          <p class="mb-1"><strong>Admin Note:</strong> <?= htmlspecialchars($cancelRequest['admin_message']) ?></p>
        <?php endif; ?>
        <p class="small mb-0 text-muted">
          <i class="bi bi-clock-history me-1"></i>
          Rejected on <?= date('M j, Y \a\t g:i a', strtotime($cancelRequest['timestamp'])) ?>
        </p>
      </div>
    </div>
  </div>
<?php endif; ?>

                    <!-- Booking Details -->
                    <div class="d-flex flex-wrap gap-2 mb-3">
                      <span class="badge badge-light">
                        <i class="bi bi-calendar me-1"></i>
                        <?= date('M j, Y', strtotime($booking['bookingDate'])) ?>
                      </span>
                      
                      <?php if ($booking['serviceType'] === 'hotel'): ?>
                        <span class="badge badge-light">
                          <i class="bi bi-moon-stars me-1"></i>
                          <?= date_diff(new DateTime($booking['scheduledFor']), new DateTime($booking['endDate']))->days ?> Nights
                        </span>
                      <?php else: ?>
                        <span class="badge badge-light">
                          <i class="bi bi-people me-1"></i>
                          <?= $booking['quantity'] ?> Person<?= $booking['quantity'] > 1 ? 's' : '' ?>
                        </span>
                      <?php endif; ?>
                      
                      <span class="badge badge-light">
                        <i class="bi bi-geo-alt me-1"></i>
                        <?= htmlspecialchars($booking['extra_info']) ?>
                      </span>
                    </div>

                    <!-- Dates and Payment -->
                    <div class="mb-3">
                      <p class="mb-2 small">
                        <i class="bi bi-calendar<?= $booking['serviceType'] === 'hotel' ? '-check' : '-event' ?> me-1"></i>
                        <strong>Dates:</strong> 
                        <?= date('M j, Y', strtotime($booking['scheduledFor'])) ?>
                        <?php if ($booking['serviceType'] === 'hotel'): ?>
                          - <?= date('M j, Y', strtotime($booking['endDate'])) ?>
                        <?php endif; ?>
                      </p>
                      <p class="mb-0 small">
                        <i class="bi bi-credit-card me-1"></i>
                        <strong>Payment:</strong> 
                        <span class="text-capitalize"><?= $booking['paymentStatus'] ?></span>
                      </p>
                    </div>

                    <!-- Price -->
                    <div class="d-flex justify-content-between align-items-center mt-auto">
                      <h5 class="mb-0 text-primary">
                        <i class="bi bi-currency-exchange me-1"></i>
                        <?= number_format($booking['totalAmount'], 2) ?> ETB
                      </h5>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                   <div class="d-flex justify-content-between mt-3 pt-3 border-top">
          <a href="receipt.php?booking_id=<?= $booking['bookingID'] ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-receipt me-1"></i> Receipt
          </a>
          
          <?php if ($booking['status'] === 'completed'): ?>
            <?php if (empty($booking['reviewID'])): ?>
              <!-- Show review button if no review exists -->
              <button type="button" class="btn btn-sm btn-success" 
                      data-bs-toggle="modal" 
                      data-bs-target="#reviewModal"
                      data-booking-id="<?= $booking['bookingID'] ?>">
                <i class="bi bi-star-fill me-1"></i> Leave Review
              </button>
            <?php else: ?>
              <!-- Show view review button if review exists -->
              <button type="button" class="btn btn-sm btn-info" 
                      data-bs-toggle="modal" 
                      data-bs-target="#viewReviewModal"
                      data-review-rating="<?= $booking['review_rating'] ?>"
                      data-review-content="<?= htmlspecialchars($booking['review_content']) ?>"
                      data-review-reply="<?= htmlspecialchars($booking['review_reply']) ?>"
                      data-review-replied-at="<?= $booking['review_replied_at'] ?>">
                <i class="bi bi-chat-square-text me-1"></i> View Review
              </button>
            <?php endif; ?>
          <?php endif; ?>
          
          <?php if ($isWaitingApproval): ?>
            <button class="btn btn-sm btn-secondary" disabled>
              <i class="bi bi-hourglass-split me-1"></i>
              <?= ($cancelType === 'refund_requested') ? 'Refund Pending' : 'Cancel Pending' ?>
            </button>
          <?php elseif ($isApproved): ?>
            <button class="btn btn-sm btn-success" disabled>
              <i class="bi bi-check-circle me-1"></i>
              <?= ($cancelType === 'refund_requested') ? 'Refund Approved' : 'Cancellation Approved' ?>
            </button>
          <?php elseif ($isRejected): ?>
            <button class="btn btn-sm btn-warning" disabled>
              <i class="bi bi-exclamation-triangle me-1"></i>
              Refund Rejected
            </button>
          <?php elseif ($showCancelButton): ?>
            <button type="button" class="btn btn-sm btn-outline-danger" 
                    data-bs-toggle="modal" 
                    data-bs-target="#cancelModal" 
                    data-booking-id="<?= $booking['bookingID'] ?>"
                    data-is-paid="<?= $booking['paymentStatus'] === 'paid' ? 'true' : 'false' ?>">
              <i class="bi bi-x-circle me-1"></i> 
              <?= ($booking['paymentStatus'] === 'paid') ? 'Request Refund' : 'Cancel' ?>
            </button>
          <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</main>

<!-- Cancel Booking Modal -->
<div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="cancelBookingForm" method="POST" action="history.php">
        <div class="modal-header">
          <h5 class="modal-title" id="cancelModalLabel">Cancel Booking</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p id="modalMessage" class="mb-3">Are you sure you want to cancel this booking?</p>
          <div class="mb-3">
            <label for="cancellation_reason" class="form-label">Reason (optional):</label>
            <textarea class="form-control reason-textarea" name="cancellation_reason" id="cancellation_reason" rows="3"></textarea>
          </div>
          <input type="hidden" name="booking_id" id="modalBookingId">
          <input type="hidden" name="cancel_booking" value="1">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-danger" id="confirmCancelBtn">Confirm</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Review Modal -->
  <div class="modal fade" id="reviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="reviewForm" method="POST" action="history.php">
          <div class="modal-header">
            <h5 class="modal-title">Leave a Review</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Rating</label>
              <div class="rating-stars">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                  <i class="bi bi-star-fill star-rating" data-rating="<?= $i ?>"></i>
                <?php endfor; ?>
              </div>
              <input type="hidden" name="rating" id="ratingValue" required>
            </div>
            <div class="mb-3">
              <label for="reviewContent" class="form-label">Your Review</label>
              <textarea class="form-control" name="content" id="reviewContent" rows="4" required></textarea>
            </div>
            <input type="hidden" name="booking_id" id="reviewBookingId">
            <input type="hidden" name="submit_review" value="1">
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Submit Review</button>
          </div>
        </form>
      </div>
    </div>
  </div>
<!-- View Review Modal -->
 <div class="modal fade" id="viewReviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Your Review</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Your Rating</label>
            <div class="rating-stars-static">
              <?php for ($i = 1; $i <= 5; $i++): ?>
                <i class="bi bi-star-fill" id="staticStar<?= $i ?>"></i>
              <?php endfor; ?>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label">Your Review</label>
            <div class="card bg-light p-3">
              <p id="viewReviewContent"></p>
            </div>
          </div>
          
          <div id="reviewResponseContainer" class="d-none">
            <hr>
            <div class="mb-3">
              <label class="form-label">Owner's Response</label>
              <div class="card bg-light p-3">
                <p id="viewReviewReply"></p>
                <small class="text-muted" id="viewReviewRepliedAt"></small>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
   <!-- Footer -->
    <footer class="bg-dark text-white pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <h5 class="fw-bold mb-3">Explore Ethiopia</h5>
                    <p>Your gateway to seamless travel experiences in Ethiopia. Book hotels, rides, and tours all in one place.</p>
                    <div class="social-icons mt-3">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-twitter fs-5"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-5"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="index.php" class="text-white text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="hotel booking.php" class="text-white text-decoration-none">Hotels</a></li>
                        <li class="mb-2"><a href="ride booking.php" class="text-white text-decoration-none">Rides</a></li>
                        <li class="mb-2"><a href="travel agents.php" class="text-white text-decoration-none">Travel Agents</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Support</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="faq.php" class="text-white text-decoration-none">FAQs</a></li>
                        <li class="mb-2"><a href="contact.php" class="text-white text-decoration-none">Contact Us</a></li>
                        <li class="mb-2"><a href="privacy.php" class="text-white text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="terms.php" class="text-white text-decoration-none">Terms of Service</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="fw-bold mb-3">Contact Info</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> contact@AllInOne.com</li>
                        <li class="mb-2"><i class="bi bi-telephone me-2"></i> +251 123 456 789</li>
                        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i> Addis Ababa, Ethiopia</li>
                    </ul>
                </div>
            </div>
            <hr class="my-4">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 Explore Ethiopia. All rights reserved.</p>
                </div>

            </div>
        </div>
    </footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
  // Modal initialization
  const cancelModal = document.getElementById('cancelModal');
  if (cancelModal) {
    cancelModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const bookingId = button.getAttribute('data-booking-id');
      const isPaid = button.getAttribute('data-is-paid') === 'true';
      
      document.getElementById('modalBookingId').value = bookingId;
      
      const modalTitle = document.getElementById('cancelModalLabel');
      const modalMessage = document.getElementById('modalMessage');
      const confirmBtn = document.getElementById('confirmCancelBtn');
      
      if (isPaid) {
        modalTitle.textContent = 'Request Refund';
        modalMessage.textContent = 'Are you sure you want to request a refund for this booking?';
        confirmBtn.textContent = 'Request Refund';
      } else {
        modalTitle.textContent = 'Cancel Booking';
        modalMessage.textContent = 'Are you sure you want to cancel this booking?';
        confirmBtn.textContent = 'Confirm Cancellation';
      }
    });
  }

  // Form submission
  const cancelForm = document.getElementById('cancelBookingForm');
  if (cancelForm) {
    cancelForm.addEventListener('submit', function(e) {
      const submitBtn = document.getElementById('confirmCancelBtn');
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Processing...';
    });
  }
});
// View Review modal initialization
const viewReviewModal = document.getElementById('viewReviewModal');
if (viewReviewModal) {
  viewReviewModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    const rating = button.getAttribute('data-review-rating');
    const content = button.getAttribute('data-review-content');
    const reply = button.getAttribute('data-review-reply');
    const repliedAt = button.getAttribute('data-review-replied-at');
    
    // Set rating stars
    for (let i = 1; i <= 5; i++) {
      const star = document.getElementById('staticStar' + i);
      if (i <= rating) {
        star.classList.add('text-warning');
      } else {
        star.classList.remove('text-warning');
      }
    }
    
    // Set content
    document.getElementById('viewReviewContent').textContent = content;
    
    // Set response if exists
    const responseContainer = document.getElementById('reviewResponseContainer');
    if (reply) {
      responseContainer.classList.remove('d-none');
      document.getElementById('viewReviewReply').textContent = reply;
      document.getElementById('viewReviewRepliedAt').textContent = 
        'Responded on: ' + new Date(repliedAt).toLocaleString();
    } else {
      responseContainer.classList.add('d-none');
    }
  });
}
// Review modal initialization
const reviewModal = document.getElementById('reviewModal');
if (reviewModal) {
  reviewModal.addEventListener('show.bs.modal', function(event) {
    const button = event.relatedTarget;
    document.getElementById('reviewBookingId').value = button.getAttribute('data-booking-id');
    
    // Reset stars
    document.querySelectorAll('.star-rating').forEach(star => {
      star.classList.remove('text-warning');
    });
    document.getElementById('ratingValue').value = '';
  });
  
  // Star rating interaction
  document.querySelectorAll('.star-rating').forEach(star => {
    star.addEventListener('click', function() {
      const rating = this.getAttribute('data-rating');
      document.getElementById('ratingValue').value = rating;
      
      // Highlight selected stars
      document.querySelectorAll('.star-rating').forEach(s => {
        if (s.getAttribute('data-rating') <= rating) {
          s.classList.add('text-warning');
        } else {
          s.classList.remove('text-warning');
        }
      });
    });
  });
}
</script>
</body>
</html>