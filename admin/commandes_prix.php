<?php 
	include 'includes/session.php';

	if(isset($_POST['oeuvreId'])){
		$oeuvreId = $_POST['oeuvreId'];
		
		$sql = "SELECT prix FROM oeuvre WHERE idOeuvre = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $oeuvreId);
		$stmt->execute();
		$query = $stmt->get_result();
		$row = $query->fetch_assoc();
		
		echo json_encode($row);
	}
?>