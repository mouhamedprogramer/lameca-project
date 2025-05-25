<?php
session_start();
require_once '../includes/conn.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    echo json_encode([
        'success' => true, 
        'is_favorite' => false,
        'message' => 'Non connecté'
    ]);
    exit;
}

// Récupérer l'ID de l'événement
$eventId = isset($_GET['eventId']) ? intval($_GET['eventId']) : 0;

if ($eventId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID d\'événement invalide'
    ]);
    exit;
}

$idClient = $_SESSION['idUtilisateur'];

try {
    // Vérifier si la table favoris_evenements existe
    $result = $conn->query("SHOW TABLES LIKE 'favoris_evenements'");
    if ($result->num_rows == 0) {
        // La table n'existe pas encore
        echo json_encode([
            'success' => true,
            'is_favorite' => false,
            'message' => 'Table favoris non initialisée'
        ]);
        exit;
    }

    // Vérifier si l'événement est dans les favoris
    $sql = "SELECT id FROM favoris_evenements WHERE idClient = ? AND idEvenement = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idClient, $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $isFavorite = $result->num_rows > 0;
    
    echo json_encode([
        'success' => true,
        'is_favorite' => $isFavorite
    ]);

} catch (Exception $e) {
    error_log("Erreur check favoris: " . $e->getMessage());
    echo json_encode([
        'success' => true,
        'is_favorite' => false,
        'message' => 'Erreur lors de la vérification'
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>