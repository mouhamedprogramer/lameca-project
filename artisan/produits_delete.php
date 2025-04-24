<?php
    include 'includes/session.php';

    if(isset($_POST['delete'])){
        $id = $_POST['id'];
        
        // Vérifier s'il y a des commandes liées à cette œuvre
        $sql = "SELECT * FROM commande WHERE idOeuvre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $_SESSION['error'] = 'Impossible de supprimer cette œuvre car elle est liée à des commandes';
        }
        else{
            // Commencer une transaction
            $conn->begin_transaction();
            
            try {
                // Récupérer les photos de l'œuvre
                $sql = "SELECT idPhoto, url FROM photooeuvre WHERE idOeuvre = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $photos = [];
                
                while($row = $result->fetch_assoc()) {
                    $photos[] = $row;
                }
                
                // Supprimer l'œuvre (va automatiquement supprimer les photos grâce à ON DELETE CASCADE)
                $sql = "DELETE FROM oeuvre WHERE idOeuvre = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                // Valider la transaction
                $conn->commit();
                
                // Supprimer les fichiers physiques
                foreach($photos as $photo) {
                    $file_path = '../' . $photo['url'];
                    if(file_exists($file_path)) {
                        unlink($file_path);
                    }
                }
                
                $_SESSION['success'] = 'Œuvre supprimée avec succès';
            } 
            catch (Exception $e) {
                // En cas d'erreur, annuler la transaction
                $conn->rollback();
                $_SESSION['error'] = $e->getMessage();
            }
        }
    }
    else{
        $_SESSION['error'] = 'Sélectionnez une œuvre à supprimer en premier';
    }

    header('location: produits.php');
?>