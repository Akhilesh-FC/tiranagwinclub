<?php

$servername = "localhost";
$username = "u536191189_tirangawin";
$password = "Fc3133_tiranagwin_$";
$dbname = "u536191189_tirangawin";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

