<?php
include 'includes/session.php';

if(isset($_POST['add'])){
    // Récupération des données du formulaire
    $signaleur = $_POST['signaleur'];
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
    if(empty($signaleur)){
        $_SESSION['error'] = 'Veuillez sélectionner un signaleur';
        header('location: signalements.php');
        exit();
    }
    
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
        $stmt = $conn->prepare("INSERT INTO signalement (idSignaleur, idCible, typeCible, motif, statut) VALUES (?, ?, ?, ?, ?)");
        
        // Liaison des paramètres
        $stmt->bind_param("iisss", $signaleur, $idCible, $typeCible, $motif, $statut);
        
        // Exécution de la requête
        if($stmt->execute()){
            $_SESSION['success'] = 'Signalement ajouté avec succès';
        }
        else{
            $_SESSION['error'] = 'Erreur lors de l\'ajout du signalement';
        }
        
        $stmt->close();
        
    } catch(Exception $e){
        $_SESSION['error'] = 'Une erreur est survenue: ' . $e->getMessage();
    }
}
else{
    $_SESSION['error'] = 'Remplissez d\'abord le formulaire';
}

header('location: signalements.php');
?>