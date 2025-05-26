<?php
    include 'includes/session.php';

    if(isset($_POST['edit'])){
        $id = $_POST['id'];
        
        // Informations utilisateur
        $nom = $_POST['nom'];
        $prenom = $_POST['prenom'];
        $email = $_POST['email'];
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
            // Récupérer les informations actuelles de l'utilisateur
            $sql = "SELECT photo FROM utilisateur WHERE idUtilisateur = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            // Conserver l'ancien nom de fichier par défaut
            $filename = $row['photo'];
            
            // Gestion du téléchargement de la nouvelle photo
            if(!empty($_FILES['photo']['name'])){
                // Supprimer l'ancienne photo si elle existe
                if(!empty($filename) && file_exists('../images/'.$filename)){
                    unlink('../images/'.$filename);
                }
                
                // Télécharger la nouvelle photo
                $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
                $filename = $prenom.'_'.$nom.'.'.$ext;
                move_uploaded_file($_FILES['photo']['tmp_name'], '../images/'.$filename);
            }
            
            // Commencer une transaction
            $conn->begin_transaction();
            
            try {
                // Si le mot de passe est fourni, le mettre à jour
                if(!empty($_POST['password'])){
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    
                    $sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, telephone = ?, adresse = ?, pays = ?, ville = ?, code_postal = ?, genre = ?, photo = ? WHERE idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssssssssi", $nom, $prenom, $email, $password, $telephone, $adresse, $pays, $ville, $code_postal, $genre, $filename, $id);
                }
                else{
                    // CORRECTION: Enlever le paramètre mot_de_passe de la requête ET du bind_param
                    $sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?, pays = ?, ville = ?, code_postal = ?, genre = ?, photo = ? WHERE idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssssssi", $nom, $prenom, $email, $telephone, $adresse, $pays, $ville, $code_postal, $genre, $filename, $id);
                    // Suppression de $password des paramètres car il n'est pas dans la requête SQL
                }
                
                $stmt->execute();
                
                // Mettre à jour les informations de l'artisan
                $sql = "UPDATE artisan SET specialite = ?, certification = ?, portfolio = ?, statut_verification = ? WHERE idArtisan = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssii", $specialite, $certification, $portfolio, $statut, $id);
                $stmt->execute();
                
                // Valider la transaction
                $conn->commit();
                
                $_SESSION['success'] = 'Artisan mis à jour avec succès';
            } catch (Exception $e) {
                // En cas d'erreur, annuler la transaction
                $conn->rollback();
                $_SESSION['error'] = $e->getMessage();
            }
        }
    }
    else{
        $_SESSION['error'] = 'Remplissez le formulaire de modification en premier';
    }

    header('location: artisans.php');
?>