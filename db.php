<?php
$servername = "localhost";
$username = "phpmyadmin"; // ya da root
$password = "";
$database = "SENG216";

// Bağlantıyı oluştur
$conn = new mysqli($servername, $username, $password, $database);

// Bağlantı kontrolü
if ($conn->connect_error) {
    die("Bağlantı başarısız: " . $conn->connect_error);
}

// echo "Bağlantı başarılı!";
?>
