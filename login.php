<?php
require 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST['email']);
    $pass = $_POST['password'];
    $errors = [];

    try {
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE email = ? OR username = ?");
        $stmt->execute([$user, $user]);
        $account = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($account) {
            if (password_verify($pass, $account['password'])) {
                $_SESSION['accountID'] = $account['accountID'];
                $_SESSION['email'] = $account['email'];
                $_SESSION['role'] = $account['role'];
                $_SESSION['username'] = $account['username'];

                if ($account['role'] === 'tourist') {
                    $stmtTourist = $conn->prepare("SELECT * FROM tourists WHERE touristID = ?");
                    $stmtTourist->execute([$account['accountID']]);
                    $tourist = $stmtTourist->fetch(PDO::FETCH_ASSOC);

                    if ($tourist) {
                        $_SESSION['fullName'] = $tourist['fullName'];
                        $_SESSION['phoneNumber'] = $tourist['phoneNumber'];
                        $_SESSION['nationality'] = $tourist['nationality'];
                    }
                } elseif ($account['role'] === 'admin') {
                    $stmtAdmin = $conn->prepare("SELECT * FROM admins WHERE adminID = ?");
                    $stmtAdmin->execute([$account['accountID']]);
                    $admin = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

                    if ($admin) {
                        $_SESSION['fullName'] = $admin['fullName'];
                        $_SESSION['phoneNumber'] = $admin['phoneNumber'];
                        $_SESSION['adminRole'] = $admin['role'];
                    }
                }

                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Invalid password. Please try again.";
            }
        } else {
            $errors[] = "Account not found. Please check your email/username.";
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $errors[] = "A system error occurred. Please try again later.";
    }

    $_SESSION['login_errors'] = $errors;
    $_SESSION['login_attempted'] = true;

    $referrer = 'user login.php';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referrer = basename($_SERVER['HTTP_REFERER']);
    }
    if ($referrer === 'user sign up.html') {
        $referrer = 'user sign up.php';
    }

    header("Location: $referrer");
    exit;
}
