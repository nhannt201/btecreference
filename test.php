<?php
$servername = "103.27.238.234:3306";
$username = "nguyentr_blog";
$password = "Nhan123456789";

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully"; //s*lCRfeyn8P! nguyentrungnhan@103.27.238.234:2210
?>
