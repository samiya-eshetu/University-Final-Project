<?php
$mysqli = new mysqli("sql207.infinityfree.com", "if0_42226342", "VqIUuAIZ38T0f8", "if0_42226342_allonone");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $reviewID = $_POST['reviewID'];
    $replyText = $_POST['replyText'];

    $stmt = $mysqli->prepare("UPDATE reviews SET reply = ?, replied_at = NOW() WHERE reviewID = ?");
    $stmt->bind_param("si", $replyText, $reviewID);
    $stmt->execute();

    header("Location: review_and_rating.php");
    exit();
}
?>
