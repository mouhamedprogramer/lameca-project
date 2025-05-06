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
        $date_naissance = $_POST['date_naissance'];
        $genre = $_POST['genre'];
        
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
                // Si le mot de passe est fourni, le mettre à jour
                if(!empty($_POST['password'])){
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    
                    $sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, mot_de_passe = ?, telephone = ?, adresse = ?, pays = ?, ville = ?, code_postal = ?, date_naissance = ?, genre = ?, photo = ? WHERE idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssssssssssssi", $nom, $prenom, $email, $password, $telephone, $adresse, $pays, $ville, $code_postal, $date_naissance, $genre, $filename, $id);
                }
                else{
                    $sql = "UPDATE utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?, pays = ?, ville = ?, code_postal = ?, date_naissance = ?, genre = ?, photo = ? WHERE idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("sssssssssssi", $nom, $prenom, $email, $telephone, $adresse, $pays, $ville, $code_postal, $date_naissance, $genre, $filename, $id);
                }
                
                $stmt->execute();
                
                // Valider la transaction
                $conn->commit();
                
                $_SESSION['success'] = 'Client mis à jour avec succès';
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

    header('location: clients.php');
?>