<?php
    include 'includes/session.php';

    if(isset($_POST['edit'])){
        $id = $_POST['id'];
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $prix = $_POST['prix'];
        $caracteristiques = $_POST['caracteristiques'];
        $artisan = $_POST['artisan'];
        $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;
        
        // Commencer une transaction
        $conn->begin_transaction();
        
        try {
            // Mettre à jour l'œuvre dans la base de données
            $sql = "UPDATE oeuvre SET titre = ?, description = ?, prix = ?, caracteristiques = ?, idArtisan = ?, disponibilite = ? WHERE idOeuvre = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsiid", $titre, $description, $prix, $caracteristiques, $artisan, $disponibilite, $id);
            $stmt->execute();
            
            // Gérer les nouvelles photos
            if(isset($_FILES['photos']) && count($_FILES['photos']['name']) > 0) {
                // Créer le répertoire s'il n'existe pas
                $upload_dir = '../images/oeuvres/';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Traiter chaque nouvelle photo
                for($i = 0; $i < count($_FILES['photos']['name']); $i++) {
                    if(!empty($_FILES['photos']['name'][$i])) {
                        $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
                        $filename = 'oeuvre_' . $id . '_' . time() . '_' . $i . '.' . $ext;
                        $target_file = $upload_dir . $filename;
                        
                        // Télécharger la photo
                        if(move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_file)) {
                            // Enregistrer le chemin de la photo dans la base de données
                            $url = 'images/oeuvres/' . $filename;
                            $sql = "INSERT INTO photooeuvre (url, idOeuvre) VALUES (?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $url, $id);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            // Tout s'est bien passé, valider la transaction
            $conn->commit();
            $_SESSION['success'] = 'Œuvre mise à jour avec succès';
        } 
        catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    }
    else{
        $_SESSION['error'] = 'Remplissez le formulaire de modification en premier';
    }

    header('location: oeuvres.php');
?>