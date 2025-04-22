<?php
    include 'includes/session.php';

    if(isset($_POST['add'])){
        $titre = $_POST['titre'];
        $description = $_POST['description'];
        $prix = $_POST['prix'];
        $caracteristiques = $_POST['caracteristiques'];
        $artisan = $_POST['artisan'];
        $disponibilite = isset($_POST['disponibilite']) ? 1 : 0;
        
        // Commencer une transaction
        $conn->begin_transaction();
        
        try {
            // Insérer l'œuvre dans la base de données
            $sql = "INSERT INTO oeuvre (titre, description, prix, caracteristiques, idArtisan, disponibilite) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssdsii", $titre, $description, $prix, $caracteristiques, $artisan, $disponibilite);
            $stmt->execute();
            
            // Récupérer l'ID de l'œuvre nouvellement créée
            $oeuvre_id = $conn->insert_id;
            
            // Gérer les photos
            if(isset($_FILES['photos']) && count($_FILES['photos']['name']) > 0) {
                // Créer le répertoire s'il n'existe pas
                $upload_dir = '../images/oeuvres/';
                if(!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                // Traiter chaque photo
                for($i = 0; $i < count($_FILES['photos']['name']); $i++) {
                    if(!empty($_FILES['photos']['name'][$i])) {
                        $ext = pathinfo($_FILES['photos']['name'][$i], PATHINFO_EXTENSION);
                        $filename = 'oeuvre_' . $oeuvre_id . '_' . time() . '_' . $i . '.' . $ext;
                        $target_file = $upload_dir . $filename;
                        
                        // Télécharger la photo
                        if(move_uploaded_file($_FILES['photos']['tmp_name'][$i], $target_file)) {
                            // Enregistrer le chemin de la photo dans la base de données
                            $url = 'images/oeuvres/' . $filename;
                            $sql = "INSERT INTO photooeuvre (url, idOeuvre) VALUES (?, ?)";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("si", $url, $oeuvre_id);
                            $stmt->execute();
                        }
                    }
                }
            }
            
            // Tout s'est bien passé, valider la transaction
            $conn->commit();
            $_SESSION['success'] = 'Œuvre ajoutée avec succès';
        } 
        catch (Exception $e) {
            // En cas d'erreur, annuler la transaction
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
    }
    else{
        $_SESSION['error'] = 'Remplissez le formulaire d\'ajout en premier';
    }

    header('location: oeuvres.php');
?>