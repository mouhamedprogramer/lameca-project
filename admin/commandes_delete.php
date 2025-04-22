<?php
    include 'includes/session.php';

    if(isset($_POST['delete'])){
        $id = $_POST['id'];
        
        // Récupérer les informations de la commande
        $sql = "SELECT idOeuvre FROM commande WHERE idCommande = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $oeuvre = $row['idOeuvre'];
        
        // Supprimer la commande
        $sql = "DELETE FROM commande WHERE idCommande = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if($stmt->execute()){
            // Rendre l'œuvre disponible à nouveau
            /*
            $sql = "UPDATE oeuvre SET disponibilite = 1 WHERE idOeuvre = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $oeuvre);
            $stmt->execute();
            */
            
            $_SESSION['success'] = 'Commande supprimée avec succès';
        }
        else{
            $_SESSION['error'] = $conn->error;
        }
    }
    else{
        $_SESSION['error'] = 'Sélectionnez une commande à supprimer en premier';
    }

    header('location: commandes.php');
?>