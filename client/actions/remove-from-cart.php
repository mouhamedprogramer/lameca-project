<?php
session_start();
require_once '../includes/conn.php';

header('Content-Type: application/json');

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    echo json_encode(['success' => false, 'message' => 'Vous devez être connecté en tant que client']);
    exit;
}

// Lire les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['idCommande'])) {
    echo json_encode(['success' => false, 'message' => 'ID de commande manquant']);
    exit;
}

$idCommande = intval($input['idCommande']);
$idClient = $_SESSION['idUtilisateur'];

try {
    // Vérifier que la commande appartient bien au client connecté
    $sql_check = "SELECT * FROM Commande WHERE idCommande = ? AND idClient = ? AND statut = 'En attente'";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $idCommande, $idClient);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Commande introuvable']);
        exit;
    }

    // Supprimer la commande
    $sql_delete = "DELETE FROM Commande WHERE idCommande = ? AND idClient = ? AND statut = 'En attente'";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("ii", $idCommande, $idClient);

    if ($stmt_delete->execute()) {
        echo json_encode(['success' => true, 'message' => 'Article supprimé du panier']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la suppression']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>