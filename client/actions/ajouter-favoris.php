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

if (!isset($input['idOeuvre']) || !isset($input['action'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$idOeuvre = intval($input['idOeuvre']);
$action = $input['action'];
$idClient = $_SESSION['idUtilisateur'];

try {
    if ($action === 'ajouter') {
        // Vérifier si l'œuvre n'est pas déjà dans les favoris
        $sql_check = "SELECT * FROM Aimer WHERE idClient = ? AND idOeuvre = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ii", $idClient, $idOeuvre);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Cette œuvre est déjà dans vos favoris']);
            exit;
        }

        // Ajouter aux favoris
        $sql_insert = "INSERT INTO Aimer (idClient, idOeuvre) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $idClient, $idOeuvre);

        if ($stmt_insert->execute()) {
            echo json_encode(['success' => true, 'message' => 'Œuvre ajoutée aux favoris']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'ajout aux favoris']);
        }

    } elseif ($action === 'retirer') {
        // Retirer des favoris
        $sql_delete = "DELETE FROM Aimer WHERE idClient = ? AND idOeuvre = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $idClient, $idOeuvre);

        if ($stmt_delete->execute()) {
            echo json_encode(['success' => true, 'message' => 'Œuvre retirée des favoris']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erreur lors du retrait des favoris']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Action non valide']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}
?>