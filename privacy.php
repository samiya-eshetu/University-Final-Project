<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Privacy Policy - Explore Ethiopia</title>
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
        
        .privacy-header {
            background: linear-gradient(135deg, var(--primary-color), var(--accent-color));
            color: white;
            padding: 80px 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .privacy-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('pattern.png') center/cover;
            opacity: 0.1;
        }
        
        .privacy-section {
            padding: 80px 0;
            background-color: var(--secondary-color);
        }
        
        .privacy-card {
            background: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .privacy-card h2 {
            color: var(--primary-color);
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .privacy-card h3 {
            color: var(--dark-color);
            margin-top: 1.5rem;
        }
        
        .update-card {
            background-color: rgba(78, 115, 223, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .contact-privacy {
            background-color: white;
            border-radius: 0.5rem;
            padding: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        
        .contact-privacy-icon {
            font-size: 2.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>



<?php
require_once 'header.php'; 
?>

    

<!-- Page Header -->
<div class="privacy-header animate__animated animate__fadeIn">
    <div class="container position-relative">
        <h1 class="display-4 fw-bold mb-3">Privacy Policy</h1>
        <p class="lead mb-4">Your privacy is important to us. Learn how we collect, use, and protect your information.</p>
    </div>
</div>

<!-- Privacy Section -->
<div class="privacy-section">
    <div class="container">
        <div class="update-card animate__animated animate__fadeIn">
            <h4><i class="bi bi-info-circle-fill me-2"></i> Last Updated: July 2025</h4>
            <p class="mb-0">This policy describes how we handle your personal information when you use our services.</p>
        </div>
        
        <div class="row">
            <div class="col-lg-8">
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>1. Information We Collect</h2>
                    
                    <h3>1.1 Personal Information</h3>
                    <p>When you create an account or make a booking, we collect:</p>
                    <ul>
                        <li>Name, email address, and phone number</li>
                        <li>Payment information (processed securely through Chapa)</li>
                        <li>Travel preferences and booking history</li>
                        <li>Device and browser information</li>
                    </ul>
                    
                    <h3>1.2 Usage Data</h3>
                    <p>We automatically collect information about how you interact with our website:</p>
                    <ul>
                        <li>IP address and location data</li>
                        <li>Pages visited and time spent</li>
                        <li>Browser type and version</li>
                        <li>Cookies and similar technologies</li>
                    </ul>
                </div>
                
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>2. How We Use Your Information</h2>
                    
                    <h3>2.1 Service Provision</h3>
                    <p>We use your information to:</p>
                    <ul>
                        <li>Process bookings and payments</li>
                        <li>Provide customer support</li>
                        <li>Communicate booking confirmations and updates</li>
                        <li>Personalize your experience</li>
                    </ul>
                    
                    <h3>2.2 Improvements & Marketing</h3>
                    <p>With your consent, we may use your information to:</p>
                    <ul>
                        <li>Improve our services and website</li>
                        <li>Send promotional offers (you can opt-out anytime)</li>
                        <li>Conduct analytics and research</li>
                    </ul>
                </div>
                
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>3. Data Sharing & Disclosure</h2>
                    
                    <h3>3.1 Service Providers</h3>
                    <p>We share necessary information with:</p>
                    <ul>
                        <li>Hotels, tour operators, and transportation providers to fulfill bookings</li>
                        <li>Payment processors like Chapa to complete transactions</li>
                        <li>IT and analytics service providers</li>
                    </ul>
                    
                    <h3>3.2 Legal Requirements</h3>
                    <p>We may disclose information when required by law or to:</p>
                    <ul>
                        <li>Comply with legal processes</li>
                        <li>Protect our rights or property</li>
                        <li>Prevent fraud or security issues</li>
                    </ul>
                </div>
                
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>4. Data Security</h2>
                    <p>We implement appropriate technical and organizational measures to protect your personal information:</p>
                    <ul>
                        <li>SSL encryption for all data transmissions</li>
                        <li>Regular security audits</li>
                        <li>Limited access to personal data</li>
                        <li>Secure payment processing</li>
                    </ul>
                    <p>While we strive to protect your data, no method of transmission over the internet is 100% secure.</p>
                </div>
                
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>5. Your Rights & Choices</h2>
                    
                    <h3>5.1 Access & Control</h3>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access, update, or delete your personal information</li>
                        <li>Object to or restrict certain processing</li>
                        <li>Receive your data in a portable format</li>
                        <li>Withdraw consent for marketing communications</li>
                    </ul>
                    
                    <h3>5.2 Cookies</h3>
                    <p>You can manage cookies through your browser settings. However, disabling cookies may affect website functionality.</p>
                </div>
                
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>6. International Transfers</h2>
                    <p>Your information may be transferred to and processed in countries other than Ethiopia, where data protection laws may differ. We ensure appropriate safeguards are in place for such transfers.</p>
                </div>
                
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>7. Children's Privacy</h2>
                    <p>Our services are not directed to children under 13. We do not knowingly collect personal information from children without parental consent.</p>
                </div>
                
                <div class="privacy-card animate__animated animate__fadeInLeft">
                    <h2>8. Changes to This Policy</h2>
                    <p>We may update this policy periodically. We'll notify you of significant changes through email or website notices. Your continued use of our services constitutes acceptance of the updated policy.</p>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="contact-privacy animate__animated animate__fadeInRight">
                    <div class="contact-privacy-icon">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h3>Questions About Privacy?</h3>
                    <p class="mb-4">Contact our Data Protection Officer if you have any questions about this policy or your personal data.</p>
                    <a href="contact.php" class="btn btn-primary w-100 mb-3">
                        <i class="bi bi-envelope me-2"></i> Email Us
                    </a>
                    <a href="tel:+251123456789" class="btn btn-outline-primary w-100">
                        <i class="bi bi-telephone me-2"></i> +251 123 456 789
                    </a>
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
</body>
</html>