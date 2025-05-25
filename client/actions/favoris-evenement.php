<?php
session_start();
require_once '../includes/conn.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    http_response_code(401);
    echo json_encode([
        'success' => false, 
        'message' => 'Vous devez être connecté en tant que client pour gérer vos favoris',
        'redirect' => 'connexion.php'
    ]);
    exit;
}

// Lire les données JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validation des données d'entrée
if (!isset($input['idEvenement']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Données manquantes. Veuillez rafraîchir la page et réessayer.'
    ]);
    exit;
}

$idEvenement = intval($input['idEvenement']);
$action = trim($input['action']);
$idClient = $_SESSION['idUtilisateur'];

// Validation de l'ID événement
if ($idEvenement <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'ID d\'événement invalide'
    ]);
    exit;
}

// Validation de l'action
if (!in_array($action, ['ajouter', 'retirer'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Action non valide'
    ]);
    exit;
}

try {
    // Commencer une transaction pour assurer la cohérence
    $conn->begin_transaction();

    // Vérifier si l'événement existe
    $sql_event = "SELECT e.*, u.nom as artisan_nom, u.prenom as artisan_prenom
                  FROM Evenement e
                  LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
                  LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                  WHERE e.idEvenement = ?";
    
    $stmt_event = $conn->prepare($sql_event);
    $stmt_event->bind_param("i", $idEvenement);
    $stmt_event->execute();
    $result_event = $stmt_event->get_result();

    if ($result_event->num_rows === 0) {
        throw new Exception('Événement introuvable');
    }

    $event = $result_event->fetch_assoc();

    // Vérifier l'état actuel des favoris
    $sql_check = "SELECT * FROM favoris_evenements WHERE idClient = ? AND idEvenement = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $idClient, $idEvenement);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    $isCurrentlyFavorite = $result_check->num_rows > 0;

    if ($action === 'ajouter') {
        // Logique d'ajout aux favoris
        if ($isCurrentlyFavorite) {
            throw new Exception('Cet événement est déjà dans vos favoris');
        }

        // Ajouter aux favoris
        $sql_insert = "INSERT INTO favoris_evenements (idClient, idEvenement, date_ajout) VALUES (?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $idClient, $idEvenement);

        if (!$stmt_insert->execute()) {
            throw new Exception('Erreur lors de l\'ajout aux favoris');
        }

        // Log de l'action
        logAction($idClient, 'favori_ajoute', $idEvenement, 'Ajout aux favoris: ' . $event['nomEvenement']);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Événement ajouté à vos favoris !',
            'action' => 'ajouter',
            'event_id' => $idEvenement,
            'is_favorite' => true
        ]);

    } elseif ($action === 'retirer') {
        // Logique de retrait des favoris
        if (!$isCurrentlyFavorite) {
            throw new Exception('Cet événement n\'est pas dans vos favoris');
        }

        // Retirer des favoris
        $sql_delete = "DELETE FROM favoris_evenements WHERE idClient = ? AND idEvenement = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $idClient, $idEvenement);

        if (!$stmt_delete->execute()) {
            throw new Exception('Erreur lors du retrait des favoris');
        }

        // Log de l'action
        logAction($idClient, 'favori_retire', $idEvenement, 'Retrait des favoris: ' . $event['nomEvenement']);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Événement retiré de vos favoris',
            'action' => 'retirer',
            'event_id' => $idEvenement,
            'is_favorite' => false
        ]);
    }

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollback();
    
    // Log de l'erreur
    error_log("Erreur favoris événement - Client: $idClient, Événement: $idEvenement, Action: $action, Erreur: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'FAVORIS_ERROR'
    ]);
} finally {
    // Fermer les statements
    if (isset($stmt_event)) $stmt_event->close();
    if (isset($stmt_check)) $stmt_check->close();
    if (isset($stmt_insert)) $stmt_insert->close();
    if (isset($stmt_delete)) $stmt_delete->close();
}

// Fonction pour logger les actions
function logAction($userId, $action, $eventId, $description) {
    global $conn;
    
    $sql = "INSERT INTO log_actions (user_id, action_type, event_id, description, ip_address, user_agent, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $stmt->bind_param("isisss", $userId, $action, $eventId, $description, $ip, $userAgent);
        $stmt->execute();
        $stmt->close();
    }
}
?>