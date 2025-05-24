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

if (!isset($input['idCommande']) || !isset($input['nouvelle_quantite'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$idCommande = intval($input['idCommande']);
$nouvelleQuantite = intval($input['nouvelle_quantite']);
$idClient = $_SESSION['idUtilisateur'];

// Validation de la quantité
if ($nouvelleQuantite < 1 || $nouvelleQuantite > 10) {
    echo json_encode(['success' => false, 'message' => 'Quantité invalide (1-10)']);
    exit;
}

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

    // Mettre à jour la quantité
    $sql_update = "UPDATE Commande SET nombreArticles = ? WHERE idCommande = ? AND idClient = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("iii", $nouvelleQuantite, $idCommande, $idClient);

    if ($stmt_update->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quantité mise à jour']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>