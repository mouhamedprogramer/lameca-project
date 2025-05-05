<?php
    include 'includes/session.php';

    if(isset($_POST['add'])){
        $client = $_POST['client'];
        $oeuvre = $_POST['oeuvre'];
        $quantite = $_POST['quantite'];
        $statut = $_POST['statut'];
        
        // Vérifier la disponibilité de l'œuvre
        $sql = "SELECT disponibilite, prix FROM oeuvre WHERE idOeuvre = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $oeuvre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows > 0){
            $row = $result->fetch_assoc();
            
            if($row['disponibilite'] == 1){
                // L'œuvre est disponible, créer la commande
                $sql = "INSERT INTO commande (nombreArticles, statut, idClient, idOeuvre) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isii", $quantite, $statut, $client, $oeuvre);
                
                if($stmt->execute()){
                    // Si l'œuvre est une pièce unique (quantité = 1), la marquer comme indisponible
                    // Cela dépend de la logique de votre application
                    /*
                    if($quantite == 1){
                        $sql = "UPDATE oeuvre SET disponibilite = 0 WHERE idOeuvre = ?";
                        $stmt = $conn->prepare($sql);
                        $stmt->bind_param("i", $oeuvre);
                        $stmt->execute();
                    }
                    */
                    
                    $_SESSION['success'] = 'Commande ajoutée avec succès';
                }
                else{
                    $_SESSION['error'] = $conn->error;
                }
            }
            else{
                $_SESSION['error'] = 'L\'œuvre sélectionnée n\'est plus disponible';
            }
        }
        else{
            $_SESSION['error'] = 'Œuvre introuvable';
        }
    }
    else{
        $_SESSION['error'] = 'Remplissez le formulaire d\'ajout en premier';
    }

    header('location: commandes.php');
?>