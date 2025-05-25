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
        'is_in_wishlist' => false,
        'message' => 'Non connecté'
    ]);
    exit;
}

// Récupérer l'ID de l'œuvre
$oeuvreId = isset($_GET['oeuvreId']) ? intval($_GET['oeuvreId']) : 0;

if ($oeuvreId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'ID d\'œuvre invalide'
    ]);
    exit;
}

$idClient = $_SESSION['idUtilisateur'];

try {
    // Vérifier si la table wishlist existe
    $result = $conn->query("SHOW TABLES LIKE 'wishlist'");
    if ($result->num_rows == 0) {
        // La table n'existe pas encore
        echo json_encode([
            'success' => true,
            'is_in_wishlist' => false,
            'message' => 'Table wishlist non initialisée'
        ]);
        exit;
    }

    // Vérifier si l'œuvre est dans la wishlist
    $sql = "SELECT id FROM wishlist WHERE idClient = ? AND idOeuvre = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idClient, $oeuvreId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $isInWishlist = $result->num_rows > 0;
    
    echo json_encode([
        'success' => true,
        'is_in_wishlist' => $isInWishlist
    ]);

} catch (Exception $e) {
    error_log("Erreur check wishlist: " . $e->getMessage());
    echo json_encode([
        'success' => true,
        'is_in_wishlist' => false,
        'message' => 'Erreur lors de la vérification'
    ]);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
}
?>