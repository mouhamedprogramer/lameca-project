<?php
require_once 'includes/conn.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_POST['query']) || empty(trim($_POST['query']))) {
    echo json_encode(['error' => 'Query manquante']);
    exit;
}

$query = trim($_POST['query']);
$searchTerm = '%' . $query . '%';

$results = [
    'oeuvres' => [],
    'artisans' => [],
    'evenements' => []
];

try {
    // Recherche dans les œuvres (avec les bons noms de colonnes)
    $sql = "SELECT o.idOeuvre as id, o.titre as nom, o.prix, 
                   CONCAT(u.prenom, ' ', u.nom) as artisan
            FROM Oeuvre o 
            LEFT JOIN Utilisateur u ON o.idArtisan = u.idUtilisateur 
            WHERE (o.titre LIKE ? 
               OR o.description LIKE ? 
               OR o.caracteristiques LIKE ?)
               AND o.disponibilite = 1
            ORDER BY o.titre 
            LIMIT 5";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $results['oeuvres'][] = [
                'id' => $row['id'],
                'nom' => htmlspecialchars($row['nom']),
                'prix' => number_format($row['prix'], 2),
                'artisan' => htmlspecialchars($row['artisan'] ?? 'Artisan inconnu')
            ];
        }
        mysqli_stmt_close($stmt);
    }

    // Recherche dans les artisans (via la table Artisan et Utilisateur)
    $sql = "SELECT u.idUtilisateur as id, 
                   CONCAT(u.prenom, ' ', u.nom) as nom,
                   a.specialite
            FROM Utilisateur u 
            INNER JOIN Artisan a ON u.idUtilisateur = a.idArtisan
            WHERE (u.nom LIKE ? 
                   OR u.prenom LIKE ? 
                   OR a.specialite LIKE ?
                   OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)
            ORDER BY u.nom 
            LIMIT 5";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $results['artisans'][] = [
                'id' => $row['id'],
                'nom' => htmlspecialchars($row['nom']),
                'specialite' => htmlspecialchars($row['specialite'] ?? 'Spécialité non définie')
            ];
        }
        mysqli_stmt_close($stmt);
    }

    // Recherche dans les événements (avec les bons noms de colonnes)
    $sql = "SELECT e.idEvenement as id, e.nomEvenement as nom, 
                   DATE_FORMAT(e.dateDebut, '%d/%m/%Y') as date
            FROM Evenement e 
            WHERE e.nomEvenement LIKE ? 
               OR e.description LIKE ?
               OR e.lieu LIKE ?
            ORDER BY e.dateDebut 
            LIMIT 5";
    
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $results['evenements'][] = [
                'id' => $row['id'],
                'nom' => htmlspecialchars($row['nom']),
                'date' => $row['date']
            ];
        }
        mysqli_stmt_close($stmt);
    }

} catch (Exception $e) {
    error_log("Erreur de recherche: " . $e->getMessage());
    echo json_encode(['error' => 'Erreur de base de données: ' . $e->getMessage()]);
    exit;
}

echo json_encode($results);
?>