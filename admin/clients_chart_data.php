<?php
include 'includes/session.php';

// Récupérer les données pour le graphique d'évolution des inscriptions mensuelles
// Nous allons récupérer les 12 derniers mois

$months = [];
$data = [];

// Générer les 12 derniers mois
for ($i = 11; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime("-$i months")); // Format d'affichage (ex: Jan 2025)
    
    // Requête pour compter les inscriptions du mois
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date)); // Dernier jour du mois
    
    $sql = "SELECT COUNT(*) as count 
            FROM utilisateur 
            WHERE role = 'Client' 
            AND date_creation BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $data[] = $row['count'];
}

// Renvoyer les données au format JSON
echo json_encode([
    'labels' => $months,
    'data' => $data
]);
?>