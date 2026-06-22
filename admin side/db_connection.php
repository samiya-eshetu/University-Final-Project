<?php


$host = "sql207.infinityfree.com";
$username = "if0_42226342";
$password = "VqIUuAIZ38T0f8";
$dbname = "if0_42226342_allonone";

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
