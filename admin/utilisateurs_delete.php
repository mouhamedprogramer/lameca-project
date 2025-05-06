<?php
	include 'includes/session.php';

	if(isset($_POST['delete'])){
		$id = $_POST['id'];
		
		// Récupérer les informations de l'utilisateur
		$sql = "SELECT * FROM utilisateur WHERE idUtilisateur = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		$row = $query->fetch_assoc();
		
		// Supprimer les entrées dans les tables correspondantes au rôle
		if($row['role'] == 'Admin'){
			$sql = "DELETE FROM administrateur WHERE idAdministrateur = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $id);
			$stmt->execute();
		}
		else if($row['role'] == 'Artisan'){
			$sql = "DELETE FROM artisan WHERE idArtisan = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $id);
			$stmt->execute();
		}
		else if($row['role'] == 'Client'){
			$sql = "DELETE FROM client WHERE idClient = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $id);
			$stmt->execute();
		}
		
		// Supprimer l'utilisateur
		$sql = "DELETE FROM utilisateur WHERE idUtilisateur = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		
		if($stmt->execute()){
			$_SESSION['success'] = 'Utilisateur supprimé avec succès';
			
			// Supprimer la photo du serveur si elle existe
			if(!empty($row['photo'])){
				if(file_exists('../images/'.$row['photo'])){
					unlink('../images/'.$row['photo']);
				}
			}
		}
		else{
			$_SESSION['error'] = $conn->error;
		}
	}
	else{
		$_SESSION['error'] = 'Sélectionnez un utilisateur à supprimer en premier';
	}

	header('location: utilisateurs.php');
?>