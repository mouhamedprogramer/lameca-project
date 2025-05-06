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
        $date_naissance = $_POST['date_naissance'];
        $genre = $_POST['genre'];
        
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
                $filename = 'client_'.time().'_'.$prenom.'_'.$nom.'.'.$ext;
                
                // Vérifier le type de fichier
                $allowed = array('jpg', 'jpeg', 'png', 'gif');
                if(!in_array(strtolower($ext), $allowed)){
                    $_SESSION['error'] = 'Format de fichier non autorisé. Utilisez JPG, JPEG, PNG ou GIF.';
                    header('location: clients.php');
                    exit();
                }
                
                // Vérifier la taille du fichier (5MB max)
                if($_FILES['photo']['size'] > 5000000){
                    $_SESSION['error'] = 'La taille du fichier ne doit pas dépasser 5MB';
                    header('location: clients.php');
                    exit();
                }
                
                move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);
            }
            
            // Commencer une transaction
            $conn->begin_transaction();
            
            try {
                // Insérer l'utilisateur
                $role = 'Client'; // Le rôle est fixé à 'Client'
                $sql = "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, adresse, pays, ville, code_postal, date_naissance, genre, photo, role, date_creation) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssssssss", $nom, $prenom, $email, $password_hash, $telephone, $adresse, $pays, $ville, $code_postal, $date_naissance, $genre, $filename, $role);
                $stmt->execute();
                
                // Récupérer l'ID de l'utilisateur nouvellement créé
                $idUtilisateur = $conn->insert_id;
                
                // Insérer le client
                $sql = "INSERT INTO client (idClient) VALUES (?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $idUtilisateur);
                $stmt->execute();
                
                // Valider la transaction
                $conn->commit();
                
                $_SESSION['success'] = 'Client ajouté avec succès';
                
                // Envoyer un email de bienvenue (option)
                // Cette partie peut être implémentée selon vos besoins
                
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

    header('location: clients.php');
?>