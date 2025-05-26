<?php
session_start();
require_once '../includes/conn.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// Lire les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['idCommande'])) {
    echo json_encode(['success' => false, 'message' => 'ID commande manquant']);
    exit;
}

$idClient = $_SESSION['idUtilisateur'];
$idCommande = intval($input['idCommande']);

if ($idCommande <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID commande invalide']);
    exit;
}

try {
    // Vérifier que la commande appartient bien au client
    $checkQuery = "SELECT idCommande FROM Commande 
                   WHERE idCommande = ? AND idClient = ? AND statut = 'En attente'";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("ii", $idCommande, $idClient);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Commande non trouvée']);
        exit;
    }
    
    // Supprimer la commande
    $deleteQuery = "DELETE FROM Commande WHERE idCommande = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $idCommande);
    
    if ($deleteStmt->execute()) {
        // Récupérer le nouveau nombre d'articles dans le panier
        $countQuery = "SELECT COUNT(*) as total FROM Commande WHERE idClient = ? AND statut = 'En attente'";
        $countStmt = $conn->prepare($countQuery);
        $countStmt->bind_param("i", $idClient);
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $cartCount = $countResult->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Article supprimé du panier',
            'cart_count' => $cartCount
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}

$conn->close();
?>