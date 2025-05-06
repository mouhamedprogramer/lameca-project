<?php
    include 'includes/session.php';

    if(isset($_POST['delete']) && isset($_POST['photo_id']) && isset($_POST['oeuvre_id'])){
        $photo_id = $_POST['photo_id'];
        $oeuvre_id = $_POST['oeuvre_id'];
        
        // Récupérer l'URL de la photo
        $sql = "SELECT url FROM photooeuvre WHERE idPhoto = ? AND idOeuvre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $photo_id, $oeuvre_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            $photo_url = $row['url'];
            
            // Supprimer la photo de la base de données
            $sql = "DELETE FROM photooeuvre WHERE idPhoto = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $photo_id);
            
            if($stmt->execute()){
                // Supprimer le fichier physique
                $file_path = '../' . $photo_url;
                if(file_exists($file_path)){
                    unlink($file_path);
                }
                
                $_SESSION['success'] = 'Photo supprimée avec succès';
            }
            else{
                $_SESSION['error'] = 'Erreur lors de la suppression de la photo';
            }
        }
        else{
            $_SESSION['error'] = 'Photo non trouvée';
        }
    }
    else{
        $_SESSION['error'] = 'Identifiants manquants pour la suppression de la photo';
    }

    header('location: produits.php');
?>