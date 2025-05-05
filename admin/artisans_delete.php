<?php
    include 'includes/session.php';

    if(isset($_POST['delete'])){
        $id = $_POST['id'];
        
        // Vérifier s'il y a des œuvres liées à cet artisan
        $sql = "SELECT * FROM oeuvre WHERE idArtisan = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $_SESSION['error'] = 'Impossible de supprimer cet artisan car il a des œuvres associées';
        }
        else{
            // Vérifier s'il y a des événements liés à cet artisan
            $sql = "SELECT * FROM evenement WHERE idArtisan = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0){
                $_SESSION['error'] = 'Impossible de supprimer cet artisan car il a des événements associés';
            }
            else{
                // Récupérer les informations de l'utilisateur
                $sql = "SELECT photo FROM utilisateur WHERE idUtilisateur = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $photo = $row['photo'];
                
                // Commencer une transaction
                $conn->begin_transaction();
                
                try {
                    // Supprimer l'artisan
                    $sql = "DELETE FROM artisan WHERE idArtisan = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    
                    // Supprimer l'utilisateur
                    $sql = "DELETE FROM utilisateur WHERE idUtilisateur = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    
                    // Valider la transaction
                    $conn->commit();
                    
                    // Supprimer la photo si elle existe
                    if(!empty($photo) && file_exists('../images/'.$photo)){
                        unlink('../images/'.$photo);
                    }
                    
                    $_SESSION['success'] = 'Artisan supprimé avec succès';
                } catch (Exception $e) {
                    // En cas d'erreur, annuler la transaction
                    $conn->rollback();
                    $_SESSION['error'] = $e->getMessage();
                }
            }
        }
    }
    else{
        $_SESSION['error'] = 'Sélectionnez un artisan à supprimer en premier';
    }

    header('location: artisans.php');
?>