<?php
	//Kết nối cơ sở dữ liệu
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "fashion_store";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
  		die("Connection failed: " . $conn->connect_error);
	}
?>

