<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];
		
		// Récupérer les informations de l'œuvre
		$sql = "SELECT o.*, 
                u.nom AS nom_artisan, u.prenom AS prenom_artisan 
                FROM oeuvre o
                LEFT JOIN artisan a ON o.idArtisan = a.idArtisan
                LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
                WHERE o.idOeuvre = ?";
		
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		$oeuvre = $query->fetch_assoc();
		
		// Récupérer les photos de l'œuvre
		$sql = "SELECT * FROM photooeuvre WHERE idOeuvre = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		
		$photos = array();
		while($row = $query->fetch_assoc()){
			$photos[] = $row;
		}
		
		// Ajouter les photos à l'objet œuvre
		$oeuvre['photos'] = $photos;
		
		echo json_encode($oeuvre);
	}
?>