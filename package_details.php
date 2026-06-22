<?php
session_start();
$connection = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

// Check if package ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: events.php");
    exit();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['accountID'])) {
    header("Location: user sign up.php");
    exit; 
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
    tourists t ON a.accountID = t.touristID AND a.role = 'tourist'
LEFT JOIN 
    hotel_owners ho ON a.accountID = ho.ownerID AND a.role = 'hotel_owner'
LEFT JOIN 
    tour_owners to_o ON a.accountID = to_o.ownerID AND a.role = 'tour_owner'
LEFT JOIN 
    ride_owners ro ON a.accountID = ro.ownerID AND a.role = 'ride_owner'
WHERE 
    a.accountID = ?";

$userStmt = $connection->prepare($userSql);
if (!$userStmt) {
    die("Prepare failed: " . $connection->error);
}
$userStmt->bind_param("i", $accountID);
$userStmt->execute();
$userResult = $userStmt->get_result();

if ($userResult->num_rows > 0) {
    $userData = $userResult->fetch_assoc();
}
$userStmt->close();

$packageID = $_GET['id'];

// Fetch package details
$packageQuery = "SELECT 
    p.*, 
    h.hotelID, h.name AS hotel_name, h.description AS hotel_desc, h.pricePerNight, h.image_path AS hotel_image,
    tp.packageID AS tour_packageID, tp.title AS tour_title, tp.description AS tour_desc, tp.price AS tour_price, tp.image_path AS tour_image,
    r.rideID, r.provider_name AS ride_name, r.description AS ride_desc,r.image AS ride_image 
    FROM all_in_one_packages p
    LEFT JOIN hotels h ON p.hotelID = h.hotelID
    LEFT JOIN tour_packages tp ON p.tourID = tp.packageID
    LEFT JOIN rides r ON p.rideID = r.rideID
    WHERE p.packageID = ?"; 


$stmt = $connection->prepare($packageQuery);
if (!$stmt) {
    die("Prepare failed: " . $connection->error);
}
$stmt->bind_param("i", $packageID);
$stmt->execute();
$package = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$package) {
    header("Location: events.php");
    exit();
}

// Handle package booking submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_package'])) {
    // Calculate total amount
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    $quantity = $_POST['quantity'];
    
    // Calculate duration in days
    $days = (new DateTime($endDate))->diff(new DateTime($startDate))->days;
    $days = max(1, $days); // Ensure at least 1 day
    
    $totalAmount = 0;
    if ($package['hotel_name']) $totalAmount += ($package['pricePerNight'] * $days );
    if ($package['tour_title']) $totalAmount += ($package['tour_price'] * $quantity);
    
    // Add service fee (5%)
    $totalAmount *= 1.05;
    
    // Dynamically build base URL
    $host = $_SERVER['HTTP_HOST'];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $base_url = "$protocol://$host/Final_Project";

    // Set booking session data for Chapa payment
    $_SESSION['chapa_booking_data'] = [
        'tx_ref' => 'TX' . time() . '_' . rand(1000, 9999),
        'package_id' => $packageID,
        'accountID' => $_SESSION['accountID'],
        'start_date' => $startDate,
        'end_date' => $endDate,
        'quantity' => $quantity,
        'email' => $userData['email'],
        'name' => $userData['touristName'],
        'total_amount' => $totalAmount
    ];

    // Output Chapa payment form
    echo '<form id="chapaPaymentForm" method="POST" action="https://api.chapa.co/v1/hosted/pay">';
    echo '<input type="hidden" name="public_key" value="CHAPUBK_TEST-04SpgjsBgOmcq07WpFKbiC8tZU69DmgB">';
    echo '<input type="hidden" name="tx_ref" value="' . $_SESSION['chapa_booking_data']['tx_ref'] . '">';
    echo '<input type="hidden" name="amount" value="' . $totalAmount . '">';
    echo '<input type="hidden" name="currency" value="ETB">';
    echo '<input type="hidden" name="email" value="' . $userData['email'] . '">';
    echo '<input type="hidden" name="first_name" value="' . $userData['touristName'] . '">';
    echo '<input type="hidden" name="last_name" value=" ">';
    echo '<input type="hidden" name="title" value="Explore Ethiopia Package">';
    echo '<input type="hidden" name="description" value="Booking package for hotel, tour, and ride">';
    echo '<input type="hidden" name="callback_url" value="' . $base_url . '/payment_success.php">';
    echo '<input type="hidden" name="return_url" value="' . $base_url . '/payment_success.php?id=' . $packageID . '">';
    echo '</form>';
    echo '<script>document.getElementById("chapaPaymentForm").submit();</script>';
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($package['title']); ?> - Explore Ethiopia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --dark-color: #212529;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
        }
        
        .package-header {
            background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), url('<?php echo htmlspecialchars($package['image_path']); ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            margin-bottom: 50px;
            position: relative;
        }
        
        .package-header::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 20px;
            background: linear-gradient(to bottom, rgba(0,0,0,0.3), transparent);
        }
        
        .service-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        
        .service-card-header {
            background-color: var(--primary-color);
            color: white;
            padding: 15px;
            text-align: center;
        }
        
        .service-card-body {
            padding: 25px;
            text-align: center;
        }
        
        .service-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
            color: var(--primary-color);
        }
        
        .included-badge {
            background-color: var(--success-color);
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 50px;
        }
        
        .not-included-badge {
            background-color: var(--secondary-color);
            font-size: 0.8rem;
            padding: 5px 10px;
            border-radius: 50px;
        }
        
        .price-highlight {
            font-size: 1.3rem;
            font-weight: bold;
            color: var(--primary-color);
            margin-top: 15px;
        }
        
        .booking-form-container {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            padding: 30px;
            position: sticky;
            top: 20px;
        }
        
        .form-control, .form-select {
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        
        .price-summary-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .price-summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .total-price {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-color);
        }
        
        .payment-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-btn:hover {
            background-color: #0b5ed7;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
        }
        
        .payment-btn img {
            height: 30px;
            margin-right: 10px;
        }
        
        .section-title {
            position: relative;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .empty-state {
            padding: 20px;
            text-align: center;
            color: var(--secondary-color);
        }
        
        .empty-state i {
            font-size: 2.5rem;
            opacity: 0.5;
            margin-bottom: 10px;
        }
        
        footer {
            background: linear-gradient(to right, #1a1a1a, #2d2d2d);
            color: white;
            padding: 50px 0 20px;
        }
        
        footer a {
            color: #ddd;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        footer a:hover {
            color: white;
        }
        
        .social-icons a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background-color: rgba(255,255,255,0.1);
            border-radius: 50%;
            margin-right: 10px;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            background-color: var(--primary-color);
            transform: translateY(-3px);
        }
        
        @media (max-width: 991.98px) {
            .package-header {
                padding: 80px 0;
            }
            
            .booking-form-container {
                margin-top: 40px;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>

    <!-- Package Header -->
    <div class="package-header text-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-4"><?php echo htmlspecialchars($package['title']); ?></h1>
                    <div class="d-flex align-items-center justify-content-center">
                        <i class="bi bi-geo-alt-fill me-2"></i>
                        <p class="lead mb-0"><?php echo htmlspecialchars($package['location']); ?></p>
                    </div>
                    <div class="mt-4">
                        <span class="badge bg-light text-dark fs-6 px-3 py-2 mb-2 me-2">
                            <i class="bi bi-clock me-1"></i> <?php echo htmlspecialchars($package['duration']); ?>
                        </span>
                        <span class="badge bg-light text-dark fs-6 px-3 py-2 mb-2">
                            <i class="bi bi-people me-1"></i> Group Package
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container mb-5">
        <div class="row">
            <!-- Package Overview -->
            <div class="col-lg-8">
                <div class="card border-0 mb-4">
                    <div class="card-body p-4">
                        <h2 class="fw-bold section-title">Package Overview</h2>
                        <p class="lead mb-4"><?php echo htmlspecialchars($package['description']); ?></p>
                        
                        <h4 class="fw-bold mb-4">What's Included</h4>
                        <div class="row g-4">
                            <!-- Hotel Card -->
                            <div class="col-md-4">
                                <div class="service-card">
                                    <div class="service-card-header">
                                        <i class="bi bi-building"></i>
                                        <h5 class="mb-0">Accommodation</h5>
                                    </div>
                                    <div class="service-card-body">
                                        <?php if ($package['hotel_name']): ?>
                                            <span class="badge included-badge mb-3">Included</span>
                                            <h6 class="fw-bold"><?php echo htmlspecialchars($package['hotel_name']); ?></h6>
                                            <p class="text-muted mb-3"><?php echo htmlspecialchars($package['hotel_desc']); ?></p>
                                            <div class="price-highlight">
                                                <?php echo number_format($package['pricePerNight']); ?> ETB/night
                                            </div>
                                        <?php else: ?>
                                            <div class="empty-state">
                                                <i class="bi bi-building"></i>
                                                <p>No accommodation included</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tour Card -->
                            <div class="col-md-4">
                                <div class="service-card">
                                    <div class="service-card-header bg-info">
                                        <i class="bi bi-signpost-split"></i>
                                        <h5 class="mb-0">Guided Tour</h5>
                                    </div>
                                    <div class="service-card-body">
                                        <?php if ($package['tour_title']): ?>
                                            <span class="badge included-badge mb-3">Included</span>
                                            <h6 class="fw-bold"><?php echo htmlspecialchars($package['tour_title']); ?></h6>
                                            <p class="text-muted mb-3"><?php echo utf8_encode($package['tour_desc']); ?></p>
                                            <div class="price-highlight">
                                                <?php echo number_format($package['tour_price']); ?> ETB
                                            </div>
                                        <?php else: ?>
                                            <div class="empty-state">
                                                <i class="bi bi-signpost-split"></i>
                                                <p>No tour included</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Transportation Card -->
                            <div class="col-md-4">
                                <div class="service-card">
                                    <div class="service-card-header bg-warning text-dark">
                                        <i class="bi bi-car-front"></i>
                                        <h5 class="mb-0">Transportation</h5>
                                    </div>
                                    <div class="service-card-body">
                                        <?php if ($package['ride_name']): ?>
                                            <span class="badge included-badge mb-3">Included</span>
                                            <h6 class="fw-bold"><?php echo htmlspecialchars($package['ride_name']); ?></h6>
                                            <p class="text-muted mb-3"><?php echo htmlspecialchars($package['ride_desc']); ?></p>
                                            <div class="price-highlight">
                                                1,000 ETB/day
                                            </div>
                                        <?php else: ?>
                                            <div class="empty-state">
                                                <i class="bi bi-car-front"></i>
                                                <p>No transportation included</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Package Gallery (placeholder - you can add actual images) -->
                <div class="card border-0 mb-4">
                    <div class="card-body p-4">
                        <h4 class="fw-bold section-title">Gallery</h4>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <img src="<?php echo htmlspecialchars($package['image_path']); ?>" class="img-fluid rounded" alt="Package Image">
                            </div>
                            <?php if ($package['hotel_image']): ?>
                            <div class="col-md-4">
                                <img src="<?php echo htmlspecialchars($package['hotel_image']); ?>" class="img-fluid rounded" alt="Hotel Image">
                            </div>
                            <?php endif; ?>
                            <?php if ($package['tour_image']): ?>
                            <div class="col-md-4">
                                <img src="<?php echo htmlspecialchars($package['tour_image']); ?>" class="img-fluid rounded" alt="Tour Image">
                            </div>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </div>
            
<!-- Booking Form -->
<div class="col-lg-4">
    <div class="booking-form-container">
        <h3 class="fw-bold mb-4 section-title">Book This Package</h3>
        <form method="POST" action="">
            <div class="mb-4">
                <label for="start_date" class="form-label fw-semibold">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-4">
                <label for="end_date" class="form-label fw-semibold">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required min="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="mb-4">
                <label for="quantity" class="form-label fw-semibold">Number of People</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="1" value="1" required>
            </div>
            
            <!-- Price Summary -->
            <div class="card price-summary-card mb-4">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">Price Breakdown</h5>
                    
                    <?php if ($package['hotel_name']): ?>
                    <div class="price-summary-item">
                        <span>Accommodation (<?php echo htmlspecialchars($package['hotel_name']); ?>):</span>
                        <span class="hotel-price-display">0 ETB</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($package['tour_title']): ?>
                    <div class="price-summary-item">
                        <span>Tour (<?php echo htmlspecialchars($package['tour_title']); ?>):</span>
                        <span class="tour-price-display">0 ETB</span>
                    </div>
                    <?php endif; ?>

                    
                    <div class="price-summary-item">
                        <span>Subtotal:</span>
                        <span class="subtotal-display">0 ETB</span>
                    </div>
                    <div class="price-summary-item">
                        <span>Service Fee (5%):</span>
                        <span class="service-fee-display">0 ETB</span>
                    </div>
                    <hr>
                    <div class="price-summary-item">
                        <span class="fw-bold">Total:</span>
                        <span class="total-price fw-bold">0 ETB</span>
                    </div>
                </div>
            </div>
            
            <!-- Payment Method -->
            <div class="mb-4">
                <h5 class="fw-bold mb-3">Payment Method</h5>
                <button type="submit" name="book_package" class="payment-btn">
                    <img src="public/images/chapa.png" alt="Chapa">
                    <span>Pay with Chapa</span>
                </button>
                <p class="text-muted small mt-2 text-center">
                    <i class="bi bi-lock-fill"></i> Secure payment processing
                </p>
            </div>
            
            <!-- Cancellation Policy -->
            <div class="alert alert-info">
                <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Cancellation Policy</h6>
                <p class="small mb-0">Free cancellation up to 48 hours before the trip. No refunds after this period.</p>
            </div>
        </form>
    </div>
</div>
</div>
</div>

<script>
    // Update price summary when dates or quantity change
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const quantityInput = document.getElementById('quantity');
        
        function updatePriceSummary() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);
            const quantity = parseInt(quantityInput.value) || 1;
            
            if (startDate && endDate && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                const days = Math.max(1, Math.round((endDate - startDate) / (1000 * 60 * 60 * 24)));
                
                // Calculate components
                let hotelCost = 0;
                let tourCost = 0;
                let rideCost = 0;
                
                <?php if ($package['hotel_name']): ?>
                    hotelCost = <?= $package['pricePerNight'] ?> * days ;
                    document.querySelector('.hotel-price-display').textContent = hotelCost.toLocaleString('en-US') + ' ETB';
                <?php endif; ?>
                
                <?php if ($package['tour_title']): ?>
                    tourCost = <?= $package['tour_price'] ?> * quantity;
                    document.querySelector('.tour-price-display').textContent = tourCost.toLocaleString('en-US') + ' ETB';
                <?php endif; ?>
                
                <?php if ($package['ride_name']): ?>
                    rideCost = 1000 * days * quantity; // Assuming 1000 ETB per day per person for transportation
                    document.querySelector('.ride-price-display').textContent = rideCost.toLocaleString('en-US') + ' ETB';
                <?php endif; ?>
                
                const subtotal = hotelCost + tourCost + rideCost;
                const serviceFee = subtotal * 0.05;
                const total = subtotal + serviceFee;
                
                // Update displayed prices
                document.querySelector('.subtotal-display').textContent = subtotal.toLocaleString('en-US') + ' ETB';
                document.querySelector('.service-fee-display').textContent = serviceFee.toLocaleString('en-US') + ' ETB';
                document.querySelector('.total-price').textContent = total.toLocaleString('en-US') + ' ETB';
            }
        }
        
        // Add event listeners
        startDateInput.addEventListener('change', updatePriceSummary);
        endDateInput.addEventListener('change', updatePriceSummary);
        quantityInput.addEventListener('input', updatePriceSummary);
        
        // Initialize price summary
        updatePriceSummary();
    });
</script>

    <!-- Footer -->
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
        // Update price summary when dates or quantity change
        document.addEventListener('DOMContentLoaded', function() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const quantityInput = document.getElementById('quantity');
            
            function updatePriceSummary() {
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(endDateInput.value);
                const quantity = parseInt(quantityInput.value) || 1;
                
                if (startDate && endDate && !isNaN(startDate.getTime()) && !isNaN(endDate.getTime())) {
                    const days = Math.max(1, Math.round((endDate - startDate) / (1000 * 60 * 60 * 24)));
                    
                    // Calculate subtotal
                    let subtotal = 0;
                    <?php if ($package['hotel_name']): ?>
                        subtotal += <?= $package['pricePerNight'] ?> * days ;
                    <?php endif; ?>
                    <?php if ($package['tour_title']): ?>
                        subtotal += <?= $package['tour_price'] ?> * quantity;
                    <?php endif; ?>
                    <?php if ($package['ride_name']): ?>
                        
                    <?php endif; ?>
                    
                    const serviceFee = subtotal * 0.05;
                    const total = subtotal + serviceFee;
                    
                    // Update displayed prices
                    document.querySelector('.subtotal-display').textContent = subtotal.toLocaleString('en-US') + ' ETB';
                    document.querySelector('.service-fee-display').textContent = serviceFee.toLocaleString('en-US') + ' ETB';
                    document.querySelector('.total-price').textContent = total.toLocaleString('en-US') + ' ETB';
                }
            }
            
            // Add event listeners
            startDateInput.addEventListener('change', updatePriceSummary);
            endDateInput.addEventListener('change', updatePriceSummary);
            quantityInput.addEventListener('input', updatePriceSummary);
            
            // Initialize price summary
            updatePriceSummary();
        });
    </script>
</body>
</html>
<?php
$connection->close();
?>