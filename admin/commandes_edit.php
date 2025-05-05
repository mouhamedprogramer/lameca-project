<?php
    include 'includes/session.php';

    if(isset($_POST['edit'])){
        $id = $_POST['id'];
        $client = $_POST['client'];
        $oeuvre = $_POST['oeuvre'];
        $quantite = $_POST['quantite'];
        $statut = $_POST['statut'];
        
        // Récupérer les anciennes informations de la commande
        $sql = "SELECT idOeuvre FROM commande WHERE idCommande = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $old_oeuvre = $row['idOeuvre'];
        
        // Si l'œuvre a changé, vérifier la disponibilité de la nouvelle œuvre
        if($old_oeuvre != $oeuvre){
            $sql = "SELECT disponibilite FROM oeuvre WHERE idOeuvre = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $oeuvre);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if($result->num_rows > 0){
                $row = $result->fetch_assoc();
                
                if($row['disponibilite'] != 1){
                    $_SESSION['error'] = 'L\'œuvre sélectionnée n\'est pas disponible';
                    header('location: commandes.php');
                    exit();
                }
            }
        }
        
        // Mettre à jour la commande
        $sql = "UPDATE commande SET nombreArticles = ?, statut = ?, idClient = ?, idOeuvre = ? WHERE idCommande = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isiii", $quantite, $statut, $client, $oeuvre, $id);
        
        if($stmt->execute()){
            // Si l'œuvre a changé, peut-être mettre à jour la disponibilité des œuvres
            if($old_oeuvre != $oeuvre){
                // L'ancienne œuvre redevient disponible
                /*
                $sql = "UPDATE oeuvre SET disponibilite = 1 WHERE idOeuvre = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $old_oeuvre);
                $stmt->execute();
                
                // Si la nouvelle œuvre est une pièce unique, la marquer comme indisponible
                if($quantite == 1){
                    $sql = "UPDATE oeuvre SET disponibilite = 0 WHERE idOeuvre = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $oeuvre);
                    $stmt->execute();
                }
                */
            }
            
            $_SESSION['success'] = 'Commande mise à jour avec succès';
        }
        else{
            $_SESSION['error'] = $conn->error;
        }
    }
    else{
        $_SESSION['error'] = 'Remplissez le formulaire de modification en premier';
    }

    header('location: commandes.php');
?>