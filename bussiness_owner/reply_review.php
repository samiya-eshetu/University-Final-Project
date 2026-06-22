<?php
$conn = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$reviewID = $_POST['reviewID'];
$reply = $_POST['reply'];
$now = date('Y-m-d H:i:s');

$stmt = $conn->prepare("UPDATE reviews SET reply = ?, replied_at = ? WHERE reviewID = ?");
$stmt->bind_param("ssi", $reply, $now, $reviewID);
$stmt->execute();

header("Location: review_and_rating.php");
exit();
?>
