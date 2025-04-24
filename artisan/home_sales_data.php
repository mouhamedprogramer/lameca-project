<?php
include 'includes/session.php';

// Récupérer les données des ventes et du chiffre d'affaires des 6 derniers mois
$labels = [];
$ca_data = [];
$sales_data = [];

// Générer les 6 derniers mois
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $labels[] = date('M Y', strtotime("-$i months")); // Format d'affichage (ex: Jan 2025)
    
    // Requête pour compter les ventes du mois
    $start_date = $month . '-01';
    $end_date = date('Y-m-t', strtotime($start_date)); // Dernier jour du mois
    
    // Nombre de ventes
    $sql = "SELECT COUNT(*) as count 
            FROM commande 
            WHERE dateCommande BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $sales_data[] = $row['count'];
    
    // Chiffre d'affaires
    $sql = "SELECT SUM(o.prix * c.nombreArticles) as ca 
            FROM commande c 
            JOIN oeuvre o ON c.idOeuvre = o.idOeuvre 
            WHERE c.dateCommande BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $ca_data[] = $row['ca'] ?? 0;
}

// Renvoyer les données au format JSON
echo json_encode([
    'labels' => $labels,
    'ca_data' => $ca_data,
    'sales_data' => $sales_data
]);
?>