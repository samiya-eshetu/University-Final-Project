<?php
session_start();
require 'db.php';

// Redirect if not logged in
if (!isset($_SESSION['accountID'])) {
    header("Location: user sign up.php");
    exit;
}

$connection = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

    // Get user information
    $accountID = $_SESSION['accountID'];
    $userInfoString = "";

    $userSql = "SELECT 
        a.accountID, a.username, a.email, a.role, a.status, a.createdAt,
        t.fullName AS touristName, t.phoneNumber AS touristPhone, t.nationality,
        ho.businessName AS hotelBusiness, ho.phoneNumber AS hotelPhone, ho.location AS hotelLocation,
        to_o.businessName AS tourBusiness, to_o.phoneNumber AS tourPhone, to_o.location AS tourLocation,
        ro.businessName AS rideBusiness, ro.phoneNumber AS ridePhone, ro.location AS rideLocation
    FROM 
        accounts a
    LEFT JOIN 
        tourists t ON a.accountID = t.touristID
    LEFT JOIN 
        hotel_owners ho ON a.accountID = ho.ownerID AND a.role = 'hotel_owner'
    LEFT JOIN 
        tour_owners to_o ON a.accountID = to_o.ownerID AND a.role = 'tour_owner'
    LEFT JOIN 
        ride_owners ro ON a.accountID = ro.ownerID AND a.role = 'ride_owner'

    WHERE 
        a.accountID = ?";

$userStmt = $connection->prepare($userSql);
$userStmt->bind_param("i", $accountID);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
    
    // Build the user info string
    $userInfoString = "User ID: {$userData['accountID']}, " .
                      "Username: {$userData['username']}, " .
                      "Email: {$userData['email']}, " .
                      "Role: {$userData['role']}, " .
                      "Status: {$userData['status']}, " .
                      "Created At: {$userData['createdAt']}, ";
    
    // Add role-specific information
    switch ($userData['role']) {
        case 'tourist':
            $userInfoString .= "Full Name: {$userData['touristName']}, " .
                               "Phone: {$userData['touristPhone']}, " .
                               "Nationality: {$userData['nationality']}";
            break;
        case 'hotel_owner':
            $userInfoString .= "Business Name: {$userData['hotelBusiness']}, " .
                               "Phone: {$userData['hotelPhone']}, " .
                               "Location: {$userData['hotelLocation']}";
            break;
        case 'tour_owner':
            $userInfoString .= "Business Name: {$userData['tourBusiness']}, " .
                               "Phone: {$userData['tourPhone']}, " .
                               "Location: {$userData['tourLocation']}";
            break;
        case 'ride_owner':
            $userInfoString .= "Business Name: {$userData['rideBusiness']}, " .
                               "Phone: {$userData['ridePhone']}, " .
                               "Location: {$userData['rideLocation']}";
            break;
        case 'admin':
            $userInfoString .= "Full Name: {$userData['touristName']}, " .
                               "Phone: {$userData['touristPhone']}, " .
                               "Nationality: {$userData['nationality']}";
            break;
        default:
            $userInfoString .= "No additional role-specific information";
    }
}
$userStmt->close();

// Rest of your existing code
$type = $_GET['type'] ?? 'hotel';

if ($type === 'hotel') {
    $hotelID = isset($_GET['hotel_id']) ? intval($_GET['hotel_id']) : 0;
    $sql = "SELECT * FROM hotels WHERE hotelID = ? AND status = 'approved'";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $hotelID);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($type === 'tour') {
    $packageID = isset($_GET['package_id']) ? intval($_GET['package_id']) : 0;
    $sql = "SELECT tp.*, t.provider_name FROM tour_packages tp
            JOIN tours t ON tp.tourID = t.tourID
            WHERE tp.packageID = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $packageID);
    $stmt->execute();
    $result = $stmt->get_result();
}

if (!$result || $result->num_rows === 0) {
    echo "<h3 class='text-danger text-center mt-5'>Item not found.</h3>";
    exit;
}

$data = $result->fetch_assoc();
$pricePerNight = floatval($data["price"] ?? $data["pricePerNight"]);
$stmt->close();

// Calculate dates and nights
$startDate = $_GET["start_date"] ?? date('Y-m-d');
$endDate = $_GET["end_date"] ?? date('Y-m-d', strtotime('+1 day'));
$guests = isset($_GET["guests"]) ? intval($_GET["guests"]) : 2;

$start = new DateTime($startDate);
$end = new DateTime($endDate);
$nights = $start->diff($end)->days;
if ($nights < 1) $nights = 1;

$subtotal = $pricePerNight * $nights;
$serviceFee = $subtotal * 0.05;  // 5% service fee
$total = $subtotal + $serviceFee;

// Now you can use $userInfoString wherever you need it in your HTML/processing
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Booking | Explore Ethiopia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="public/css/style.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --success-color: #1cc88a;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .booking-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
        }
        
        .payment-method {
            transition: all 0.3s;
            border: 2px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 1rem;
            cursor: pointer;
            height: 100%;
        }
        
        .payment-method:hover {
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border-color: var(--primary-color);
        }
        
        .payment-method.active {
            border-color: var(--primary-color) !important;
            background-color: rgba(78, 115, 223, 0.05);
        }
        
        .payment-method img {
            height: 30px;
            object-fit: contain;
        }
        
        .price-breakdown {
            background-color: var(--secondary-color);
            border-radius: 0.5rem;
            padding: 1.5rem;
        }
        
        .price-item {
            border-bottom: 1px dashed #dee2e6;
            padding: 0.75rem 0;
        }
        
        .price-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .service-image {
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .card-details {
            background-color: var(--secondary-color);
            border-radius: 0.5rem;
            padding: 1.5rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .tab-content {
            padding: 1.5rem 0;
        }
        
        .date-display {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        
        .guests-display {
            background-color: white;
            border-radius: 0.5rem;
            padding: 1rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        
        .edit-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(255,255,255,0.8);
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .edit-btn:hover {
            background: white;
            transform: rotate(90deg);
        }
    </style>
</head>
<body>



<?php
require_once 'header.php'; 
?>

    
    <!-- Rest of the content remains the same -->
    <div class="container py-5">
   
        <div class="row g-4">
       <!-- Selected Trip Info -->
        <div class="col-lg-6">
            <div class="booking-card card mb-4">
                <div class="position-relative">
                    <img src="<?= htmlspecialchars($data["image_path"]) ?>" class="service-image w-100" alt="<?= htmlspecialchars($data["name"] ?? $data["title"]) ?>">
                    <div class="edit-btn" onclick="window.history.back()">
                        <i class="bi bi-pencil text-primary"></i>
                    </div>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h4 class="card-title mb-1"><?= htmlspecialchars($data["name"] ?? $data["title"] ?? 'Unknown') ?></h4>
                            <span class="badge bg-success"><?= $type === 'hotel' ? 'Hotel' : 'Tour Package' ?></span>
                        </div>
                        <div class="text-end">
                            <div class="text-muted small">Price per <?= $type === 'hotel' ? 'night' : 'person' ?></div>
                            <div class="h5 text-primary"><?= number_format($pricePerNight, 2) ?> ETB</div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <?php if ($type === 'hotel'): ?>
                            <div class="date-display mb-3 position-relative">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <small class="text-muted">CHECK-IN</small>
                                        <div class="fw-bold"><?= date('D, M j, Y', strtotime($startDate)) ?></div>
                                    </div>
                                    <div class="text-center px-3">
                                        <div class="bg-light rounded-pill py-1 px-3">
                                            <small class="text-muted"><?= $nights ?> night<?= $nights > 1 ? 's' : '' ?></small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted">CHECK-OUT</small>
                                        <div class="fw-bold"><?= date('D, M j, Y', strtotime($endDate)) ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="guests-display mb-3">
                                <small class="text-muted">GUESTS</small>
                                <div class="fw-bold"><?= $guests ?> <?= $guests == 1 ? "Guest" : "Guests" ?></div>
                            </div>
                        <?php else: ?>
                            <div class="date-display mb-3">
                                <small class="text-muted">TOUR DATE</small>
                                <div class="fw-bold"><?= date('D, M j, Y', strtotime($startDate)) ?></div>
                            </div>
                            
                            <div class="guests-display mb-3">
                                <small class="text-muted">TRAVELERS</small>
                                <div class="fw-bold"><?= $guests ?> <?= $guests == 1 ? "Person" : "People" ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="d-flex align-items-center">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i>
                            <div>
                                <small class="text-muted">LOCATION</small>
                                <div class="fw-bold"><?= htmlspecialchars($data["location"] ?? 'Unknown') ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($data["description"])): ?>
                        <div class="border-top pt-3">
                            <h6 class="fw-bold mb-2">Description</h6>
                            <p class="text-muted"><?= htmlspecialchars($data["description"]) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="booking-card card">

            </div>
        </div>
        
                <!-- Payment Section -->
                <div class="col-lg-6">

                    <div class="card border-0 shadow-sm">
                                    <div class="card-body">
                        <h5 class="fw-bold mb-4">Price Breakdown</h5>
                    
                            <div class="price-breakdown bg-light p-4 rounded-3 mb-4">
                                <h5 class="fw-semibold mb-3">Price Breakdown</h5>
    
                                <?php if ($type === 'hotel'): ?>
                                    <div class="price-item d-flex justify-content-between py-2 border-bottom">
                                        <div class="text-secondary">
                                            <?= number_format($pricePerNight, 2) ?> ETB × <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?>
                                        </div>
                                        <div class="fw-medium">
                                            <?= number_format($subtotal, 2) ?> ETB
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="price-item d-flex justify-content-between py-2 border-bottom">
                                        <div class="text-secondary">
                                            Package price 
                                        </div>
                                        <div class="fw-medium">
                                            <?= number_format($subtotal, 2) ?> ETB
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <div class="price-item d-flex justify-content-between py-2 border-bottom">
                                    <div class="text-secondary">Service fee 5%</div>
                                    <div><?= number_format($serviceFee, 2) ?> ETB</div>
                                </div>

                                <div class="price-item d-flex justify-content-between pt-3">
                                    <div class="fw-bold fs-5">Total (ETB)</div>
                                    <div class="text-primary fw-bold fs-5"><?= number_format($total, 2) ?> ETB</div>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <h5 class="fw-semibold mb-3">Payment Method</h5>
    
                                <div class="d-flex flex-wrap gap-3 mb-4">

                                    <?php
                                    $public_key = 'CHAPUBK_TEST-04SpgjsBgOmcq07WpFKbiC8tZU69DmgB';
                                    $tx_ref = 'TX' . time() . '_' . rand(1000,9999);

                                    // Store booking data in session
                                    $_SESSION['chapa_booking_data'] = [
                                        'tx_ref' => $tx_ref,
                                        'type' => $type,
                                        'service_id' => $type === 'hotel' ? $data['hotelID'] : $data['packageID'],
                                        'start_date' => $startDate,
                                        'end_date' => $endDate,
                                        'guests' => $guests,
                                        'quantity' => $type === 'hotel' ? $nights : 1,
                                        'total' => $total,
                                        'payment_method' => 'chapa',
                                        'user_id' => $_SESSION['accountID']
                                    ];

        
                                    $host = $_SERVER['HTTP_HOST']; // Gets host (e.g., localhost or localhost:8080)
                                    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
                                    $base_url = "$protocol://$host/Final_Project";

                                    $return_url = "$base_url/process_booking.php?tx_ref=$tx_ref";
                                    ?>
        
                                    <form method="POST" action="https://api.chapa.co/v1/hosted/pay" class="flex-fill">
                                        <input type="hidden" name="public_key" value="<?= $public_key ?>" />
                                        <input type="hidden" name="tx_ref" value="<?= $tx_ref ?>" />
                                        <input type="hidden" name="amount" value="<?= $total ?>" />
                                        <input type="hidden" name="currency" value="ETB" />
                                        <input type="hidden" name="email" value="<?= $userData['email'] ?>" />
                                        <input type="hidden" name="first_name" value="<?= $userData['touristName'] ?>" />
                                        <input type="hidden" name="last_name" value=" " />
                                        <input type="hidden" name="title" value="Explore Ethiopia Booking" />
                                        <input type="hidden" name="description" value="Paying with Confidence with cha" />
                                        <input type="hidden" name="logo" value="https://chapa.link/asset/images/chapa_swirl.svg" />
                                        <input type="hidden" name="callback_url" value="https://example.com/callbackurl" />
                                        <input type="hidden" name="return_url" value="<?= $return_url ?>" />
            
                                        <button type="submit" class="btn payment-method border w-100 h-100 d-flex align-items-center justify-content-center p-3">
                                            <img src="public/images/chapa.png" alt="Chapa" width="120" class="img-fluid">
                                        </button>
                                    </form>

                                    <button type="button" class="btn payment-method border flex-fill d-flex align-items-center justify-content-center p-3" data-method="cbe">
                                        <img src="public/images/cbe.png" alt="CBE" width="30" class="me-2">
                                        <span>CBE</span>
                                    </button>

                                    <button type="button" class="btn payment-method border flex-fill d-flex align-items-center justify-content-center p-3" data-method="mastercard">
                                        <img src="public/images/mastercard.png" alt="Mastercard" width="30" class="me-2">
                                        <span>Mastercard</span>
                                    </button>
                                </div>
                            </div>
                            
                            
                        </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Payment method selection
        const paymentMethods = document.querySelectorAll('.payment-method');
        const paymentInput = document.getElementById('selectedPaymentMethod');
        
        paymentMethods.forEach(method => {
            method.addEventListener('click', function() {
                // Remove active class from all buttons
                paymentMethods.forEach(m => m.classList.remove('active'));
                
                // Add active class to clicked button
                this.classList.add('active');
                
                // Update hidden input value
                paymentInput.value = this.dataset.method;
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const cardNumber = this.querySelector('input[type="text"][placeholder*="Card Number"]').value;
            if (cardNumber && !/^\d{16}$/.test(cardNumber.replace(/\s/g, ''))) {
                e.preventDefault();
                alert('Please enter a valid 16-digit card number');
            }
        });
    </script>
</body>
</html>
<?php
$connection->close();
?>