<?php
	session_start();
	include 'includes/conn.php';

	if(!isset($_SESSION['artisan']) || trim($_SESSION['artisan']) == ''){
		header('location: index.php');
	}

	$sql = "SELECT * FROM Utilisateur WHERE idUtilisateur = '".$_SESSION['artisan']."'";
	$query = $conn->query($sql);
	$user = $query->fetch_assoc();
	
?>


