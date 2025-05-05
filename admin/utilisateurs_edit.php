<?php
	include 'includes/session.php';

	if(isset($_POST['edit'])){
		$id = $_POST['id'];
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$email = $_POST['email'];
		$telephone = $_POST['telephone'];
		$adresse = $_POST['adresse'];
		$pays = $_POST['pays'];
		$ville = $_POST['ville'];
		$code_postal = $_POST['code_postal'];
		$date_naissance = $_POST['date_naissance'];
		$genre = $_POST['genre'];
		$role = $_POST['role'];
		
		// Récupérer les informations actuelles de l'utilisateur
		$sql = "SELECT * FROM utilisateur WHERE idUtilisateur = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("i", $id);
		$stmt->execute();
		$query = $stmt->get_result();
		$row = $query->fetch_assoc();
		
		// Vérifier si l'email existe déjà sur un autre utilisateur
		$sql = "SELECT * FROM utilisateur WHERE email = ? AND idUtilisateur != ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("si", $email, $id);
		$stmt->execute();
		$query = $stmt->get_result();
		
		if($query->num_rows > 0){
			$_SESSION['error'] = 'Cet email est déjà utilisé';
		}
		else{
			// Gestion du téléchargement de la photo
			$filename = $row['photo'];
			if(!empty($_FILES['photo']['name'])){
				$photo = $_FILES['photo']['name'];
				$ext = pathinfo($photo, PATHINFO_EXTENSION);
				$filename = $prenom.'_'.$nom.'.'.$ext;
				move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);
			}
			
			// Si le mot de passe est fourni, le mettre à jour
			if(!empty($_POST['password'])){
				$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
				
				$sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, telephone = ?, adresse = ?, pays = ?, ville = ?, code_postal = ?, date_naissance = ?, photo = ?, genre = ?, role = ? WHERE idUtilisateur = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("sssssssssssssi", $nom, $prenom, $email, $password, $telephone, $adresse, $pays, $ville, $code_postal, $date_naissance, $filename, $genre, $role, $id);
			}
			else{
				$sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?, pays = ?, ville = ?, code_postal = ?, date_naissance = ?, photo = ?, genre = ?, role = ? WHERE idUtilisateur = ?";
				$stmt = $conn->prepare($sql);
				$stmt->bind_param("ssssssssssssi", $nom, $prenom, $email, $telephone, $adresse, $pays, $ville, $code_postal, $date_naissance, $filename, $genre, $role, $id);
			}
			
			if($stmt->execute()){
				$_SESSION['success'] = 'Utilisateur mis à jour avec succès';
				
				// Vérifier si le rôle a changé
				$ancien_role = $row['role'];
				if($ancien_role != $role){
					// Supprimer l'ancien rôle
					if($ancien_role == 'Admin'){
						$sql = "DELETE FROM administrateur WHERE idAdministrateur = ?";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
					}
					else if($ancien_role == 'Artisan'){
						$sql = "DELETE FROM artisan WHERE idArtisan = ?";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
					}
					else if($ancien_role == 'Client'){
						$sql = "DELETE FROM client WHERE idClient = ?";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
					}
					
					// Ajouter le nouveau rôle
					if($role == 'Admin'){
						$sql = "INSERT INTO administrateur (idAdministrateur) VALUES (?)";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
					}
					else if($role == 'Artisan'){
						$sql = "INSERT INTO artisan (idArtisan) VALUES (?)";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
					}
					else if($role == 'Client'){
						$sql = "INSERT INTO client (idClient) VALUES (?)";
						$stmt = $conn->prepare($sql);
						$stmt->bind_param("i", $id);
						$stmt->execute();
					}
				}
			}
			else{
				$_SESSION['error'] = $conn->error;
			}
		}
	}
	else{
		$_SESSION['error'] = 'Remplissez le formulaire de modification en premier';
	}

	header('location: utilisateurs.php');
?>