<?php
session_start();
$connection = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}


// Redirect to login if not authenticated
if (!isset($_SESSION['accountID'])) {
    header("Location: user sign up.php");
    exit;
}


// Fetch all-in-one packages with calculated prices
$query = "SELECT 
            p.packageID, 
            p.title, 
            p.location, 
            p.description, 
            p.duration,
            p.price,
            p.image_path,
            h.name AS hotel_name,
            h.pricePerNight AS hotel_price,
            t.price AS tour_price
          FROM all_in_one_packages p
          LEFT JOIN hotels h ON p.hotelID = h.hotelID
          LEFT JOIN tours t ON p.tourID = t.tourID
          WHERE p.status = 'approved'";

$result = $connection->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Ethiopia - All-in-One Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <style>
        .card {
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card img {
            height: 200px;
            object-fit: cover;
        }
        .card-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .card-title {
            font-weight: 600;
        }
        .price {
            font-size: 1.2rem;
            font-weight: bold;
            color: #0d6efd;
        }
        .btn-booking {
            margin-top: auto;
        }
        .package-includes {
            font-size: 0.9rem;
            color: #6c757d;
        }
    </style>
</head>
<body>



<?php
require_once 'header.php'; 
?>

    

<div class="container mt-5">
    <h2 class="fw-bold text-center mb-4">All-in-One Travel Packages</h2>
    <p class="text-center mb-5">Complete travel solutions including hotels, tours, and transportation</p>
    
    <div class="row g-4">
        <?php if ($result && $result->num_rows > 0): ?>
            <?php while($package = $result->fetch_assoc()): ?>
                <?php 
                    // Calculate duration in days
                    $days = 1; // default to 1 day if dates not available
                    if (!empty($package['startDate']) && !empty($package['endDate'])) {
                        $start = new DateTime($package['startDate']);
                        $end = new DateTime($package['endDate']);
                        $days = $start->diff($end)->days;
                        $days = max(1, $days); // ensure at least 1 day
                    }
                    
                    // Calculate costs with null checks
                    $hotelCost = isset($package['hotel_price']) ? $package['hotel_price'] * $days : 0;
                    $tourCost = isset($package['tour_price']) ? $package['tour_price'] : 0;
                    $rideCost = 1000 * $days; // 1000 ETB per day for ride
                    $totalPrice = $hotelCost + $tourCost + $rideCost;
                    
                    // Build includes list
                    $includes = [];
                    if (!empty($package['hotel_name'])) $includes[] = 'Hotel';
                    if (!empty($package['tour_price'])) $includes[] = 'Tour';
                    $includes[] = 'Transportation';
                ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card h-100">
                        <img src="<?php echo htmlspecialchars($package['image_path'] ?? 'public/images/default-package.jpg'); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['title']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($package['title']); ?></h5>
                            <p class="text-muted"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($package['location']); ?></p>
                            <p class="card-text"><?php echo htmlspecialchars($package['description']); ?></p>
                            <p class="package-includes mb-2">
                                <small>Includes: <?php echo implode(', ', $includes); ?></small>
                            </p>
                            <div class="mt-auto">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><i class="bi bi-clock"></i> <?php echo htmlspecialchars($package['duration'] ?? "$days days"); ?></span>
                                    <span class="price"><?php echo number_format($package['price']); ?> ETB</span>
                                </div>
                                <div class="d-grid">
                                    <a href="package_details.php?id=<?php echo $package['packageID']; ?>" class="btn btn-primary btn-booking">View Package</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    No packages available at the moment. Please check back later.
                </div>
            </div>
        <?php endif; ?>
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

</body>
</html>
<?php 
if ($connection) {
    $connection->close();
}
?>