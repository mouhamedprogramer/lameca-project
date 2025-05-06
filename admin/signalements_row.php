<?php 
include 'includes/session.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];
    
    $sql = "SELECT s.*, 
            CONCAT(us.prenom, ' ', us.nom) as signaleur_nom,
            CONCAT(uc.prenom, ' ', uc.nom) as cible_nom,
            o.titre as oeuvre_titre
            FROM signalement s 
            LEFT JOIN utilisateur us ON s.idSignaleur = us.idUtilisateur
            LEFT JOIN utilisateur uc ON s.idCible = uc.idUtilisateur AND s.typeCible = 'Utilisateur'
            LEFT JOIN oeuvre o ON s.idCible = o.idOeuvre AND s.typeCible = 'Oeuvre'
            WHERE s.idSignalement = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode($row);
}
?>