<?php
	include 'includes/session.php';

	if(isset($_POST['add'])){
		$nom = $_POST['nom'];
		$prenom = $_POST['prenom'];
		$email = $_POST['email'];
		$password = $_POST['password'];
		$telephone = $_POST['telephone'];
		$adresse = $_POST['adresse'];
		$pays = $_POST['pays'];
		$ville = $_POST['ville'];
		$code_postal = $_POST['code_postal'];
		$date_naissance = $_POST['date_naissance'];
		$genre = $_POST['genre'];
		$role = $_POST['role'];

		// Vérifier si l'email existe déjà
		$sql = "SELECT * FROM utilisateur WHERE email = ?";
		$stmt = $conn->prepare($sql);
		$stmt->bind_param("s", $email);
		$stmt->execute();
		$query = $stmt->get_result();
		
		if($query->num_rows > 0){
			$_SESSION['error'] = 'Cet email est déjà utilisé';
		}
		else{
			// Hachage du mot de passe
			$password_hash = password_hash($password, PASSWORD_DEFAULT);
			
			// Gestion du téléchargement de la photo
			$filename = '';
			if(!empty($_FILES['photo']['name'])){
				$photo = $_FILES['photo']['name'];
				$ext = pathinfo($photo, PATHINFO_EXTENSION);
				$filename = $prenom.'_'.$nom.'.'.$ext;
				move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);
			}
			
			// Insertion de l'utilisateur
			$sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, adresse, pays, ville, code_postal, date_naissance, photo, genre, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("sssssssssssss", $nom, $prenom, $email, $password_hash, $telephone, $adresse, $pays, $ville, $code_postal, $date_naissance, $filename, $genre, $role);
			
			if($stmt->execute()){
				$_SESSION['success'] = 'Utilisateur ajouté avec succès';
				
				// En fonction du rôle, ajouter à la table correspondante
				$idUtilisateur = $conn->insert_id;
				
				if($role == 'Admin'){
					$sql = "INSERT INTO administrateur (idAdministrateur) VALUES (?)";
					$stmt = $conn->prepare($sql);
					$stmt->bind_param("i", $idUtilisateur);
					$stmt->execute();
				}
				else if($role == 'Artisan'){
					$sql = "INSERT INTO artisan (idArtisan) VALUES (?)";
					$stmt = $conn->prepare($sql);
					$stmt->bind_param("i", $idUtilisateur);
					$stmt->execute();
				}
				else if($role == 'Client'){
					$sql = "INSERT INTO client (idClient) VALUES (?)";
					$stmt = $conn->prepare($sql);
					$stmt->bind_param("i", $idUtilisateur);
					$stmt->execute();
				}
			}
			else{
				$_SESSION['error'] = $conn->error;
			}
		}
	}
	else{
		$_SESSION['error'] = 'Remplissez le formulaire d\'ajout en premier';
	}

	header('location: utilisateurs.php');
?>