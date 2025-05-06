<?php 
	include 'includes/session.php';

	if(isset($_POST['id'])){
		$id = $_POST['id'];
		
		$sql = "SELECT c.*, 
                cl.idClient,
                u.nom AS nom_client, u.prenom AS prenom_client, u.email AS email_client, 
                u.telephone AS telephone_client, u.adresse AS adresse_client,
                o.idOeuvre, o.titre AS titre_oeuvre, o.description AS description_oeuvre, 
                o.prix AS prix_oeuvre, o.disponibilite
                FROM commande c
                LEFT JOIN client cl ON c.idClient = cl.idClient
                LEFT JOIN utilisateur u ON cl.idClient = u.idUtilisateur
                LEFT JOIN oeuvre o ON c.idOeuvre = o.idOeuvre
                WHERE c.idCommande = ?";
		
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		$row = $query->fetch_assoc();
		
		echo json_encode($row);
	}
?>