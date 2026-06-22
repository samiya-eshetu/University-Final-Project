<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FAQs - Explore Ethiopia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --dark-color: #2c3e50;
        }
        
        .faq-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .faq-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('pattern.png') center/cover;
            opacity: 0.1;
        }
        
        .faq-section {
            padding: 80px 0;
            background-color: var(--secondary-color);
        }
        
        .accordion-button {
            font-weight: 600;
            padding: 1.25rem 1.5rem;
            box-shadow: none !important;
        }
        
        .accordion-button:not(.collapsed) {
            background-color: rgba(78, 115, 223, 0.1);
            color: var(--primary-color);
        }
        
        .accordion-button::after {
            background-size: 1.25rem;
        }
        
        .accordion-item {
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 0.5rem !important;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .faq-category {
            margin-bottom: 3rem;
        }
        
        .faq-category h2 {
            color: var(--primary-color);
            position: relative;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
        }
        
        .faq-category h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background-color: var(--primary-color);
        }
        
        .search-box {
            max-width: 600px;
            margin: 0 auto 3rem;
        }
        
        .contact-card {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .popular-questions {
            background-color: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }
        
        .popular-list {
            list-style-type: none;
            padding: 0;
        }
        
        .popular-list li {
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .popular-list li:last-child {
            border-bottom: none;
        }
        
        .popular-list a {
            color: var(--dark-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .popular-list a:hover {
            color: var(--primary-color);
        }
        
        .help-section {
            background-color: white;
            padding: 4rem 0;
        }
    </style>
</head>
<body>


<?php
require_once 'header.php'; 
?>

    

<!-- Page Header -->
<div class="faq-header animate__animated animate__fadeIn">
    <div class="container position-relative">
        <h1 class="display-4 fw-bold mb-3">Frequently Asked Questions</h1>
        <p class="lead mb-4">Find answers to all your questions about booking, payments, and using Explore Ethiopia.</p>

    </div>
</div>

<!-- FAQ Section -->
<div class="faq-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Booking Questions -->
                <div class="faq-category animate__animated animate__fadeInUp">
                    <h2><i class="bi bi-calendar-check me-2"></i> Booking Questions</h2>
                    
                    <div class="accordion" id="bookingAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="booking1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBooking1">
                                    How do I book a travel package?
                                </button>
                            </h2>
                            <div id="collapseBooking1" class="accordion-collapse collapse show" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    <p>Booking a travel package is simple:</p>
                                    <ol>
                                        <li>Visit the <a href="travel agents.php">Travel Agents</a> page</li>
                                        <li>Browse available packages and select one that interests you</li>
                                        <li>Choose your travel dates and number of travelers</li>
                                        <li>Review the package details and click "Book Now"</li>
                                        <li>Complete the payment process using Chapa</li>
                                        <li>You'll receive a confirmation email with all booking details</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="booking2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBooking2">
                                    Can I book multiple services together?
                                </button>
                            </h2>
                            <div id="collapseBooking2" class="accordion-collapse collapse" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    Yes! Our platform allows you to book hotels, rides, and tours together in a single package. 
                                    When viewing a package, you'll see all included services and can customize them as needed.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="booking3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseBooking3">
                                    How far in advance should I book?
                                </button>
                            </h2>
                            <div id="collapseBooking3" class="accordion-collapse collapse" data-bs-parent="#bookingAccordion">
                                <div class="accordion-body">
                                    <p>We recommend booking as early as possible, especially during peak seasons (December-January and July-August).</p>
                                    <ul>
                                        <li>Hotels: At least 2 weeks in advance</li>
                                        <li>Tours: At least 1 month in advance for popular destinations</li>
                                        <li>Rides: Can often be booked with just 24 hours notice</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Payment Questions -->
                <div class="faq-category animate__animated animate__fadeInUp">
                    <h2><i class="bi bi-credit-card me-2"></i> Payment Questions</h2>
                    
                    <div class="accordion" id="paymentAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="payment1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayment1">
                                    What payment methods are supported?
                                </button>
                            </h2>
                            <div id="collapsePayment1" class="accordion-collapse collapse show" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body">
                                    <p>Currently, we support payments through:</p>
                                    <ul>
                                        <li><strong>Chapa</strong> - Our primary payment gateway supporting all major Ethiopian banks</li>
                                        <li><strong>Telebirr</strong> - Coming soon</li>
                                        <li><strong>CBE Birr</strong> - Coming soon</li>
                                    </ul>
                                    <p>We're working to add more local payment options in the future.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="payment2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayment2">
                                    Is my payment information secure?
                                </button>
                            </h2>
                            <div id="collapsePayment2" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body">
                                    Absolutely. We use industry-standard encryption and security measures to protect your payment information. 
                                    All transactions are processed through Chapa's secure payment gateway, and we never store your full payment details on our servers.
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="payment3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayment3">
                                    Do you offer payment plans?
                                </button>
                            </h2>
                            <div id="collapsePayment3" class="accordion-collapse collapse" data-bs-parent="#paymentAccordion">
                                <div class="accordion-body">
                                    Currently, we require full payment at the time of booking. However, for certain high-value packages, 
                                    we may offer payment plans on a case-by-case basis. Please <a href="contact.php">contact us</a> if you'd like to discuss this option.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Cancellation & Changes -->
                <div class="faq-category animate__animated animate__fadeInUp">
                    <h2><i class="bi bi-arrow-counterclockwise me-2"></i> Cancellation & Changes</h2>
                    
                    <div class="accordion" id="cancellationAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="cancel1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCancel1">
                                    Can I cancel or modify my booking?
                                </button>
                            </h2>
                            <div id="collapseCancel1" class="accordion-collapse collapse show" data-bs-parent="#cancellationAccordion">
                                <div class="accordion-body">
                                    <p>Our cancellation policy depends on the service type and timing:</p>
                                    <ul>
                                        <li><strong>Hotels:</strong> Free cancellation up to 7 days before check-in (some hotels may have stricter policies)</li>
                                        <li><strong>Tours:</strong> Free cancellation up to 14 days before departure (50% refund within 14 days)</li>
                                        <li><strong>Rides:</strong> Free cancellation up to 24 hours before pickup</li>
                                    </ul>
                                    <p>To request changes or cancellations, go to your <a href="history.php">booking history</a> and follow the instructions.</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="cancel2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCancel2">
                                    How long does it take to get a refund?
                                </button>
                            </h2>
                            <div id="collapseCancel2" class="accordion-collapse collapse" data-bs-parent="#cancellationAccordion">
                                <div class="accordion-body">
                                    Refunds are typically processed within 5-7 business days after approval. The time it takes for the funds to appear in your account depends on your bank:
                                    <ul>
                                        <li>Chapa payments: 3-5 business days</li>
                                        <li>Bank transfers: 5-7 business days</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Questions -->
                <div class="faq-category animate__animated animate__fadeInUp">
                    <h2><i class="bi bi-person-circle me-2"></i> Account Questions</h2>
                    
                    <div class="accordion" id="accountAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="account1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccount1">
                                    Do I need an account to book?
                                </button>
                            </h2>
                            <div id="collapseAccount1" class="accordion-collapse collapse show" data-bs-parent="#accountAccordion">
                                <div class="accordion-body">
                                    Yes, creating an account is required to make bookings. Your account allows us to:
                                    <ul>
                                        <li>Securely store your booking history</li>
                                        <li>Provide personalized recommendations</li>
                                        <li>Make future bookings faster</li>
                                        <li>Offer special discounts to returning customers</li>
                                    </ul>
                                    <p>Registration only takes a minute and makes managing your travel much easier!</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="account2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAccount2">
                                    How do I reset my password?
                                </button>
                            </h2>
                            <div id="collapseAccount2" class="accordion-collapse collapse" data-bs-parent="#accountAccordion">
                                <div class="accordion-body">
                                    <p>To reset your password:</p>
                                    <ol>
                                        <li>Go to the <a href="user login.php">login page</a></li>
                                        <li>Click "Forgot Password"</li>
                                        <li>Enter the email address associated with your account</li>
                                        <li>Check your email for a password reset link</li>
                                        <li>Follow the instructions to create a new password</li>
                                    </ol>
                                    <p>If you don't receive the email within 5 minutes, please check your spam folder or <a href="contact.php">contact support</a>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <!-- Popular Questions -->
                <div class="popular-questions mb-4 animate__animated animate__fadeIn">
                    <h3 class="mb-4"><i class="bi bi-fire text-danger me-2"></i> Popular Questions</h3>
                    <ul class="popular-list">
                        <li><a href="#collapseBooking1">How do I book a travel package?</a></li>
                        <li><a href="#collapsePayment1">What payment methods are supported?</a></li>
                        <li><a href="#collapseCancel1">Can I cancel or modify my booking?</a></li>
                        <li><a href="#collapseAccount1">Do I need an account to book?</a></li>
                        <li><a href="#collapsePayment2">Is my payment information secure?</a></li>
                        <li><a href="#collapseBooking3">How far in advance should I book?</a></li>
                    </ul>
                </div>
                
                <!-- Contact Card -->
                <div class="contact-card mb-4 animate__animated animate__fadeIn">
                    <div class="contact-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h3>Still need help?</h3>
                    <p class="mb-4">Our customer support team is available 24/7 to assist you.</p>
                    <a href="contact.php" class="btn btn-primary w-100">Contact Support</a>
                </div>
                
                <!-- Business Owners Card -->
                <div class="contact-card animate__animated animate__fadeIn">
                    <div class="contact-icon">
                        <i class="bi bi-buildings"></i>
                    </div>
                    <h3>Business Owners</h3>
                    <p class="mb-4">Want to list your business on our platform?</p>
                    <a href="bussiness_owner/business_profile.php" class="btn btn-outline-primary w-100">Register Your Business</a>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Help Section -->
<div class="help-section">
    <div class="container text-center">
        <h2 class="mb-4">Didn't find what you're looking for?</h2>
        <p class="lead mb-5">We're here to help with any questions you have about using Explore Ethiopia.</p>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-envelope fs-1 text-primary mb-3"></i>
                    <h4>Email Us</h4>
                    <p>support@AllInOne.com</p>
                    <p>Typically responds within 1 hour</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-telephone fs-1 text-primary mb-3"></i>
                    <h4>Call Us</h4>
                    <p>+251 123 456 789</p>
                    <p>Available 24/7</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-4">
                    <i class="bi bi-chat-dots fs-1 text-primary mb-3"></i>
                    <h4>Live Chat</h4>
                    <p>Click the chat icon below</p>
                    <p>Instant connection with our team</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <h5 class="fw-bold mb-3">Explore Ethiopia</h5>
                <p>Your gateway to seamless travel experiences in Ethiopia. Book hotels, rides, and tours all in one place.</p>
                <div class="social-icons mt-3">
                    <a href="#" class="text-white me-3"><i class="bi bi-facebook fs-5"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-twitter fs-5"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-instagram fs-5"></i></a>
                    <a href="#" class="text-white me-3"><i class="bi bi-telegram fs-5"></i></a>
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
            <div class="col-md-6 text-center text-md-end">
                <p class="mb-0">Designed with <i class="bi bi-heart-fill text-danger"></i> in Ethiopia</p>
            </div>
        </div>
    </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Smooth scrolling for anchor links
    $(document).on('click', 'a[href^="#"]', function(e) {
        e.preventDefault();
        $('html, body').animate({
            scrollTop: $($(this).attr('href')).offset().top - 100
        }, 500);
    });
    
    // Search functionality
    $('.search-box button').click(function() {
        const query = $('.search-box input').val().toLowerCase();
        if (query.length > 2) {
            $('.accordion-item').each(function() {
                const text = $(this).text().toLowerCase();
                if (text.includes(query)) {
                    $(this).show();
                    $(this).find('.accordion-collapse').addClass('show');
                } else {
                    $(this).hide();
                }
            });
        }
    });
    
    // Clear search when input is empty
    $('.search-box input').on('input', function() {
        if ($(this).val() === '') {
            $('.accordion-item').show();
        }
    });
</script>
</body>
</html>