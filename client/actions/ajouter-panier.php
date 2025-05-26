<?php
session_start();
require_once '../includes/conn.php';

header('Content-Type: application/json');

// Debug - ajouter des logs
error_log("DEBUG: Tentative d'ajout au panier");
error_log("DEBUG: Session: " . print_r($_SESSION, true));

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    error_log("DEBUG: Utilisateur non connecté ou pas client");
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté en tant que client']);
    exit;
}

// Lire les données JSON
$input = json_decode(file_get_contents('php://input'), true);
error_log("DEBUG: Input reçu: " . print_r($input, true));

if (!isset($input['idOeuvre'])) {
    error_log("DEBUG: ID œuvre manquant");
    echo json_encode(['success' => false, 'message' => 'ID de l\'œuvre manquant']);
    exit;
}

$idOeuvre = intval($input['idOeuvre']);
$idClient = $_SESSION['idUtilisateur'];

error_log("DEBUG: ID Client: $idClient, ID Œuvre: $idOeuvre");

try {
    // Vérifier si l'œuvre existe et est disponible
    $sql_check = "SELECT o.*, u.nom, u.prenom FROM Oeuvre o 
                  LEFT JOIN Artisan a ON o.idArtisan = a.idArtisan
                  LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                  WHERE o.idOeuvre = ? AND o.disponibilite = 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $idOeuvre);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        error_log("DEBUG: Œuvre non trouvée ou indisponible");
        echo json_encode(['success' => false, 'message' => 'Cette œuvre n\'est pas disponible']);
        exit;
    }

    $oeuvre = $result_check->fetch_assoc();
    error_log("DEBUG: Œuvre trouvée: " . $oeuvre['titre']);

    // Vérifier si l'œuvre n'est pas déjà dans le panier
    $sql_exists = "SELECT * FROM Commande WHERE idClient = ? AND idOeuvre = ? AND statut = 'En attente'";
    $stmt_exists = $conn->prepare($sql_exists);
    $stmt_exists->bind_param("ii", $idClient, $idOeuvre);
    $stmt_exists->execute();
    $result_exists = $stmt_exists->get_result();

    if ($result_exists->num_rows > 0) {
        error_log("DEBUG: Œuvre déjà dans le panier");
        echo json_encode(['success' => false, 'message' => 'Cette œuvre est déjà dans votre panier']);
        exit;
    }

    // Ajouter au panier (créer une commande en attente)
    $sql_insert = "INSERT INTO Commande (idClient, idOeuvre, nombreArticles, statut, dateCommande) 
                   VALUES (?, ?, 1, 'En attente', NOW())";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $idClient, $idOeuvre);

    if ($stmt_insert->execute()) {
        error_log("DEBUG: Œuvre ajoutée avec succès");
        
        // Récupérer le nouveau nombre d'articles dans le panier
        $sql_count = "SELECT COUNT(*) as total FROM Commande WHERE idClient = ? AND statut = 'En attente'";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $idClient);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $count_row = $result_count->fetch_assoc();

        echo json_encode([
            'success' => true, 
            'message' => 'Œuvre ajoutée au panier avec succès !',
            'cart_count' => $count_row['total']
        ]);
    } else {
        error_log("DEBUG: Erreur SQL lors de l'insertion: " . $conn->error);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout au panier: ' . $conn->error]);
    }

} catch (Exception $e) {
    error_log("DEBUG: Exception: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>