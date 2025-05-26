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

if (!isset($input['idCommande']) || !isset($input['quantite'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$idClient = $_SESSION['idUtilisateur'];
$idCommande = intval($input['idCommande']);
$quantite = intval($input['quantite']);

// Validation
if ($idCommande <= 0 || $quantite <= 0 || $quantite > 10) {
    echo json_encode(['success' => false, 'message' => 'Données invalides']);
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
    
    // Mettre à jour la quantité
    $updateQuery = "UPDATE Commande SET nombreArticles = ? WHERE idCommande = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("ii", $quantite, $idCommande);
    
    if ($updateStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantité mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}

$conn->close();
?>