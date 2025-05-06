<?php
include 'includes/session.php';

// Récupérer les dates de commande
$dates = array();

$sql = "SELECT 
          DATE(dateCommande) as date_commande, 
          COUNT(*) as nombre_commandes 
        FROM Commande 
        GROUP BY DATE(dateCommande)";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    // Formatage pour FullCalendar
    $dates[] = array(
        'title' => $row['nombre_commandes'] . ' commande(s)',
        'start' => $row['date_commande'],
        'className' => 'bg-red', // Classe CSS pour la couleur (Rouge par défaut)
        'type' => 'commande'
    );
}

// Retourner le résultat au format JSON
header('Content-Type: application/json');
echo json_encode($dates);
?>