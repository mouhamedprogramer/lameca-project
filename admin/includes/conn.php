<?php
	$conn = new mysqli("localhost", "root", "", "lameca");

	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	
?>