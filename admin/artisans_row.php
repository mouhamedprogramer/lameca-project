<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];
		
		$sql = "SELECT a.*, 
                u.nom, u.prenom, u.email, u.telephone, u.adresse, u.pays, u.ville, 
                u.code_postal, u.date_naissance, u.photo, u.genre
                FROM artisan a
                LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
                WHERE a.idArtisan = ?";
		
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		$row = $query->fetch_assoc();
		
		echo json_encode($row);
	}
?>