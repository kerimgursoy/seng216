<?php

$servername = "localhost";

$username = "phpmyadmin"; 

$password = "";

$database = "SENG216";

$conn = new mysqli($servername, $username, $password, $database);


if ($conn->connect_error) {

    die("Connection Failed: " . $conn->connect_error);

}

?>