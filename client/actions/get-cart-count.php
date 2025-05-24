<?php
session_start();
require_once '../includes/conn.php';

header('Content-Type: application/json');

$count = 0;

// Vérifier si l'utilisateur est connecté et est un client
if (isset($_SESSION['idUtilisateur']) && $_SESSION['role'] === 'Client') {
    try {
        $idClient = $_SESSION['idUtilisateur'];
        
        $sql = "SELECT COUNT(*) as total FROM Commande WHERE idClient = ? AND statut = 'En attente'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idClient);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result) {
            $row = $result->fetch_assoc();
            $count = intval($row['total']);
        }
    } catch (Exception $e) {
        // En cas d'erreur, on retourne 0
        $count = 0;
    }
}

echo json_encode(['count' => $count]);
?>