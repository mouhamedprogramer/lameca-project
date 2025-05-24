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

if (!isset($input['idOeuvre'])) {
    echo json_encode(['success' => false, 'message' => 'ID de l\'œuvre manquant']);
    exit;
}

$idOeuvre = intval($input['idOeuvre']);
$idClient = $_SESSION['idUtilisateur'];

try {
    // Vérifier si l'œuvre existe et est disponible
    $sql_check = "SELECT * FROM Oeuvre WHERE idOeuvre = ? AND disponibilite = 1";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $idOeuvre);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Cette œuvre n\'est pas disponible']);
        exit;
    }

    // Vérifier si l'œuvre n'est pas déjà dans le panier
    $sql_exists = "SELECT * FROM Commande WHERE idClient = ? AND idOeuvre = ? AND statut = 'En attente'";
    $stmt_exists = $conn->prepare($sql_exists);
    $stmt_exists->bind_param("ii", $idClient, $idOeuvre);
    $stmt_exists->execute();
    $result_exists = $stmt_exists->get_result();

    if ($result_exists->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Cette œuvre est déjà dans votre panier']);
        exit;
    }

    // Ajouter au panier (créer une commande en attente)
    $sql_insert = "INSERT INTO Commande (idClient, idOeuvre, nombreArticles, statut) VALUES (?, ?, 1, 'En attente')";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("ii", $idClient, $idOeuvre);

    if ($stmt_insert->execute()) {
        // Récupérer le nouveau nombre d'articles dans le panier
        $sql_count = "SELECT COUNT(*) as total FROM Commande WHERE idClient = ? AND statut = 'En attente'";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $idClient);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $count_row = $result_count->fetch_assoc();

        echo json_encode([
            'success' => true, 
            'message' => 'Œuvre ajoutée au panier',
            'cart_count' => $count_row['total']
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout au panier']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>