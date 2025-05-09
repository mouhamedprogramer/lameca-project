<?php
include 'includes/session.php';
header('Content-Type: application/json');

// Récupérer les événements à venir et en cours
$sql = "SELECT e.idEvenement, e.nomEvenement, e.description, e.dateDebut, e.dateFin, e.lieu, e.mis_en_avant,
        u.prenom, u.nom
        FROM evenement e
        LEFT JOIN artisan a ON e.idArtisan = a.idArtisan
        LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
        WHERE e.dateDebut >= CURDATE() OR 
              (e.dateFin IS NOT NULL AND e.dateFin >= CURDATE())
        ORDER BY e.dateDebut ASC";

$query = $conn->query($sql);
$events = [];

while ($row = $query->fetch_assoc()) {
    // Déterminer la couleur en fonction de si l'événement est mis en avant
    $backgroundColor = $row['mis_en_avant'] ? '#f39c12' : '#00c0ef';
    $borderColor = $row['mis_en_avant'] ? '#f08c00' : '#00a7d0';
    
    // Déterminer le titre à afficher
    $title = $row['nomEvenement'];
    if (!empty($row['prenom']) && !empty($row['nom'])) {
        $title .= ' par ' . $row['prenom'] . ' ' . $row['nom'];
    }
    
    // Créer l'événement au format FullCalendar
    $event = [
        'id' => $row['idEvenement'],
        'title' => $title,
        'start' => $row['dateDebut'],
        'end' => $row['dateFin'],
        'backgroundColor' => $backgroundColor,
        'borderColor' => $borderColor,
        'allDay' => true
    ];
    
    $events[] = $event;
}

echo json_encode($events);
?>