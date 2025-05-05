<?php
include 'includes/session.php';

if(isset($_POST['add'])){
    // Récupération des données du formulaire
    $nom = $_POST['nom'];
    $description = $_POST['description'];
    $debut = $_POST['debut'];
    $fin = !empty($_POST['fin']) ? $_POST['fin'] : NULL;
    $lieu = $_POST['lieu'];
    $artisan = !empty($_POST['artisan']) ? $_POST['artisan'] : NULL;
    $mis_en_avant = isset($_POST['mis_en_avant']) ? 1 : 0;
    
    // Validation des données
    if(empty($nom)){
        $_SESSION['error'] = 'Le nom de l\'événement est obligatoire';
        header('location: evenements.php');
        exit();
    }
    
    if(empty($debut)){
        $_SESSION['error'] = 'La date de début est obligatoire';
        header('location: evenements.php');
        exit();
    }
    
    // Si une date de fin est fournie, vérifier qu'elle est après la date de début
    if(!empty($fin) && strtotime($fin) < strtotime($debut)){
        $_SESSION['error'] = 'La date de fin doit être après la date de début';
        header('location: evenements.php');
        exit();
    }
    
    try{
        // Préparation de la requête
        $stmt = $conn->prepare("INSERT INTO evenement (nomEvenement, description, dateDebut, dateFin, lieu, idArtisan, mis_en_avant) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        // Liaison des paramètres
        $stmt->bind_param("sssssii", $nom, $description, $debut, $fin, $lieu, $artisan, $mis_en_avant);
        
        // Exécution de la requête
        if($stmt->execute()){
            $_SESSION['success'] = 'Événement ajouté avec succès';
        }
        else{
            $_SESSION['error'] = 'Erreur lors de l\'ajout de l\'événement';
        }
        
        $stmt->close();
        
    } catch(Exception $e){
        $_SESSION['error'] = 'Une erreur est survenue: ' . $e->getMessage();
    }
}
else{
    $_SESSION['error'] = 'Remplissez d\'abord le formulaire';
}

header('location: evenements.php');
?>