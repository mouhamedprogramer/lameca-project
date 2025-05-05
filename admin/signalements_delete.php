<?php
include 'includes/session.php';

if(isset($_POST['delete'])){
    $id = $_POST['id'];
    
    try{
        // Préparation de la requête de suppression
        $stmt = $conn->prepare("DELETE FROM signalement WHERE idSignalement = ?");
        
        // Liaison des paramètres
        $stmt->bind_param("i", $id);
        
        // Exécution de la requête
        if($stmt->execute()){
            $_SESSION['success'] = 'Signalement supprimé avec succès';
        }
        else{
            $_SESSION['error'] = 'Erreur lors de la suppression du signalement';
        }
        
        $stmt->close();
        
    } catch(Exception $e){
        $_SESSION['error'] = 'Une erreur est survenue: ' . $e->getMessage();
    }
}
else{
    $_SESSION['error'] = 'Sélectionnez d\'abord un signalement à supprimer';
}

header('location: signalements.php');
?>