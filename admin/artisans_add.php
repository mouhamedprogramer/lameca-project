<?php
    include 'includes/session.php';

    if(isset($_POST['add'])){
        // Informations utilisateur
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $telephone = $_POST['telephone'];
        $adresse = $_POST['adresse'];
        $pays = $_POST['pays'];
        $ville = $_POST['ville'];
        $code_postal = $_POST['code_postal'];
        $genre = $_POST['genre'];
        
        // Informations artisan
        $specialite = $_POST['specialite'];
        $certification = $_POST['certification'];
        $portfolio = $_POST['portfolio'];
        $statut = isset($_POST['statut']) ? 1 : 0;
        
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
            
            // Définir un nom de fichier par défaut pour la photo
            $filename = '';
            
            // Gestion du téléchargement de la photo
            if(!empty($_FILES['photo']['name'])){
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = $prenom.'_'.$nom.'.'.$ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);
            }
            
            // Commencer une transaction
            $conn->begin_transaction();
            
            try {
                // Insérer l'utilisateur
                $role = 'Artisan'; // Le rôle est fixé à 'Artisan'
                $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, adresse, pays, ville, code_postal, genre, photo, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssssssss", $nom, $prenom, $email, $password_hash, $telephone, $adresse, $pays, $ville, $code_postal, $genre, $filename, $role);
                $stmt->execute();
                
                // Récupérer l'ID de l'utilisateur nouvellement créé
                $idUtilisateur = $conn->insert_id;
                
                // Insérer l'artisan
                $sql = "INSERT INTO artisan (idArtisan, specialite, certification, portfolio, statut_verification) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssi", $idUtilisateur, $specialite, $certification, $portfolio, $statut);
                $stmt->execute();
                
                // Valider la transaction
                $conn->commit();
                
                $_SESSION['success'] = 'Artisan ajouté avec succès';
            } catch (Exception $e) {
                // En cas d'erreur, annuler la transaction
                $conn->rollback();
                $_SESSION['error'] = $e->getMessage();
            }
        }
    }
    else{
        $_SESSION['error'] = 'Remplissez le formulaire d\'ajout en premier';
    }

    header('location: artisans.php');
?>