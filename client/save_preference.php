<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUtilisateur'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit();
}

// Lire les données JSON
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['preference']) || !isset($input['value'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit();
}

$idUtilisateur = $_SESSION['idUtilisateur'];
$preference = $input['preference'];
$value = $input['value'] ? 1 : 0; // Convertir boolean en int

try {
    // Vérifier si la table preference_client existe, sinon la créer
    $checkTable = "SHOW TABLES LIKE 'preference_client'";
    $result = mysqli_query($conn, $checkTable);
    
    if (mysqli_num_rows($result) == 0) {
        // Créer la table si elle n'existe pas
        $createTable = "
            CREATE TABLE preference_client (
                idPreference INT PRIMARY KEY AUTO_INCREMENT,
                idClient INT NOT NULL,
                recevoir_newsletter BOOLEAN DEFAULT TRUE,
                recevoir_promotions BOOLEAN DEFAULT TRUE,
                recevoir_notifications BOOLEAN DEFAULT TRUE,
                theme_prefere VARCHAR(100) DEFAULT 'light',
                langue VARCHAR(10) DEFAULT 'fr',
                derniere_connexion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (idClient) REFERENCES Utilisateur(idUtilisateur) ON DELETE CASCADE,
                UNIQUE KEY unique_client (idClient)
            )
        ";
        
        if (!mysqli_query($conn, $createTable)) {
            throw new Exception("Erreur lors de la création de la table preferences");
        }
    }
    
    // Mapper les noms de préférences
    $preferenceMapping = [
        'Notifications par email' => 'recevoir_notifications',
        'Newsletter' => 'recevoir_newsletter', 
        'Promotions' => 'recevoir_promotions'
    ];
    
    $dbField = $preferenceMapping[$preference] ?? null;
    
    if (!$dbField) {
        echo json_encode(['success' => false, 'message' => 'Préférence non reconnue']);
        exit();
    }
    
    // Vérifier si une ligne existe déjà pour cet utilisateur
    $checkQuery = "SELECT idPreference FROM preference_client WHERE idClient = ?";
    $stmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        // Mettre à jour la préférence existante
        $updateQuery = "UPDATE preference_client SET {$dbField} = ?, derniere_connexion = CURRENT_TIMESTAMP WHERE idClient = ?";
        $stmt = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt, "ii", $value, $idUtilisateur);
    } else {
        // Créer une nouvelle ligne de préférences
        $insertQuery = "INSERT INTO preference_client (idClient, {$dbField}) VALUES (?, ?)";
        $stmt = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt, "ii", $idUtilisateur, $value);
    }
    
    if (mysqli_stmt_execute($stmt)) {
        // Log de l'action (optionnel)
        $logQuery = "INSERT INTO log_actions (user_id, action_type, description, ip_address) VALUES (?, 'preference_update', ?, ?)";
        $logStmt = mysqli_prepare($conn, $logQuery);
        $description = "Modification préférence: {$preference} = " . ($value ? 'activée' : 'désactivée');
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        mysqli_stmt_bind_param($logStmt, "iss", $idUtilisateur, $description, $ipAddress);
        mysqli_stmt_execute($logStmt);
        
        echo json_encode([
            'success' => true, 
            'message' => 'Préférence sauvegardée avec succès',
            'preference' => $preference,
            'value' => (bool)$value
        ]);
    } else {
        throw new Exception("Erreur lors de la sauvegarde");
    }
    
} catch (Exception $e) {
    error_log("Erreur sauvegarde préférence: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
?>