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

$hotelID = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM hotels WHERE hotelID = ? AND status = 'approved'";
$stmt = $connection->prepare($sql);
$stmt->bind_param("i", $hotelID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<h3 class='text-danger text-center mt-5'>Hotel not found or not approved.</h3>";
    exit;
}








$hotel = $result->fetch_assoc();

$reviewSql = "
    SELECT r.*, t.fullName
    FROM reviews r
    JOIN tourists t ON r.touristID = t.touristID
    WHERE r.serviceType = 'hotel' AND r.serviceID = ?
    ORDER BY r.createdAt DESC
    LIMIT 5
";
$reviewStmt = $connection->prepare($reviewSql);
$reviewStmt->bind_param("i", $hotelID);
$reviewStmt->execute();
$reviewsResult = $reviewStmt->get_result();

$stmt->close();
$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($hotel["name"]) ?> | Explore Ethiopia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fa;
            --accent-color: #2e59d9;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fb;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .hotel-header {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .hotel-image {
            height: 500px;
            object-fit: cover;
            width: 100%;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .hotel-title {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.7));
            color: white;
            padding: 2rem;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        
        .info-card {
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            border: none;
        }
        
        .info-card:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .amenities-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
        }
        
        .amenity-item {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        
        .amenity-icon {
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .price-tag {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .rating-badge {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            padding: 0.9rem 1rem;
            border-radius: 20px;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
        }
        
        .booking-card {
            position: sticky;
            top: 20px;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .carousel-control-prev, .carousel-control-next {
            width: 5%;
            background-color: rgba(0,0,0,0.2);
            border-radius: 50%;
            height: 50px;
            width: 50px;
            top: 50%;
            transform: translateY(-50%);
        }
        
        @media (max-width: 768px) {
            .hotel-image {
                height: 300px;
            }
            
            .amenities-list {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            }
        }
    </style>
</head>
<body>
 

<?php
require_once 'header.php'; 
?>

    

    <!-- Hotel Header -->
    <div class="container mt-4">
        <div class="hotel-header animate__animated animate__fadeIn">
            <img src="<?= htmlspecialchars($hotel["image_path"]) ?>" class="hotel-image" alt="<?= htmlspecialchars($hotel["name"]) ?>">
            <div class="hotel-title">
                <h1 class="display-5 fw-bold mb-2"><?= htmlspecialchars($hotel["name"]) ?></h1>
                <div class="d-flex align-items-center">
                    <span class="rating-badge me-3">
                        <i class="bi bi-star-fill me-1"></i> <?= number_format($hotel["averageRating"], 1) ?>
                    </span>
                    <span class="text-white"><i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($hotel["location"]) ?></span>
                </div>
            </div>
        </div>
        
        <div class="row">
            <!-- Hotel Details -->
            <div class="col-lg-8">
                <div class="info-card p-4 mb-4">
                    <h3 class="fw-bold mb-4">About This Hotel</h3>
                    <p class="lead"><?= htmlspecialchars($hotel["description"]) ?></p>
                    
                    <div class="mt-4">
                        <h4 class="fw-bold mb-3">Amenities</h4>
                        <div class="amenities-list">
                            <div class="amenity-item">
                                <i class="bi bi-wifi amenity-icon"></i>
                                <span>Free WiFi</span>
                            </div>
                            <div class="amenity-item">
                                <i class="bi bi-cup-hot amenity-icon"></i>
                                <span>Breakfast</span>
                            </div>
                            <div class="amenity-item">
                                <i class="bi bi-car-front amenity-icon"></i>
                                <span>Parking</span>
                            </div>
                            <div class="amenity-item">
                                <i class="bi bi-water amenity-icon"></i>
                                <span>Pool</span>
                            </div>
                            <div class="amenity-item">
                                <i class="bi bi-egg-fried amenity-icon"></i>
                                <span>Restaurant</span>
                            </div>
                            <div class="amenity-item">
                                <i class="bi bi-airplane amenity-icon"></i>
                                <span>Airport Shuttle</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Reviews Section -->
                <div class="info-card p-4">
                    <h3 class="fw-bold mb-4">Guest Reviews</h3>
                    <div class="d-flex align-items-center mb-3">
                        <span class="rating-badge me-3">
                            <i class="bi bi-star-fill me-1"></i> <?= number_format($hotel["averageRating"], 1) ?>
                        </span>
                        <span class="text-muted">Average reviews</span>
                    </div>
                    
                    <!-- Sample Review -->
                    <div class="review-item mb-4 pb-3 border-bottom">
                            <?php if ($reviewsResult->num_rows > 0): ?>
                                <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                                    <div class="review-item mb-4 pb-3 border-bottom">
                                        <div class="d-flex justify-content-between mb-2">
                                            <h5 class="mb-0"><?= htmlspecialchars($review['content']) ?></h5>
                                            <div class="text-warning">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi <?= $i <= $review['rating'] ? 'bi-star-fill' : 'bi-star' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-2">By <?= htmlspecialchars($review['fullName']) ?> • <?= date('F Y', strtotime($review['createdAt'])) ?></p>

                                        <?php if (!empty($review['reply'])): ?>
                                            <div class="mt-2 ms-4 p-2 border-start border-3 border-primary bg-light rounded">
                                                <strong class="text-primary">Owner Reply:</strong>
                                                <p class="mb-0"><?= htmlspecialchars($review['reply']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-muted">No reviews yet. Be the first to review this hotel!</div>
                            <?php endif; ?>
                    </div>
                    

                </div>
            </div>
            
            <!-- Booking Form -->
            <div class="col-lg-4">
                <div class="booking-card p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="fw-bold mb-0">Book Your Stay</h3>
                        <span class="price-tag"><?= number_format($hotel["pricePerNight"]) ?> ETB</span>
                    </div>
                    <small class="text-muted d-block mb-3">per night (excluding taxes)</small>
                    
                    <form action="confirm_hotel_page.php" method="GET">
                        <input type="hidden" name="hotel_id" value="<?= $hotel['hotelID'] ?>">
                        <input type="hidden" name="type" value="hotel">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Check-in Date</label>
                            <input type="date" class="form-control" name="start_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Check-out Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Guests</label>
                            <select class="form-select" name="guests" required>
                                <option value="1">1 Guest</option>
                                <option value="2" selected>2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4 Guests</option>
                                <option value="5">5+ Guests</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                            Book Now <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </form>

                    
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex align-items-center mb-2">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Free cancellation up to 24 hours</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-check-circle-fill text-success me-2"></i>
                            <span>Best price guarantee</span>
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
                        <li class="mb-2"><i class="bi bi-envelope me-2"></i> contact@exploreethiopia.com</li>
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
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Designed with <i class="bi bi-heart-fill text-danger"></i> in Ethiopia</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Set minimum end date based on start date
        $(document).ready(function() {
            $('input[name="start_date"]').change(function() {
                const startDate = new Date(this.value);
                const endDateInput = $('input[name="end_date"]');
                
                // Set minimum end date to start date
                endDateInput.attr('min', this.value);
                
                // Clear end date if it's now before start date
                if (endDateInput.val() && new Date(endDateInput.val()) < startDate) {
                    endDateInput.val('');
                }
            });
            
            // Initialize date inputs
            const today = new Date().toISOString().split('T')[0];
            $('input[name="start_date"]').attr('min', today);
        });
    </script>
</body>
</html>