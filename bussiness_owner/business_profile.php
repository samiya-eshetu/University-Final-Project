<?php
session_start();
if (!isset($_SESSION['accountID'])) {
    header("Location: ../index.php");
    exit;
}
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'];
    $fullName = $_POST['fullName'];
    $businessName = $_POST['businessName'];
    $phoneNumber = $_POST['phoneNumber'];
    $email = $_POST['email'];
    $location = $_POST['location'];
    $ownerID = $_SESSION['accountID'];
    $status = 'pending';
    $date_applied = date('Y-m-d');

    $document_path = '';
    if (!empty($_FILES['business_document']['name'])) {
        $upload_dir = '../public/documents/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = time() . "_" . basename($_FILES['business_document']['name']);
        $document_path = $upload_dir . $filename;
        move_uploaded_file($_FILES['business_document']['tmp_name'], $document_path);
    }

    $table = $type . "_owners";
    $stmt = $conn->prepare("INSERT INTO $table (ownerID, fullName, businessName, phoneNumber, business_document, date_applied, location, email, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssssss", $ownerID, $fullName, $businessName, $phoneNumber, $document_path, $date_applied, $location, $email, $status);

if ($stmt->execute()) {
    // Only proceed to insert into `tours` if it's a tour type
    if ($type === 'tour') {
        $provider_name = $_POST['provider_name'];
        $tour_price = $_POST['price'];
        $tour_duration = $_POST['duration'];
        $tour_description = $_POST['description'];
        $rating = rand(30, 50) / 10; // Random rating between 3.0 and 5.0
        $availability = 'available';




        $tour_image_path = '';
        if (!empty($_FILES['tour_image']['name'])) {
            $upload_dir = '../public/images/tours/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $image_name = time() . "_" . basename($_FILES['tour_image']['name']);
            $tour_image_path = $upload_dir . $image_name;
            move_uploaded_file($_FILES['tour_image']['tmp_name'], $tour_image_path);
        }

        // Ensure this user is now in tour_owners (should be, after above insert)
        $checkOwner = $conn->query("SELECT * FROM tour_owners WHERE ownerID = $ownerID");
        if ($checkOwner->num_rows > 0) {
            $tourStmt = $conn->prepare("INSERT INTO tours (ownerID, provider_name, location, price, duration, description, availability, averageRating, image_path, status)
                                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $tourStmt->bind_param("issdssssss", $ownerID, $provider_name, $location, $tour_price, $tour_duration, $tour_description, $availability, $rating, $tour_image_path, $status);

            if ($tourStmt->execute()) {
                $success = "🎉 Tour registration submitted! Awaiting admin approval.";
            } else {
                $error = "Tour insert failed: " . $tourStmt->error;
            }

            $tourStmt->close();
        } else {
            $error = "Tour owner not found after registration.";
        }
    } else {
        $success = "🎉 Registration submitted! Awaiting admin approval.";
    }
} else {
    $error = "Something went wrong: " . $stmt->error;
}


    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Business Registration</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
        }
        
        body {
            background-color: #f5f7fb;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        

        
        .main-content {
            margin-top: -140px;
            padding: 12rem;
        }
        
        .registration-card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.3s;
            background: white;
        }
        

        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            font-weight: 600;
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        .business-type-icon {
            font-size: 1.5rem;
            margin-right: 0.5rem;
            color: var(--primary-color);
        }
        
        .file-upload {
            position: relative;
            overflow: hidden;
        }
        
        .file-upload-input {
            position: absolute;
            font-size: 100px;
            opacity: 0;
            right: 0;
            top: 0;
        }
        
        .file-upload-label {
            display: block;
            padding: 0.375rem 0.75rem;
            background-color: var(--secondary-color);
            border: 1px dashed #d1d3e2;
            border-radius: 0.35rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .file-upload-label:hover {
            background-color: #e9ecef;
        }
        
        .file-name {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
        }
        
        .step {
            text-align: center;
            position: relative;
            flex: 1;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            background-color: #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: bold;
            color: #6c757d;
        }
        
        .step.active .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .step.completed .step-number {
            background-color: #1cc88a;
            color: white;
        }
        
        .step-title {
            font-size: 0.875rem;
            color: #6c757d;
        }
        
        .step.active .step-title {
            color: var(--primary-color);
            font-weight: bold;
        }
        
        .step.completed .step-title {
            color: #1cc88a;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="card registration-card animate__animated animate__fadeIn">
                        <div class="card-body p-5">
                            <!-- Step Indicator -->
                            <div class="step-indicator">
                                <div class="step active">
                                    <div class="step-number">1</div>
                                    <div class="step-title">Business Information</div>
                                </div>
                                <div class="step">
                                    <div class="step-number">2</div>
                                    <div class="step-title">Verification</div>
                                </div>
                                <div class="step">
                                    <div class="step-number">3</div>
                                    <div class="step-title">Complete</div>
                                </div>
                            </div>
                            
                            <h2 class="mb-4 text-center text-primary fw-bold">Register Your Business</h2>
                            <p class="text-center text-muted mb-5">Fill in your business details to get started. Our team will review your application within 24-48 hours.</p>

                            <?php if ($success): ?>
                                <div class="alert alert-success animate__animated animate__fadeIn">
                                    <i class="bi bi-check-circle-fill me-2"></i> <?= $success ?>
                                    <div class="mt-3 text-center">
                                        <a href="../index.php" class="btn btn-primary">Back to Home</a>
                                    </div>
                                </div>
                            <?php elseif ($error): ?>
                                <div class="alert alert-danger animate__animated animate__shakeX">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= $error ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!$success): ?>
                            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                                <div class="mb-4">
                                    <h5 class="mb-3"><i class="bi bi-building business-type-icon"></i> Business Type</h5>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="form-check card p-3 h-100" style="cursor: pointer;">
                                                <input class="form-check-input" type="radio" name="type" id="hotel" value="hotel" required>
                                                <label class="form-check-label d-block" for="hotel">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-house-door fs-3 text-primary me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Hotel Owner</h6>
                                                            <small class="text-muted">Accommodation services</small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check card p-3 h-100" style="cursor: pointer;">
                                                <input class="form-check-input" type="radio" name="type" id="ride" value="ride">
                                                <label class="form-check-label d-block" for="ride">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-car-front fs-3 text-primary me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Ride Provider</h6>
                                                            <small class="text-muted">Transportation services</small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-check card p-3 h-100" style="cursor: pointer;">
                                                <input class="form-check-input" type="radio" name="type" id="tour" value="tour">
                                                <label class="form-check-label d-block" for="tour">
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-signpost-split fs-3 text-primary me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Tour Operator</h6>
                                                            <small class="text-muted">Travel experiences</small>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="invalid-feedback">Please select a business type.</div>
                                </div>


                                <div class="row g-3 mb-4">
                                    <div class="col-md-6">
                                        <h5 class="mb-3"><i class="bi bi-person business-type-icon"></i> Personal Information</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="fullName" class="form-control" required>
                                            <div class="invalid-feedback">Please provide your full name.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                            <input type="tel" name="phoneNumber" class="form-control" required>
                                            <div class="invalid-feedback">Please provide a valid phone number.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required>
                                            <div class="invalid-feedback">Please provide a valid email address.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <h5 class="mb-3"><i class="bi bi-shop business-type-icon"></i> Business Information</h5>
                                        <div class="mb-3">
                                            <label class="form-label">Business Name <span class="text-danger">*</span></label>
                                            <input type="text" name="businessName" class="form-control" required>
                                            <div class="invalid-feedback">Please provide your business name.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Location <span class="text-danger">*</span></label>
                                            <input type="text" name="location" class="form-control" required>
                                            <div class="invalid-feedback">Please provide your business location.</div>
                                        </div>



                                        <div class="mb-3">
                                            <label class="form-label">Business Document <span class="text-danger">*</span></label>
                                            <div class="file-upload">
                                                <label class="file-upload-label" for="business_document">
                                                    <i class="bi bi-cloud-arrow-up fs-4"></i>
                                                    <div>Click to upload document</div>
                                                    <small class="text-muted">(PDF, DOC, JPG up to 5MB)</small>
                                                </label>
                                                <input type="file" name="business_document" id="business_document" class="file-upload-input" required>
                                                <div id="file-name" class="file-name"></div>
                                            </div>





            <!-- TOUR-SPECIFIC FIELDS (shown only when 'tour' is selected) -->
            <div id="tourFields" class="mt-4 d-none">
                <h5 class="mb-3 text-primary"><i class="bi bi-globe"></i> Tour Company Info</h5>

                <div class="mb-3">
                    <label class="form-label">Tour Agency Name <span class="text-danger">*</span></label>
                    <input type="text" name="provider_name" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Price Estimate (ETB) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Duration (e.g. 5 days / 3 nights) <span class="text-danger">*</span></label>
                    <input type="text" name="duration" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tour Image (optional)</label>
                    <input type="file" name="tour_image" class="form-control" required>
                </div>
            </div>







                                            <div class="invalid-feedback">Please upload your business document.</div>
                                            <small class="text-muted">Upload your business license, registration certificate, or other official document.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 py-2">
                                        <i class="bi bi-send-check me-2"></i> Submit Registration
                                    </button>
                                </div>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Show tour fields only when 'tour' type is selected
    $('input[name="type"]').on('change', function () {
        if (this.value === 'tour') {
            $('#tourFields').removeClass('d-none');
            $('#tourFields').find('input, textarea').attr('required', true);
        } else {
            $('#tourFields').addClass('d-none');
            $('#tourFields').find('input, textarea').removeAttr('required');
        }
    });

    // File upload display
    $(document).ready(function () {
        $('input[type="file"]').change(function (e) {
            const fileName = e.target.files[0].name;
            $('#file-name').text('Selected: ' + fileName);
        });

        // Form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {

                    form.classList.add('was-validated');
                }, false);
            });
        })();
    });
</script>

</body>
</html>
