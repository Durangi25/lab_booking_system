<?php
$host = "localhost";
$user = "root";
$password = ""; // XAMPP default has no password
$database = "lab_booking";
$port = 3306; // Use 3307 if XAMPP uses different port

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}
?>
