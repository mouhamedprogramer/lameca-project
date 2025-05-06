<?php 
    include 'includes/session.php';

    if(isset($_POST['clientId'])){
        // Cette fonction pourrait filtrer les œuvres disponibles en fonction du client
        // Par exemple, ne pas montrer les œuvres déjà commandées par ce client
        // Ici, on affiche simplement toutes les œuvres disponibles
        
        $options = '<option value="" selected disabled>- Sélectionner une œuvre -</option>';
        
        $sql = "SELECT idOeuvre, titre, prix FROM oeuvre WHERE disponibilite = 1 ORDER BY titre ASC";
        $query = $conn->query($sql);
        
        while($row = $query->fetch_assoc()){
            $options .= "<option value='".$row['idOeuvre']."'>".$row['titre']." (".number_format($row['prix'], 2, ',', ' ')." €)</option>";
        }
        
        echo json_encode($options);
    }
?>