<?php
	session_start();
	include 'includes/conn.php';

	if(!isset($_SESSION['admin']) || trim($_SESSION['admin']) == ''){
		header('location: index.php');
	}

	$sql = "SELECT * FROM Utilisateur WHERE idUtilisateur = '".$_SESSION['admin']."'";
	$query = $conn->query($sql);
	$user = $query->fetch_assoc();
	
?>


