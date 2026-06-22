<?php
session_start();
if (!isset($_SESSION['accountID'])) {
    header("Location: user sign up.php");
    exit;
}

$connection = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Booking - Explore Ethiopia</title>
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
        
        .search-container {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .search-bar {
            background: white;
            border-radius: 50px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            padding: 0.5rem 1.5rem;
        }
        
        .search-bar:hover {
            box-shadow: 0 6px 25px rgba(0,0,0,0.12);
        }
        
        .hotel-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .hotel-img {
            height: 220px;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        
        .hotel-card:hover .hotel-img {
            transform: scale(1.03);
        }
        
        .rating-badge {
            background: linear-gradient(to right, var(--primary-color), var(--accent-color));
            color: white;
            padding: 0.35rem 0.7rem;
            border-radius: 20px;
            font-size: 0.85rem;
        }
        
        .price-tag {
            color: var(--primary-color);
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255,255,255,0.9);
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            z-index: 10;
        }
        
        .wishlist-btn:hover {
            background: white;
            transform: scale(1.1);
        }
        
        .wishlist-btn.active i {
            color: red;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .pagination .page-link {
            color: var(primary-color);
        }
        
        .no-results {
            text-align: center;
            padding: 4rem 0;
        }
        
        .no-results img {
            max-height: 150px;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .search-bar {
                border-radius: 12px;
                padding: 0.75rem;
            }
            
            .hotel-img {
                height: 180px;
            }
        }
    </style>
</head>
<body>



<?php
require_once 'header.php'; 
?>



    <!-- Search Section -->
    <div class="search-container">
        <div class="container">
            <form method="GET" class="search-bar mx-auto" style="max-width: 900px;">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-search"></i></span>
                            <input type="text" name="Hotel" class="form-control border-0 shadow-none" 
                                   placeholder="Hotel name..." value="<?= htmlspecialchars($_GET['Hotel'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-transparent border-0"><i class="bi bi-geo-alt"></i></span>
                            <input type="text" name="location" class="form-control border-0 shadow-none" 
                                   placeholder="Location..." value="<?= htmlspecialchars($_GET['location'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="price_range" class="form-select border-0 shadow-none">
                            <option value="">Price range</option>
                            <option value="below1100" <?= ($_GET['price_range'] ?? '') == 'below5000' ? 'selected' : '' ?>>Under 1,100 ETB</option>
                            <option value="1100-3000" <?= ($_GET['price_range'] ?? '') == '5000-10000' ? 'selected' : '' ?>>1,000 - 3,000 ETB</option>
                            <option value="above3000" <?= ($_GET['price_range'] ?? '') == 'above10000' ? 'selected' : '' ?>>Over 3,000 ETB</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">
                            <i class="bi bi-search me-1"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container my-4">
        <div class="row">
            <!-- Hotel Listings -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Available Hotels</h4>
                    <div class="d-flex align-items-center">

                    </div>
                </div>

                <?php
                // Build WHERE clause based on search filters
                $where = "WHERE status = 'approved'";
                
                if (!empty($_GET['location'])) {
                    $location = $connection->real_escape_string($_GET['location']);
                    $where .= " AND location LIKE '%$location%'";
                }

                if (!empty($_GET['Hotel'])) {
                    $Hotel = $connection->real_escape_string($_GET['Hotel']);
                    $where .= " AND name LIKE '%$Hotel%'";
                }

                if (!empty($_GET['price_range'])) {
                    if ($_GET['price_range'] == 'below1100') {
                        $where .= " AND pricePerNight < 1100";
                    } elseif ($_GET['price_range'] == '1100-3000') {
                        $where .= " AND pricePerNight BETWEEN 1100 AND 3000";
                    } elseif ($_GET['price_range'] == 'above3000') {
                        $where .= " AND pricePerNight > 3000";
                    }
                }

                // Pagination setup
                $limit = 9;
                $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                $offset = ($page - 1) * $limit;

                // Get total count for pagination
                $countQuery = "SELECT COUNT(*) as total FROM hotels $where";
                $countResult = $connection->query($countQuery);
                $totalRows = $countResult->fetch_assoc()['total'];
                $totalPages = ceil($totalRows / $limit);

                // Get hotels for current page
                $sql = "SELECT * FROM hotels $where LIMIT $limit OFFSET $offset";
                $result = $connection->query($sql);

                if ($result->num_rows > 0) {
                    echo '<div class="row g-4">';
                    while ($row = $result->fetch_assoc()) {
                        $hotelID = $row["hotelID"];
                        echo '
                        <div class="col-md-6 col-lg-4 animate__animated animate__fadeIn">
                            <div class="card hotel-card h-100" >
                                <div class="position-relative">
                                    <img src="' . htmlspecialchars($row["image_path"]) . '" class="card-img-top hotel-img" alt="' . htmlspecialchars($row["name"]) . '">
                                    <button class="wishlist-btn" data-hotel-id="' . $hotelID . '">
                                        <i class="bi bi-heart fs-5 text-muted"></i>
                                    </button>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">' . htmlspecialchars($row["name"]) . '</h5>
                                    <p class="card-text text-muted mb-2">
                                        <i class="bi bi-geo-alt"></i> ' . htmlspecialchars($row["location"]) . '
                                    </p>
                                    <p class="card-text text-truncate">' . htmlspecialchars($row["description"]) . '</p>
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <span class="rating-badge">
                                            <i class="bi bi-star-fill"></i> ' . number_format($row["averageRating"], 1) . '
                                        </span>
                                        <div class="text-end">
                                            <p class="price-tag mb-0">' . number_format($row["pricePerNight"]) . ' ETB</p>
                                            <small class="text-muted">per night</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent border-top-0">
                                    <a href="hotel_details.php?id=' . $hotelID . '" class="btn btn-primary w-100">
                                        Book Now
                                    </a>
                                </div>
                            </div>
                        </div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="no-results">
                            <h4 class="fw-bold">No hotels found</h4>
                            <p class="text-muted">Try adjusting your search filters</p>
                            <a href="hotel booking.php" class="btn btn-primary mt-3">Reset Filters</a>
                          </div>';
                }
                ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <nav aria-label="Page navigation" class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        </li>
                        
                        <?php 
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        
                        if ($start > 1) {
                            echo '<li class="page-item"><a class="page-link" href="?'.http_build_query(array_merge($_GET, ['page' => 1])).'">1</a></li>';
                            if ($start > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        
                        for ($i = $start; $i <= $end; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor;
                        
                        if ($end < $totalPages) {
                            if ($end < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="?'.http_build_query(array_merge($_GET, ['page' => $totalPages])).'">'.$totalPages.'</a></li>';
                        }
                        ?>
                        
                        <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Wishlist functionality
        $(document).on('click', '.wishlist-btn', function(e) {
            e.preventDefault();
            const btn = $(this);
            const icon = btn.find('i');
            
            // Toggle heart icon
            if (icon.hasClass('bi-heart')) {
                icon.removeClass('bi-heart text-muted').addClass('bi-heart-fill text-danger');
                btn.addClass('active');
            } else {
                icon.removeClass('bi-heart-fill text-danger').addClass('bi-heart text-muted');
                btn.removeClass('active');
            }
            
            // Here you would typically make an AJAX call to save to wishlist
            // const hotelId = btn.data('hotel-id');
            // $.post('add_to_wishlist.php', {hotel_id: hotelId}, function(response) {
            //     if(response.success) {
            //         btn.toggleClass('active');
            //     }
            // });
        });
        
        // Animate cards on scroll
        $(window).scroll(function() {
            $('.animate__animated').each(function() {
                const position = $(this).offset().top;
                const scroll = $(window).scrollTop();
                const windowHeight = $(window).height();
                
                if (scroll + windowHeight > position + 100) {
                    $(this).addClass('animate__fadeIn');
                }
            });
        }).scroll(); // Trigger on load
        
        // Smooth scroll for pagination
        $('.pagination a').click(function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            window.scrollTo({top: 0, behavior: 'smooth'});
            setTimeout(() => { window.location = url; }, 500);
        });
    </script>
</body>
</html>
<?php
$connection->close();
?>