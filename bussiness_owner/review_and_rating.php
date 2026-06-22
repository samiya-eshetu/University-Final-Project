<?php
session_start();

$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);


// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}


$accountID = $_SESSION['accountID'];

// Check business ownership

$ownerType = null;
$ownerTables = ['hotel_owners', 'ride_owners', 'tour_owners'];

foreach ($ownerTables as $table) {
    $check = $conn->query("SELECT ownerID FROM $table WHERE ownerID = $accountID");
    if ($check && $check->num_rows > 0) {
        $ownerType = $table;
        break;
    }
}


$sort = $_GET['sort'] ?? 'latest';

switch ($sort) {
    case 'highest': $orderBy = 'r.rating DESC'; break;
    case 'lowest': $orderBy = 'r.rating ASC'; break;
    case 'unresponded': $orderBy = 'r.reply IS NOT NULL'; break;
    default: $orderBy = 'r.createdAt DESC'; break;
}

$query = "
            SELECT r.*, t.fullName,
                h.name AS hotelName,
                ri.provider_name AS rideTitle,
                tp.title AS tourTitle
            FROM reviews r
            JOIN tourists t ON r.touristID = t.touristID
            LEFT JOIN hotels h ON r.serviceType = 'hotel' AND r.serviceID = h.hotelID AND h.ownerID = $accountID
            LEFT JOIN rides ri ON r.serviceType = 'ride' AND r.serviceID = ri.rideID AND ri.ownerID = $accountID
            LEFT JOIN tour_packages tp ON r.serviceType = 'tour' AND r.serviceID = tp.packageID
            LEFT JOIN tours tour ON tp.tourID = tour.tourID AND tour.ownerID = $accountID
            WHERE (
                (r.serviceType = 'hotel' AND h.ownerID IS NOT NULL) OR
                (r.serviceType = 'ride' AND ri.ownerID IS NOT NULL) OR
                (r.serviceType = 'tour' AND tour.ownerID IS NOT NULL)
            )
            ORDER BY $orderBy
        ";

$result = $conn->query($query);







if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reviewID = $_POST['reviewID'];

    // If reply field is set, handle reply update
    if (isset($_POST['reply'])) {
        $reply = $conn->real_escape_string($_POST['reply']);
        $now = date('Y-m-d H:i:s');

        $stmt = $conn->prepare("UPDATE reviews SET reply = ?, replied_at = ? WHERE reviewID = ?");
        $stmt->bind_param("ssi", $reply, $now, $reviewID);

        if ($stmt->execute()) {
            $success = "Reply saved successfully!";
        } else {
            $error = "Failed to save reply: " . $stmt->error;
        }

        $stmt->close();
    } 

    // Otherwise, treat as report submission
    else {
        $stmt = $conn->prepare("UPDATE reviews SET reported = 'reported' WHERE reviewID = ?");
        $stmt->bind_param("i", $reviewID);

        if ($stmt->execute()) {
            $success = "Report submitted for admin review.";
        } else {
            $error = "Failed to report review: " . $stmt->error;
        }

        $stmt->close();
    }
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Listings - Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>

        .review-box {
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #198754; /* Green for replied */
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.4s ease-in-out;
            transition: background-color 0.3s ease;
        }
        .review-box.unreplied {
            border-left-color: #ffc107; /* Yellow for unreplied */
            background-color: #fff9e6;
        }
        .reply-form {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .reply-form input[type="text"] {
            flex-grow: 1;
        }
    
    }




.review-box {
    animation: fadeIn 0.5s ease-out; /* Smooth entry animation */
}
        .review-box.unreplied {
            background-color: #FFD580;
        }
    </style>

</head>
<body>
<div class="d-flex">
        <!-- Sidebar -->
        <div class="flex-shrink-0 p-3 bg-light border-end" style="width: 250px; height: 100vh; position: fixed;">
            <a href="#" class="d-flex align-items-center mb-3 text-decoration-none">
                <i class="bi bi-speedometer2 me-2 fs-4"></i>
                <span class="fs-5 fw-bold">Dashboard</span>
            </a>
            <hr>
    <ul class="nav nav-pills flex-column mb-auto">
        <li>
            <a href="dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>
        </li>

        <li>
            <a href="manage_listings.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'manage_listings.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-card-list me-2"></i> Manage Listings
            </a>
        </li>

        <li>
            <a href="payment_management.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'payment_management.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-currency-exchange me-2"></i> Payment
            </a>
        </li>
        <li>
            <a href="booking_dashboard.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'booking_dashboard.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-suitcase me-2"></i> Bookings
            </a>
        </li>
        <li>
            <a href="review_and_rating.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'review_and_rating.php' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-star me-2"></i> Reviews & Ratings
            </a>
        </li>
        <li>
            <a href="business_setting.php" class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'business_setting.html' ? 'active' : 'text-dark' ?>">
                <i class="bi bi-gear me-2"></i> Profile Settings
            </a>
        </li>
    </ul>
    <!-- Return to Home Button -->
    <div class="mt-auto pt-3 border-top">
        <a href="../index.php" 
           class="btn btn-outline-primary d-flex align-items-center w-100 py-2">
            <i class="bi bi-house-door me-2"></i>
            <span>Main Page</span>
        </a>
    </div>
</div>

    <!-- Main content -->
    <div class="container-fluid p-4" style="margin-left: 250px;">

        <h4 class="fw-bold mb-4">Customer Reviews & Ratings</h4>

        <!-- Filter Options -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <form method="GET" class="d-flex align-items-center gap-2">
                <label for="sortReviews" class="fw-bold mb-0">Sort By:</label>
                <select name="sort" id="sortReviews" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Latest</option>
                    <option value="highest" <?= $sort === 'highest' ? 'selected' : '' ?>>Highest Rating</option>
                    <option value="lowest" <?= $sort === 'lowest' ? 'selected' : '' ?>>Lowest Rating</option>
                    <option value="unresponded" <?= $sort === 'unresponded' ? 'selected' : '' ?>>Unresponded Reviews</option>
                </select>
            </form>
            <form method="POST" action="export_reviews.php">
                <button type="submit" class="btn btn-sm btn-primary" title="Download reviews as CSV or Excel">Export Reviews</button>
            </form>
        </div>


         <?php if (isset($success)): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php elseif (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>


        <!-- Review Cards -->
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="review-box <?= empty($row['reply']) ? 'unreplied' : '' ?>">
                <div class="d-flex justify-content-between align-items-start">
                  <div>
                    <strong><?= htmlspecialchars($row['fullName']) ?></strong>
                    <div class="text-muted small">
                      <?= date('F j, Y', strtotime($row['createdAt'])) ?>
                    <span class="badge bg-secondary ms-2">
                      <?php
                        if ($row['serviceType'] === 'hotel') {
                            echo 'Hotel: ' . htmlspecialchars($row['hotelName']);
                        } elseif ($row['serviceType'] === 'ride') {
                            echo 'Ride: ' . htmlspecialchars($row['rideTitle']);
                        } elseif ($row['serviceType'] === 'tour') {
                            echo 'Tour: ' . htmlspecialchars($row['tourTitle']);
                        } else {
                            echo ucfirst($row['serviceType']) . " #" . $row['serviceID'];
                        }
                      ?>
                    </span>
                    </div>
                  </div>
                  <div>
                    <?php
                    $stars = intval($row['rating']);
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $stars
                            ? '<i class="bi bi-star-fill text-warning"></i>'
                            : '<i class="bi bi-star text-muted"></i>';
                    }
                    ?>
                  </div>
                </div>


                    <p class="mt-3 mb-2"><?= htmlspecialchars($row['content']) ?></p>

                    <form method="POST"  class="reply-form">
                        <input type="hidden" name="reviewID" value="<?= $row['reviewID'] ?>">
                        <input type="text" name="reply" class="form-control form-control-sm" value="<?= htmlspecialchars(stripslashes($row['reply'] ?? '')) ?>" placeholder="Write or edit reply..." required>
                        <button type="submit" class="btn btn-success btn-sm" title="Save your reply">Save</button>
                    </form>

                    <form method="POST"  class="mt-2 text-end">
                        <input type="hidden" name="reviewID" value="<?= $row['reviewID'] ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Report this review">Report</button>
                    </form>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="alert alert-warning text-center">No reviews found.</div>
        <?php endif; ?>
    </div>
</div>

<!-- Optional Bootstrap Tooltips Activation -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const tooltipTriggerList = document.querySelectorAll('[title]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => el.style.display = 'none');
    }, 3000); // Hide after 3 seconds

</script>
</body>
</html>
