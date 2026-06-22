<?php


$host = "sql207.infinityfree.com";
$username = "if0_42226342";
$password = "VqIUuAIZ38T0f8";
$database = "if0_42226342_allonone";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
