<?php
include 'includes/session.php';

if(isset($_POST['client_id'])) {
    $client_id = $_POST['client_id'];
    
    // Récupérer les œuvres disponibles
    $sql = "SELECT idOeuvre, titre, prix FROM oeuvre WHERE disponibilite = 1 ORDER BY titre ASC";
    $query = $conn->query($sql);
    
    echo '<option value="" selected disabled>- Sélectionner une œuvre -</option>';
    
    while($row = $query->fetch_assoc()) {
        echo '<option value="'.$row['idOeuvre'].'">'.$row['titre'].' ('.number_format($row['prix'], 2, ',', ' ').' €)</option>';
    }
} else {
    echo '<option value="" disabled>Erreur: ID client non spécifié</option>';
}
?>