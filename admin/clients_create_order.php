<?php
include 'includes/session.php';

if(isset($_POST['create_order'])) {
    $client_id = $_POST['client_id'];
    $oeuvre_id = $_POST['oeuvre_id'];
    $quantite = $_POST['quantite'];
    $statut = $_POST['statut'];
    
    // Vérifier si l'œuvre est disponible
    $sql = "SELECT disponibilite, prix FROM oeuvre WHERE idOeuvre = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $oeuvre_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $oeuvre = $result->fetch_assoc();
        
        if($oeuvre['disponibilite'] == 1) {
            // Créer la commande
            $sql = "INSERT INTO commande (idClient, idOeuvre, nombreArticles, statut, dateCommande) 
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $client_id, $oeuvre_id, $quantite, $statut);
            
            if($stmt->execute()) {
                $_SESSION['success'] = 'Commande créée avec succès';
                
                // Si la commande est créée et que l'œuvre est unique, on pourrait la marquer comme indisponible
                // Décommentez le code ci-dessous si vous voulez implémenter cette logique
                /*
                if($quantite == 1) {
                    $sql = "UPDATE oeuvre SET disponibilite = 0 WHERE idOeuvre = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $oeuvre_id);
                    $stmt->execute();
                }
                */
            } else {
                $_SESSION['error'] = 'Erreur lors de la création de la commande: ' . $conn->error;
            }
        } else {
            $_SESSION['error'] = 'Cette œuvre n\'est plus disponible';
        }
    } else {
        $_SESSION['error'] = 'Œuvre non trouvée';
    }
} else {
    $_SESSION['error'] = 'Formulaire incomplet';
}

header('location: clients.php');
?>