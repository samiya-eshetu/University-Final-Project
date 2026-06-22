<?php
session_start();

// Handle form submission
$success = $error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // In a real application, you would send the email here
        // For now, we'll just simulate success
        $success = "Thank you for your message! We'll get back to you within 24 hours.";
        
        // Clear form
        $name = $email = $subject = $message = '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Us - Explore Ethiopia</title>
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
        
        .contact-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .contact-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('pattern.png') center/cover;
            opacity: 0.1;
        }
        
        .contact-section {
            padding: 80px 0;
            background-color: var(--secondary-color);
        }
        
        .contact-card {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
            height: 100%;
        }
        
        .contact-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 2rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .map-container {
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
        }
        
        .contact-method {
            padding: 1.5rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            height: 100%;
        }
        
        .contact-method:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        
        .contact-method-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>



<?php
require_once 'header.php'; 
?>

    >

<!-- Page Header -->
<div class="contact-header animate__animated animate__fadeIn">
    <div class="container position-relative">
        <h1 class="display-4 fw-bold mb-3">Contact Us</h1>
        <p class="lead mb-4">We'd love to hear from you! Reach out with questions, feedback, or partnership opportunities.</p>
    </div>
</div>

<!-- Contact Section -->
<div class="contact-section">
    <div class="container">
        <?php if ($success): ?>
            <div class="alert alert-success animate__animated animate__fadeIn mb-5 text-center">
                <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
            </div>
        <?php elseif ($error): ?>
            <div class="alert alert-danger animate__animated animate__shakeX mb-5 text-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="row g-4 mb-5">
            <div class="col-lg-6">
                <div class="contact-card animate__animated animate__fadeInLeft">
                    <h3 class="mb-4"><i class="bi bi-envelope me-2 text-primary"></i> Send Us a Message</h3>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Your Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($name ?? '') ?>" required>
                                    <div class="invalid-feedback">Please provide your name.</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email ?? '') ?>" required>
                                    <div class="invalid-feedback">Please provide a valid email.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select class="form-select" id="subject" name="subject" required>
                                <option value="" disabled selected>Select a subject</option>
                                <option value="General Inquiry" <?= ($subject ?? '') === 'General Inquiry' ? 'selected' : '' ?>>General Inquiry</option>
                                <option value="Booking Help" <?= ($subject ?? '') === 'Booking Help' ? 'selected' : '' ?>>Booking Help</option>
                                <option value="Payment Issue" <?= ($subject ?? '') === 'Payment Issue' ? 'selected' : '' ?>>Payment Issue</option>
                                <option value="Business Partnership" <?= ($subject ?? '') === 'Business Partnership' ? 'selected' : '' ?>>Business Partnership</option>
                                <option value="Feedback" <?= ($subject ?? '') === 'Feedback' ? 'selected' : '' ?>>Feedback</option>
                                <option value="Other" <?= ($subject ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
                            </select>
                            <div class="invalid-feedback">Please select a subject.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" required><?= htmlspecialchars($message ?? '') ?></textarea>
                            <div class="invalid-feedback">Please enter your message.</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3">
                                <i class="bi bi-send-check me-2"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="contact-card animate__animated animate__fadeInRight">
                    <h3 class="mb-4"><i class="bi bi-map me-2 text-primary"></i> Our Location</h3>
                    
                    <!-- In the contact form section, replace the existing map iframe with this: -->
                    <div class="map-container mb-4">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3940.715848845638!2d38.75730831478618!3d9.022924293544255!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x164b852528b8f94d%3A0xb932691d903f7674!2sUnity%20University%20Gerji!5e0!3m2!1sen!2sus!4v1620000000000!5m2!1sen!2sus" 
                                width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>

  
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="contact-method">
                                <div class="contact-method-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <h5>Address</h5>
                                <p>Bole Road, Addis Ababa, Ethiopia</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contact-method">
                                <div class="contact-method-icon">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <h5>Phone</h5>
                                <p>+251 123 456 789</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contact-method">
                                <div class="contact-method-icon">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <h5>Email</h5>
                                <p>contact@AllInOne.com</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="contact-method">
                                <div class="contact-method-icon">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <h5>Hours</h5>
                                <p>24/7 Customer Support</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Additional Contact Methods -->
        <div class="text-center mb-5">
            <h2 class="mb-4">Other Ways to Reach Us</h2>
            <p class="lead">Choose the contact method that works best for you</p>
        </div>
        
        <div class="row g-4">
            <div class="col-md-4">
                <div class="contact-card text-center animate__animated animate__fadeInUp">
                    <div class="contact-icon">
                        <i class="bi bi-headset"></i>
                    </div>
                    <h4 class="mb-3">Live Chat</h4>
                    <p class="mb-4">Get instant help from our support team through our live chat system.</p>
                    <a href="#" class="btn btn-outline-primary">Start Chat</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-card text-center animate__animated animate__fadeInUp" style="animation-delay: 0.2s;">
                    <div class="contact-icon">
                        <i class="bi bi-telephone-outbound"></i>
                    </div>
                    <h4 class="mb-3">Call Us</h4>
                    <p class="mb-4">Speak directly with a customer service representative.</p>
                    <a href="tel:+251123456789" class="btn btn-outline-primary">Call Now</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="contact-card text-center animate__animated animate__fadeInUp" style="animation-delay: 0.4s;">
                    <div class="contact-icon">
                        <i class="bi bi-people"></i>
                    </div>
                    <h4 class="mb-3">Visit Us</h4>
                    <p class="mb-4">Come to our office for face-to-face assistance with your travel needs.</p>
                    <a href="#map" class="btn btn-outline-primary">Get Directions</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Teaser -->
<div class="bg-light py-5">
    <div class="container text-center">
        <h2 class="mb-4">Need Help Quickly?</h2>
        <p class="lead mb-4">Check our FAQs for instant answers to common questions.</p>
        <a href="faq.php" class="btn btn-primary px-4 py-2">
            <i class="bi bi-question-circle me-2"></i> Visit FAQ Page
        </a>
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
<script>
    // Form validation
    (function() {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.prototype.slice.call(forms).forEach(function(form) {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>