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
        'message' => 'Vous devez être connecté en tant que client pour participer à un événement',
        'redirect' => 'connexion.php'
    ]);
    exit;
}

// Lire les données JSON
$input = json_decode(file_get_contents('php://input'), true);

// Validation des données d'entrée
if (!isset($input['idEvenement']) || !isset($input['action'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Données manquantes. Veuillez rafraîchir la page et réessayer.'
    ]);
    exit;
}

$idEvenement = intval($input['idEvenement']);
$action = trim($input['action']);
$idClient = $_SESSION['idUtilisateur'];

// Validation de l'ID événement
if ($idEvenement <= 0) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'ID d\'événement invalide'
    ]);
    exit;
}

// Validation de l'action
if (!in_array($action, ['participer', 'desinscrire'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Action non valide'
    ]);
    exit;
}

try {
    // Commencer une transaction pour assurer la cohérence
    $conn->begin_transaction();

    // Vérifier si l'événement existe et récupérer ses détails
    $sql_event = "SELECT e.*, u.nom as artisan_nom, u.prenom as artisan_prenom, u.email as artisan_email
                  FROM Evenement e
                  LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
                  LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                  WHERE e.idEvenement = ?";
    
    $stmt_event = $conn->prepare($sql_event);
    $stmt_event->bind_param("i", $idEvenement);
    $stmt_event->execute();
    $result_event = $stmt_event->get_result();

    if ($result_event->num_rows === 0) {
        throw new Exception('Événement introuvable');
    }

    $event = $result_event->fetch_assoc();

    // Vérifier si l'événement n'est pas déjà passé
    $currentDate = date('Y-m-d');
    if ($event['dateDebut'] < $currentDate && $action === 'participer') {
        throw new Exception('Impossible de s\'inscrire à un événement passé');
    }

    // Vérifier l'état actuel de participation
    $sql_check = "SELECT * FROM Clientevenement WHERE idClient = ? AND idEvenement = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $idClient, $idEvenement);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    $isCurrentlyParticipating = $result_check->num_rows > 0;

    if ($action === 'participer') {
        // Logique de participation
        if ($isCurrentlyParticipating) {
            throw new Exception('Vous participez déjà à cet événement');
        }

        // Récupérer les paramètres de l'événement s'ils existent
        $limite_participants = 100; // Valeur par défaut
        $sql_settings = "SELECT max_participants FROM evenement_settings WHERE idEvenement = ?";
        $stmt_settings = $conn->prepare($sql_settings);
        if ($stmt_settings) {
            $stmt_settings->bind_param("i", $idEvenement);
            $stmt_settings->execute();
            $result_settings = $stmt_settings->get_result();
            if ($result_settings->num_rows > 0) {
                $settings = $result_settings->fetch_assoc();
                $limite_participants = $settings['max_participants'];
            }
            $stmt_settings->close();
        }

        // Vérifier s'il y a une limite de participants
        $sql_count = "SELECT COUNT(*) as nb_participants FROM Clientevenement WHERE idEvenement = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $idEvenement);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $count_data = $result_count->fetch_assoc();
        
        if ($count_data['nb_participants'] >= $limite_participants) {
            throw new Exception('Cet événement est complet. Plus de places disponibles.');
        }

        // Ajouter la participation
        $sql_insert = "INSERT INTO Clientevenement (idClient, idEvenement) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $idClient, $idEvenement);

        if (!$stmt_insert->execute()) {
            throw new Exception('Erreur lors de l\'inscription');
        }

        // Récupérer les informations du client pour l'email
        $sql_client = "SELECT nom, prenom, email FROM Utilisateur WHERE idUtilisateur = ?";
        $stmt_client = $conn->prepare($sql_client);
        $stmt_client->bind_param("i", $idClient);
        $stmt_client->execute();
        $client = $stmt_client->get_result()->fetch_assoc();

        // Envoyer un email de confirmation (simulation)
        envoyerEmailConfirmationParticipation($client, $event);

        // Log de l'action (optionnel - ne pas générer d'erreur si la table n'existe pas)
        logAction($idClient, 'participation', $idEvenement, 'Inscription à l\'événement: ' . $event['nomEvenement']);

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Inscription confirmée ! Vous recevrez un email de confirmation.',
            'action' => 'participer',
            'event_id' => $idEvenement,
            'new_count' => $count_data['nb_participants'] + 1
        ]);

    } elseif ($action === 'desinscrire') {
        // Logique de désinscription
        if (!$isCurrentlyParticipating) {
            throw new Exception('Vous ne participez pas à cet événement');
        }

        // Vérifier si l'événement n'a pas déjà commencé (grace period de 2 heures)
        $eventDateTime = strtotime($event['dateDebut']);
        $currentDateTime = time();
        $timeDifference = $eventDateTime - $currentDateTime;
        
        // Récupérer les paramètres de délai d'annulation
        $delai_annulation = 7200; // 2 heures par défaut
        $sql_settings = "SELECT allow_cancellation_hours FROM evenement_settings WHERE idEvenement = ?";
        $stmt_settings = $conn->prepare($sql_settings);
        if ($stmt_settings) {
            $stmt_settings->bind_param("i", $idEvenement);
            $stmt_settings->execute();
            $result_settings = $stmt_settings->get_result();
            if ($result_settings->num_rows > 0) {
                $settings = $result_settings->fetch_assoc();
                $delai_annulation = $settings['allow_cancellation_hours'] * 3600; // Convertir en secondes
            }
            $stmt_settings->close();
        }
        
        if ($timeDifference < $delai_annulation) {
            $heures = $delai_annulation / 3600;
            throw new Exception("Impossible de se désinscrire : l'événement commence dans moins de {$heures} heure(s)");
        }

        // Supprimer la participation
        $sql_delete = "DELETE FROM Clientevenement WHERE idClient = ? AND idEvenement = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $idClient, $idEvenement);

        if (!$stmt_delete->execute()) {
            throw new Exception('Erreur lors de la désinscription');
        }

        // Récupérer les informations du client
        $sql_client = "SELECT nom, prenom, email FROM Utilisateur WHERE idUtilisateur = ?";
        $stmt_client = $conn->prepare($sql_client);
        $stmt_client->bind_param("i", $idClient);
        $stmt_client->execute();
        $client = $stmt_client->get_result()->fetch_assoc();

        // Envoyer un email d'annulation
        envoyerEmailAnnulationParticipation($client, $event);

        // Log de l'action (optionnel)
        logAction($idClient, 'desinscription', $idEvenement, 'Désinscription de l\'événement: ' . $event['nomEvenement']);

        // Récupérer le nouveau nombre de participants
        $sql_count = "SELECT COUNT(*) as nb_participants FROM Clientevenement WHERE idEvenement = ?";
        $stmt_count = $conn->prepare($sql_count);
        $stmt_count->bind_param("i", $idEvenement);
        $stmt_count->execute();
        $result_count = $stmt_count->get_result();
        $count_data = $result_count->fetch_assoc();

        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Désinscription confirmée',
            'action' => 'desinscrire',
            'event_id' => $idEvenement,
            'new_count' => $count_data['nb_participants']
        ]);
    }

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollback();
    
    // Log de l'erreur
    error_log("Erreur participation événement - Client: $idClient, Événement: $idEvenement, Action: $action, Erreur: " . $e->getMessage());
    
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error_code' => 'PARTICIPATION_ERROR'
    ]);
} finally {
    // Fermer les statements
    if (isset($stmt_event)) $stmt_event->close();
    if (isset($stmt_check)) $stmt_check->close();
    if (isset($stmt_insert)) $stmt_insert->close();
    if (isset($stmt_delete)) $stmt_delete->close();
    if (isset($stmt_count)) $stmt_count->close();
    if (isset($stmt_client)) $stmt_client->close();
}

// Fonction pour envoyer l'email de confirmation de participation
function envoyerEmailConfirmationParticipation($client, $event) {
    $to = $client['email'];
    $subject = "Confirmation d'inscription - " . $event['nomEvenement'];
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #4a90e2, #357abd); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; }
            .event-details { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 15px; text-align: center; border-radius: 0 0 10px 10px; }
            .btn { background: #4a90e2; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Inscription confirmée !</h1>
            </div>
            <div class='content'>
                <p>Bonjour " . htmlspecialchars($client['prenom']) . ",</p>
                <p>Votre inscription à l'événement <strong>" . htmlspecialchars($event['nomEvenement']) . "</strong> a été confirmée avec succès.</p>
                
                <div class='event-details'>
                    <h3>📅 Détails de l'événement</h3>
                    <p><strong>Nom :</strong> " . htmlspecialchars($event['nomEvenement']) . "</p>
                    <p><strong>Date :</strong> " . date('d/m/Y', strtotime($event['dateDebut'])) . "</p>
                    <p><strong>Lieu :</strong> " . htmlspecialchars($event['lieu']) . "</p>
                    " . (!empty($event['description']) ? "<p><strong>Description :</strong> " . htmlspecialchars($event['description']) . "</p>" : "") . "
                </div>
                
                <p>Nous vous rappelons que vous pouvez vous désinscrire jusqu'à 2 heures avant le début de l'événement.</p>
                
                <p>À bientôt !</p>
                <p>L'équipe Artisano</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Artisano - Plateforme d'art authentique</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@artisano.com" . "\r\n";
    
    // En développement, on log l'email au lieu de l'envoyer
    if (isDevelopmentMode()) {
        error_log("EMAIL CONFIRMATION PARTICIPATION: To: $to, Subject: $subject");
        return true;
    }
    
    return mail($to, $subject, $message, $headers);
}

// Fonction pour envoyer l'email d'annulation de participation
function envoyerEmailAnnulationParticipation($client, $event) {
    $to = $client['email'];
    $subject = "Annulation d'inscription - " . $event['nomEvenement'];
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; }
            .event-details { background: white; padding: 20px; border-radius: 10px; margin: 20px 0; }
            .footer { background: #333; color: white; padding: 15px; text-align: center; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>😔 Désinscription confirmée</h1>
            </div>
            <div class='content'>
                <p>Bonjour " . htmlspecialchars($client['prenom']) . ",</p>
                <p>Votre désinscription de l'événement <strong>" . htmlspecialchars($event['nomEvenement']) . "</strong> a été prise en compte.</p>
                
                <div class='event-details'>
                    <h3>📅 Événement annulé</h3>
                    <p><strong>Nom :</strong> " . htmlspecialchars($event['nomEvenement']) . "</p>
                    <p><strong>Date :</strong> " . date('d/m/Y', strtotime($event['dateDebut'])) . "</p>
                    <p><strong>Lieu :</strong> " . htmlspecialchars($event['lieu']) . "</p>
                </div>
                
                <p>Nous espérons vous voir lors d'un prochain événement !</p>
                
                <p>L'équipe Artisano</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Artisano - Plateforme d'art authentique</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: noreply@artisano.com" . "\r\n";
    
    if (isDevelopmentMode()) {
        error_log("EMAIL ANNULATION PARTICIPATION: To: $to, Subject: $subject");
        return true;
    }
    
    return mail($to, $subject, $message, $headers);
}

// Fonction pour logger les actions (ne génère pas d'erreur si la table n'existe pas)
function logAction($userId, $action, $eventId, $description) {
    global $conn;
    
    try {
        // Vérifier si la table log_actions existe
        $result = $conn->query("SHOW TABLES LIKE 'log_actions'");
        if ($result->num_rows == 0) {
            // La table n'existe pas, on ignore silencieusement
            return false;
        }
        
        $sql = "INSERT INTO log_actions (user_id, action_type, event_id, description, ip_address, user_agent, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
            
            $stmt->bind_param("isisss", $userId, $action, $eventId, $description, $ip, $userAgent);
            $stmt->execute();
            $stmt->close();
            return true;
        }
    } catch (Exception $e) {
        // Ignorer les erreurs de log pour ne pas casser l'application
        error_log("Erreur lors du log d'action: " . $e->getMessage());
        return false;
    }
    
    return false;
}

// Vérifier si on est en mode développement
function isDevelopmentMode() {
    return in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1']) || 
           strpos($_SERVER['HTTP_HOST'] ?? '', '.local') !== false;
}
?>