<?php
    include 'includes/session.php';
    
    if(isset($_POST['idCommande']) && isset($_POST['statut'])){
        $idCommande = $_POST['idCommande'];
        $statut = $_POST['statut'];

        $stmt = $conn->prepare("UPDATE commande SET statut = ? WHERE idCommande = ?");
        $stmt->bind_param("si", $statut, $idCommande);

        if($stmt->execute()){
            echo 'success';
        } else {
            http_response_code(500);
            echo 'Erreur de mise à jour.';
        }
        $stmt->close();
    } else {
        http_response_code(400);
        echo 'Requête invalide.';
    }

?>
