<?php
$servername = "sql207.infinityfree.com";
$username = "if0_42226342";
$password = "VqIUuAIZ38T0f8";
$dbname = "if0_42226342_allonone";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();
// Redirect to login if not authenticated
if (!isset($_SESSION['accountID'])) {
    header("Location: user sign up.php");
    exit;
}

// Fetch featured hotels
$featuredHotels = [];
$hotelQuery = "SELECT * FROM hotels WHERE status='approved' AND featured='Yes' ORDER BY averageRating DESC LIMIT 4";
$hotelResult = $conn->query($hotelQuery);
if ($hotelResult->num_rows > 0) {
    while($row = $hotelResult->fetch_assoc()) {
        $featuredHotels[] = $row;
    }
}

// Fetch special offers (hotels with exclusive offers)
$specialOffers = [];
$offerQuery = "SELECT * FROM hotels WHERE exclusive_offer IS NOT NULL AND status='approved' LIMIT 6";
$offerResult = $conn->query($offerQuery);
if ($offerResult->num_rows > 0) {
    while($row = $offerResult->fetch_assoc()) {
        $specialOffers[] = $row;
    }
}

// Fetch ride services
$rideServices = [];
$rideQuery = "SELECT * FROM rides WHERE status='approved'";
$rideResult = $conn->query($rideQuery);
if ($rideResult->num_rows > 0) {
    while($row = $rideResult->fetch_assoc()) {
        $rideServices[] = $row;
    }
}



// Fetch tour packages
$tourPackages = [];
$tourQuery = "SELECT * FROM tour_packages ORDER BY rating DESC LIMIT 3";
$tourResult = $conn->query($tourQuery);
if ($tourResult->num_rows > 0) {
    while($row = $tourResult->fetch_assoc()) {
        $tourPackages[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Ethiopia - Your Complete Travel Solution</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('public/images/food1.jpg');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 100px 0;
            margin-bottom: 50px;
        }
        
        .service-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 12px;
            overflow: hidden;
            height: 100%;
        }
        
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .offer-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #dc3545;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .ride-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #0d6efd;
        }
        
        .feature-icon {
            font-size: 2.5rem;
            color: #0d6efd;
            margin-bottom: 15px;
        }
        
        .search-tabs .nav-link {
            color: #495057;
            font-weight: 500;
        }
        
        .search-tabs .nav-link.active {
            color: #0d6efd;
            font-weight: 600;
        }
    </style>
</head>
<body>


<?php
require_once 'header.php'; 
?>

    
    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Discover the Wonders of Ethiopia</h1>
            <p class="lead mb-4">Book hotels, rides, and tours all in one place for your perfect Ethiopian adventure</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="hotel booking.php" class="btn btn-primary btn-lg px-4">Find Hotels</a>
                <a href="travel agents.php" class="btn btn-outline-light btn-lg px-4">Book a Tours</a>
            </div>
        </div>
    </section>

    <!-- Search Section -->
    <section class="container mb-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4">
                <ul class="nav nav-tabs search-tabs mb-4" id="searchTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="hotels-tab" data-bs-toggle="tab" data-bs-target="#hotels" type="button">Hotels</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="rides-tab" data-bs-toggle="tab" data-bs-target="#rides" type="button">Rides</button>
                    </li>

                </ul>
                
                <div class="tab-content" id="searchTabsContent">
                    <div class="tab-pane fade show active" id="hotels" role="tabpanel">
                        <form action="hotel booking.php" method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="destination" class="form-label">Destination</label>
                                <input type="text" class="form-control" id="destination" name="location" placeholder="Where are you going?">
                            </div>
                            <div class="col-md-3">
                                <label for="checkin" class="form-label">Check-in</label>
                                <input type="date" class="form-control" id="checkin" name="checkin">
                            </div>
                            <div class="col-md-3">
                                <label for="checkout" class="form-label">Check-out</label>
                                <input type="date" class="form-control" id="checkout" name="checkout">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Search Hotels</button>
                            </div>
                        </form>
                    </div>
                    
                    <div class="tab-pane fade" id="rides" role="tabpanel">
                        <form action="ride booking.php" method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label for="pickup" class="form-label">Pick-up Location</label>
                                <input type="text" class="form-control" id="pickup" name="pickup" placeholder="Where from?">
                            </div>
                            <div class="col-md-6">
                                <label for="dropoff" class="form-label">Drop-off Location</label>
                                <input type="text" class="form-control" id="dropoff" name="dropoff" placeholder="Where to?">
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Find Rides</button>
                            </div>
                        </form>
                    </div>
                    

                </div>
            </div>
        </div>
    </section>

    <!-- Special Offers -->
    <section class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Special Offers</h2>
            <a href="hotel booking.php" class="btn btn-outline-primary">View All Hotels</a>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($specialOffers)): ?>
                <?php foreach ($specialOffers as $hotel): ?>
                <div class="col-md-4">
                    <div class="card service-card h-100 border-0 position-relative">
                        <span class="offer-badge">Special Offer</span>
                        <img src="<?php echo htmlspecialchars($hotel['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small"><i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($hotel['location']); ?></span>
                                <span class="text-success small fw-bold">Available</span>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($hotel['description']); ?></p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-primary">ETB <?php echo number_format($hotel['pricePerNight'], 2); ?></span>
                                    <span class="text-muted small d-block">per night</span>
                                </div>
                                <a href="hotel_details.php?id=<?php echo $hotel['hotelID']; ?>" class="btn btn-sm btn-outline-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">No special offers available at the moment. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Featured Hotels -->
    <section class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Featured Hotels</h2>
            <a href="hotel booking.php" class="btn btn-outline-primary">View All Hotels</a>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($featuredHotels)): ?>
                <?php foreach ($featuredHotels as $hotel): ?>
                <div class="col-md-6">
                    <div class="card service-card h-100 border-0">
                        <div class="row g-0 h-100">
                            <div class="col-md-5">
                                <img src="<?php echo htmlspecialchars($hotel['image_path']); ?>" class="img-fluid rounded-start h-100" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="object-fit: cover;">
                            </div>
                            <div class="col-md-7">
                                <div class="card-body d-flex flex-column h-100">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                                        <div class="text-warning">
                                            <?php
                                            $rating = round($hotel['averageRating']);
                                            for ($i = 1; $i <= 5; $i++) {
                                                if ($i <= $rating) {
                                                    echo '<i class="bi bi-star-fill"></i>';
                                                } else {
                                                    echo '<i class="bi bi-star"></i>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="card-text text-muted"><i class="bi bi-geo-alt-fill text-primary me-2"></i> <?php echo htmlspecialchars($hotel['location']); ?></p>
                                    <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($hotel['description'], 0, 100)); ?>...</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-primary">ETB <?php echo number_format($hotel['pricePerNight'], 2); ?></span>
                                            <span class="text-muted small d-block">per night</span>
                                        </div>
                                        <a href="hotel_details.php?id=<?php echo $hotel['hotelID']; ?>" class="btn btn-primary">Book Now</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">No featured hotels available at the moment. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ride Services -->
    <section class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Ride Services</h2>
            <a href="ride booking.php" class="btn btn-outline-primary">View All Options</a>
        </div>
        
        <div class="row g-4 text-center">
            <?php if (!empty($rideServices)): ?>
                <?php foreach ($rideServices as $ride): ?>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="card service-card h-100 border-0 p-3">
                        <div class="ride-icon bg-light bg-opacity-10 rounded-circle p-3 mb-3">
                            <i class="bi bi-car-front-fill"></i>
                        </div>
                        <h6><?php echo htmlspecialchars($ride['provider_name']); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($ride['description']); ?></p>
                        <a href="ride_details.php?id=<?php echo $ride['rideID']; ?>" class="btn btn-sm btn-outline-primary mt-auto">Book</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">No ride services available at the moment. Check back later!</p>
                </div>
            <?php endif; ?>
            
            <!-- More options card -->
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card service-card h-100 border-0 p-3">
                    <div class="ride-icon bg-light bg-opacity-10 rounded-circle p-3 mb-3">
                        <i class="bi bi-three-dots"></i>
                    </div>
                    <h6>More</h6>
                    <p class="text-muted small">Other options</p>
                    <a href="ride booking.php" class="btn btn-sm btn-outline-primary mt-auto">View All</a>
                </div>
            </div>
        </div>
    </section>



    <!-- Tour Packages -->
    <section class="container mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold">Featured Tour Packages</h2>
            <a href="travel agents.php" class="btn btn-outline-primary">View All Tours</a>
        </div>
        
        <div class="row g-4">
            <?php if (!empty($tourPackages)): ?>
                <?php foreach ($tourPackages as $tour): ?>
                <div class="col-md-4">
                    <div class="card service-card h-100 border-0">
                        <img src="<?php echo htmlspecialchars($tour['image_path']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tour['title']); ?>" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary">Tour</span>
                                <span class="text-muted small"><i class="bi bi-calendar me-1"></i> <?php echo htmlspecialchars($tour['duration']); ?></span>
                            </div>
                            <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars(substr($tour['description'], 0, 100)); ?>...</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="fw-bold text-primary">ETB <?php echo number_format($tour['price'], 2); ?></span>
                                </div>
                                <a href="confirm_hotel_page.php?type=tour&package_id=<?= $tour['packageID'] ?>" class="btn btn-sm btn-primary">Book Now</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-4">
                    <p class="text-muted">No tour packages available at the moment. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>


<!-- Modern Business Owners Section -->
<section class="container my-5 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card service-card1   h-100 border-0 overflow-hidden">
                <div class="row g-0">
                    <!-- Visual Column -->
                    <div class="col-md-5 bg-primary-gradient position-relative">
                        <div class="h-100 d-flex flex-column justify-content-center p-4 text-white">
                            <div class="feature-icon1 mb-4">
                                <i class="bi bi-buildings fs-1"></i>
                            </div>
                            <h2 class="h3 mb-3">Grow Your Business</h2>
                            <p class="mb-0">Join Ethiopia's fastest-growing travel marketplace</p>
                            <div class="decorative-shape position-absolute bottom-0 end-0">
                                <svg width="100" height="100" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="100" cy="100" r="100" fill="white" fill-opacity="0.1"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content Column -->
                    <div class="col-md-7">
                        <div class="card-body p-4 p-lg-5">
                            <div class="badge bg-primary-soft text-primary mb-3">FOR BUSINESSES</div>
                            <h3 class="card-title mb-3">Connect With Travelers</h3>
                            <p class="card-text text-muted mb-4">List your hotels, tours, or transportation services to reach thousands of potential customers every month.</p>
                            
                            <div class="d-grid gap-3">
                                <a href="bussiness_owner/business_profile.php" class="btn btn-primary btn-hover-shadow py-3">
                                    <i class="bi bi-plus-circle me-2"></i>Register Your Business
                                </a>
                                
                                <?php if(isset($_SESSION['accountID']) && in_array($_SESSION['role'], ['hotel_owner', 'tour_owner', 'ride_owner','admin'])): ?>
                                    <a href="bussiness_owner/dashboard.php" class="btn btn-outline-primary py-3">
                                        <i class="bi bi-speedometer2 me-2"></i>Go to Dashboard
                                    </a>
                                <?php endif; ?>
                            </div>
                           
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    /* Custom Styles */
    .service-card {
        border-radius: 12px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        box-shadow: 0 10px 20px rgba(0,0,0,0.08);
    }
    

    
    .bg-primary-gradient {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    }
    
    .btn-hover-shadow {
        transition: all 0.3s ease;
    }
    
    .btn-hover-shadow:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3);
    }
    
    .bg-primary-soft {
        background-color: rgba(13, 110, 253, 0.1);
    }
    
    .decorative-shape {
        opacity: 0.5;
    }
    
    @media (max-width: 767.98px) {
        .service-card .col-md-5 {
            padding: 2rem 1rem;
        }
        
        .feature-icon1 {
            margin-bottom: 1.5rem !important;
        }
    }
</style>






    <!-- Why Choose Us -->
    <section class="bg-light py-5 mb-5">
        <div class="container">
            <h2 class="text-center fw-bold mb-5">Why Choose Explore Ethiopia?</h2>
            
            <div class="row g-4">
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Trusted Services</h5>
                    <p class="text-muted">All our partners are carefully vetted to ensure quality and reliability for your Ethiopian adventure.</p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="bi bi-currency-exchange"></i>
                    </div>
                    <h5>Best Prices</h5>
                    <p class="text-muted">We guarantee the best prices for hotels, rides, and tours across Ethiopia or we'll match the difference.</p>
                </div>
                
                <div class="col-md-4 text-center">
                    <div class="feature-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h5>24/7 Support</h5>
                    <p class="text-muted">Our customer service team is available around the clock to assist you with any needs during your travels.</p>
                </div>
            </div>
        </div>
    </section>




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
        // Set current date as default for check-in
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('checkin').value = today;
            document.getElementById('tourDate').value = today;
            
            // Set check-out to 1 day after check-in
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('checkout').value = tomorrow.toISOString().split('T')[0];
        });
    </script>
</body>
</html>