<?php
$servername = "sql207.infinityfree.com";
$username = "if0_42226342";
$password = "VqIUuAIZ38T0f8";
$dbname = "if0_42226342_allonone";




$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = 1; // Replace this with session ID if login system exists

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Check if email is already used by another user
    $check_email_sql = "SELECT id FROM business_owners WHERE email = '$email' AND id != $user_id";
    $result = $conn->query($check_email_sql);
    if ($result->num_rows > 0) {
        $message = "Error: The email address is already in use by another account.";
    } else {
        // Update name and email
        $sql = "UPDATE business_owners SET name='$name', email='$email' WHERE id = $user_id";
        $conn->query($sql);

        // If password fields are filled and match
        if (!empty($new_password) && $new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_sql = "UPDATE business_owners SET password='$hashed_password' WHERE id = $user_id";
            $conn->query($password_sql);
        }

        $message = "Profile updated successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Update Profile</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 30px; background-color: #f2f2f2; }
        form { max-width: 400px; margin: auto; padding: 20px; background: white; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        label { display: block; margin-top: 15px; }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%; padding: 10px; margin-top: 5px;
            border: 1px solid #ccc; border-radius: 5px;
        }
        button {
            margin-top: 20px; padding: 10px 15px;
            background-color: #4CAF50; color: white;
            border: none; border-radius: 5px; cursor: pointer;
        }
        .message {
            text-align: center; margin-bottom: 20px;
            color: green; font-weight: bold;
        }
    </style>
</head>
<body>

<h2 style="text-align: center;">Edit Profile</h2>

<?php if (!empty($message)): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form action="update_profile.php" method="POST">
    <label for="full_name">Full Name:</label>
    <input type="text" name="full_name" required>

    <label for="email">Email:</label>
    <input type="email" name="email" required>

    <label for="current_password">Current Password:</label>
    <input type="password" name="current_password">

    <label for="new_password">New Password:</label>
    <input type="password" name="new_password">

    <label for="confirm_password">Confirm New Password:</label>
    <input type="password" name="confirm_password">

    <button type="submit">Update Profile</button>
</form>

</body>
</html>
