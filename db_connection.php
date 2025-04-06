<?php
$servername = "localhost";
$username = "root"; // default XAMPP username
$password = "Zoro*2222"; // default XAMPP password (usually empty)
$dbname = "database1";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
