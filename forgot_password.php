<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);
    $errors = [];

    try {
        // Check if email exists in accounts table
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ?");
        $stmt->execute([$email]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            // Store account info in session for verification step
            $_SESSION['reset_account'] = $account;
            header("Location: verify_reset.php");
            exit;
        } else {
            $errors[] = "No account found with that email address.";
        }
    } catch (PDOException $e) {
        error_log("Forgot password error: " . $e->getMessage());
        $errors[] = "A system error occurred. Please try again later.";
    }

    if (!empty($errors)) {
        $_SESSION['forgot_errors'] = $errors;
        header("Location: forgot_password.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600,700,800,900" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }
        .form-style {
            padding: 13px 20px;
            padding-left: 55px;
            height: 48px;
            width: 100%;
            font-weight: 500;
            border-radius: 8px;
            font-size: 14px;
            line-height: 22px;
            letter-spacing: 0.5px;
            outline: none;
            color: #4a5568;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            transition: all 200ms linear;
            box-shadow: 0 1px 3px 0 rgba(0,0,0,0.05);
        }
        .form-style:focus, .form-style:active {
            border-color: #4a6cf7;
            box-shadow: 0 0 0 3px rgba(74, 108, 247, 0.15);
        }
        .input-icon {
            position: absolute;
            top: 0;
            left: 18px;
            height: 48px;
            font-size: 20px;
            line-height: 48px;
            text-align: left;
            color: #4a6cf7;
        }
        .btn {
            border-radius: 8px;
            height: 48px;
            font-size: 14px;
            font-weight: 600;
            background-color: #4a6cf7;
            color: white;
            border: none;
        }
        .btn:hover {
            background-color: #3a5ce4;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card p-4">
                    <div class="card-body">
                        <h3 class="text-center mb-4">Forgot Password</h3>
                        <p class="text-center mb-4">Enter your email address to reset your password.</p>
                        
                        <form action="forgot_password.php" method="POST">
                            <div class="form-group mb-3 position-relative">
                                <input type="email" name="email" class="form-style" placeholder="Your Email" required>
                                <i class="input-icon fas fa-envelope"></i>
                            </div>
                            
                            <button type="submit" class="btn btn-primary w-100">Continue</button>
                        </form>
                        
                        <div class="text-center mt-3">
                            <a href="user sign up.php" class="text-decoration-none">Back to Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($_SESSION['forgot_errors'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: `<?= nl2br(addslashes(implode("<br>", $_SESSION['forgot_errors']))) ?>`,
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['forgot_errors']); ?>
        });
    </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>