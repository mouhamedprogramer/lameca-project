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
        'message' => 'Vous devez être connecté en tant que client pour gérer votre liste de souhaits',
        'redirect' => 'connexion.php'
    ]);
    exit;
}

// Lire les données JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validation des données d'entrée
if (!isset($input['idOeuvre']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Données manquantes. Veuillez réessayer.'
    ]);
    exit;
}

$idOeuvre = intval($input['idOeuvre']);
$action = trim($input['action']);
$idClient = $_SESSION['idUtilisateur'];

// Validation de l'ID œuvre
if ($idOeuvre <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'ID d\'œuvre invalide'
    ]);
    exit;
}

// Validation de l'action
if (!in_array($action, ['add', 'remove', 'ajouter', 'retirer'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Action non valide'
    ]);
    exit;
}

// Normaliser les actions
if ($action === 'ajouter') $action = 'add';
if ($action === 'retirer') $action = 'remove';

try {
    // DEBUG - Ajoutez ces lignes temporairement
    error_log("DEBUG WISHLIST - Client: $idClient, Œuvre: $idOeuvre, Action: $action");
    
    // Vérifier si la table wishlist existe
    $result = $conn->query("SHOW TABLES LIKE 'wishlist'");
    if ($result->num_rows == 0) {
        throw new Exception('La fonctionnalité liste de souhaits n\'est pas encore disponible');
    }

    // Commencer une transaction pour assurer la cohérence
    $conn->begin_transaction();

    // Vérifier si l'œuvre existe et est disponible
    $sql_oeuvre = "SELECT o.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom
                   FROM Oeuvre o
                   JOIN Artisan a ON o.idArtisan = a.idArtisan
                   JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                   WHERE o.idOeuvre = ? AND o.disponibilite = TRUE";
    
    $stmt_oeuvre = $conn->prepare($sql_oeuvre);
    $stmt_oeuvre->bind_param("i", $idOeuvre);
    $stmt_oeuvre->execute();
    $result_oeuvre = $stmt_oeuvre->get_result();

    if ($result_oeuvre->num_rows === 0) {
        throw new Exception('Œuvre introuvable ou non disponible');
    }

    $oeuvre = $result_oeuvre->fetch_assoc();

    // Vérifier l'état actuel dans la wishlist
    $sql_check = "SELECT * FROM wishlist WHERE idClient = ? AND idOeuvre = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $idClient, $idOeuvre);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    $isCurrentlyInWishlist = $result_check->num_rows > 0;

    if ($action === 'add') {
        // Logique d'ajout à la wishlist
        if ($isCurrentlyInWishlist) {
            throw new Exception('Cette œuvre est déjà dans votre liste de souhaits');
        }

        // Ajouter à la wishlist
        $sql_insert = "INSERT INTO wishlist (idClient, idOeuvre, date_ajout) VALUES (?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $idClient, $idOeuvre);

        if (!$stmt_insert->execute()) {
            throw new Exception('Erreur lors de l\'ajout à la liste de souhaits');
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Œuvre ajoutée à votre liste de souhaits !',
            'action' => 'add',
            'oeuvre_id' => $idOeuvre,
            'is_in_wishlist' => true
        ]);

    } elseif ($action === 'remove') {
        // Logique de retrait de la wishlist
        if (!$isCurrentlyInWishlist) {
            throw new Exception('Cette œuvre n\'est pas dans votre liste de souhaits');
        }

        // Supprimer de la wishlist
        $sql_delete = "DELETE FROM wishlist WHERE idClient = ? AND idOeuvre = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $idClient, $idOeuvre);

        if (!$stmt_delete->execute()) {
            throw new Exception('Erreur lors du retrait de la liste de souhaits');
        }

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Œuvre retirée de votre liste de souhaits',
            'action' => 'remove',
            'oeuvre_id' => $idOeuvre,
            'is_in_wishlist' => false
        ]);
    }

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollback();
    
    // Log de l'erreur
    error_log("Erreur wishlist - Client: $idClient, Œuvre: $idOeuvre, Action: $action, Erreur: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'WISHLIST_ERROR'
    ]);
} finally {
    // Fermer les statements
    if (isset($stmt_oeuvre)) $stmt_oeuvre->close();
    if (isset($stmt_check)) $stmt_check->close();
    if (isset($stmt_insert)) $stmt_insert->close();
    if (isset($stmt_delete)) $stmt_delete->close();
}
?>