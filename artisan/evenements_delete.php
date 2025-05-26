<?php
include 'includes/session.php';

if(isset($_POST['delete'])){
    $id = $_POST['id'];
    
    try{
        // Préparation de la requête de suppression
        $stmt = $conn->prepare("DELETE FROM evenement WHERE idEvenement = ?");
        
        // Liaison des paramètres
        $stmt->bind_param("i", $id);
        
        // Exécution de la requête
        if($stmt->execute()){
            $_SESSION['success'] = 'Événement supprimé avec succès';
        }
        else{
            $_SESSION['error'] = 'Erreur lors de la suppression de l\'événement';
        }
        
        $stmt->close();
        
    } catch(Exception $e){
        // Vérifier si l'erreur est due à une contrainte de clé étrangère
        if($e->getCode() == 1451){ // Code MySQL pour erreur de contrainte
            $_SESSION['error'] = 'Impossible de supprimer cet événement car il est référencé ailleurs';
        } else {
            $_SESSION['error'] = 'Une erreur est survenue: ' . $e->getMessage();
        }
    }
}
else{
    $_SESSION['error'] = 'Sélectionnez d\'abord un événement à supprimer';
}

header('location: evenements.php');
?>