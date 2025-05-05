<?php
include 'includes/session.php';

if(isset($_POST['edit'])){
    // Récupération des données du formulaire
    $id = $_POST['id'];
    $typeCible = $_POST['type_cible'];
    $motif = $_POST['motif'];
    $statut = $_POST['statut'];
    
    // Déterminer l'ID de la cible en fonction du type
    $idCible = NULL;
    if($typeCible == 'Utilisateur' && !empty($_POST['utilisateur'])){
        $idCible = $_POST['utilisateur'];
    } else if($typeCible == 'Oeuvre' && !empty($_POST['oeuvre'])){
        $idCible = $_POST['oeuvre'];
    }
    
    // Validation des données
    if(empty($typeCible)){
        $_SESSION['error'] = 'Veuillez sélectionner un type de cible';
        header('location: signalements.php');
        exit();
    }
    
    if(empty($idCible)){
        $_SESSION['error'] = 'Veuillez sélectionner une cible';
        header('location: signalements.php');
        exit();
    }
    
    if(empty($motif)){
        $_SESSION['error'] = 'Le motif du signalement est obligatoire';
        header('location: signalements.php');
        exit();
    }
    
    try{
        // Préparation de la requête
        $stmt = $conn->prepare("UPDATE signalement SET idCible = ?, typeCible = ?, motif = ?, statut = ? WHERE idSignalement = ?");
        
        // Liaison des paramètres
        $stmt->bind_param("isssi", $idCible, $typeCible, $motif, $statut, $id);
        
        // Exécution de la requête
        if($stmt->execute()){
            $_SESSION['success'] = 'Signalement mis à jour avec succès';
        }
        else{
            $_SESSION['error'] = 'Erreur lors de la mise à jour du signalement';
        }
        
        $stmt->close();
        
    } catch(Exception $e){
        $_SESSION['error'] = 'Une erreur est survenue: ' . $e->getMessage();
    }
}
else{
    $_SESSION['error'] = 'Sélectionnez d\'abord un signalement à modifier';
}

header('location: signalements.php');
?>