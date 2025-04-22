<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];
		
		// Requête pour récupérer les informations du client
		$sql = "SELECT c.*, 
                u.nom, u.prenom, u.email, u.telephone, u.adresse, u.pays, u.ville, 
                u.code_postal, u.date_naissance, u.photo, u.genre, u.date_creation
                FROM client c
                LEFT JOIN utilisateur u ON c.idClient = u.idUtilisateur
                WHERE c.idClient = ?";
		
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		$row = $query->fetch_assoc();
		
		// Récupérer les statistiques du client
		// Nombre de commandes
		$sql = "SELECT COUNT(*) as nb_commandes FROM commande WHERE idClient = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$stats = $result->fetch_assoc();
		$row['nb_commandes'] = $stats['nb_commandes'];
		
		// Total dépensé
		$sql = "SELECT SUM(o.prix * c.nombreArticles) as total_depense 
				FROM commande c 
				JOIN oeuvre o ON c.idOeuvre = o.idOeuvre 
				WHERE c.idClient = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$stats = $result->fetch_assoc();
		$row['total_depense'] = $stats['total_depense'] ?? 0;
		
		// Date de la dernière commande
		$sql = "SELECT dateCommande as derniere_commande 
				FROM commande 
				WHERE idClient = ? 
				ORDER BY dateCommande DESC 
				LIMIT 1";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if($result->num_rows > 0) {
			$stats = $result->fetch_assoc();
			$row['derniere_commande'] = $stats['derniere_commande'];
		} else {
			$row['derniere_commande'] = null;
		}
		
		echo json_encode($row);
	}
?>