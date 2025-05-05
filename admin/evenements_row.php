<?php 
include 'includes/session.php';

if(isset($_POST['id'])){
    $id = $_POST['id'];
    
    $sql = "SELECT e.*, u.nom, u.prenom, CONCAT(u.prenom, ' ', u.nom) as artisan_nom 
            FROM evenement e 
            LEFT JOIN artisan a ON e.idArtisan = a.idArtisan
            LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
            WHERE e.idEvenement = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode($row);
}
?>