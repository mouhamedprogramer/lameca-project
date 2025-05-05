<?php
include 'includes/session.php';

// Ajouter ces lignes pour utiliser PHPMailer (version moderne)
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

if(isset($_POST['send_email'])) {
    $client_id = $_POST['client_id'];
    $client_name = $_POST['client_name'];
    $email_to = $_POST['email_to'];
    $subject = $_POST['email_subject'];
    $message = $_POST['email_message'];
    $template = $_POST['email_template'];
    
    // Vérifier si l'adresse email est valide
    if (empty($email_to) || !filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Adresse email non valide: ' . $email_to;
        header('location: clients.php');
        exit();
    }
    
    // Si un modèle est sélectionné, générer le contenu du message
    if(!empty($template)) {
        // Récupérer les informations du client
        $sql = "SELECT u.prenom, u.nom FROM client c 
                JOIN utilisateur u ON c.idClient = u.idUtilisateur 
                WHERE c.idClient = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $client_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $client = $result->fetch_assoc();
        
        switch($template) {
            case 'welcome':
                $subject = "Bienvenue chez Lameca !";
                $message = "Bonjour ".$client['prenom'].",\n\n";
                $message .= "Nous sommes ravis de vous accueillir sur notre plateforme d'artisanat d'art.\n\n";
                $message .= "N'hésitez pas à parcourir notre catalogue d'œuvres uniques créées par nos artisans talentueux.\n\n";
                $message .= "Si vous avez des questions, notre équipe est à votre disposition.\n\n";
                $message .= "Cordialement,\nL'équipe Lameca";
                break;
                
            case 'promo':
                $subject = "Offre spéciale pour vous !";
                $message = "Cher(e) ".$client['prenom'].",\n\n";
                $message .= "Nous avons le plaisir de vous annoncer notre promotion spéciale du moment :\n\n";
                $message .= "15% de réduction sur toutes nos œuvres d'art avec le code PROMO15\n\n";
                $message .= "Cette offre est valable jusqu'au ".date('d/m/Y', strtotime('+7 days')).".\n\n";
                $message .= "Profitez-en vite !\n\n";
                $message .= "L'équipe Lameca";
                break;
                
            case 'reminder':
                $subject = "Votre panier vous attend !";
                $message = "Bonjour ".$client['prenom'].",\n\n";
                $message .= "Nous avons remarqué que vous avez récemment consulté nos œuvres d'art mais n'avez pas finalisé votre commande.\n\n";
                $message .= "Les pièces que vous avez sélectionnées sont toujours disponibles, et nous serions ravis de vous aider si vous avez des questions.\n\n";
                $message .= "N'hésitez pas à nous contacter ou à revenir sur notre site pour finaliser votre choix.\n\n";
                $message .= "Cordialement,\nL'équipe Lameca";
                break;
                
            case 'feedback':
                $subject = "Votre avis nous intéresse !";
                $message = "Cher(e) ".$client['prenom'].",\n\n";
                $message .= "Nous espérons que vous êtes satisfait(e) de votre expérience avec Lameca.\n\n";
                $message .= "Votre avis est très important pour nous aider à améliorer nos services. Pourriez-vous prendre quelques minutes pour répondre à notre court questionnaire de satisfaction ?\n\n";
                $message .= "Lien vers le questionnaire : [URL_QUESTIONNAIRE]\n\n";
                $message .= "Merci pour votre contribution !\n\n";
                $message .= "L'équipe Lameca";
                break;
        }
    }
    
    // Vérifier si la table communication existe
    $result = $conn->query("SHOW TABLES LIKE 'communication'");
    if($result->num_rows == 0) {
        // Créer la table si elle n'existe pas
        $conn->query("CREATE TABLE communication (
            idCommunication INT PRIMARY KEY AUTO_INCREMENT,
            idClient INT NOT NULL,
            type ENUM('email', 'sms', 'appel', 'autre') NOT NULL,
            sujet VARCHAR(255) NOT NULL,
            contenu TEXT NOT NULL,
            date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (idClient) REFERENCES client(idClient) ON DELETE CASCADE
        )");
    }
    
    // Enregistrer l'email dans la base de données
    $sql = "INSERT INTO communication (idClient, type, sujet, contenu, date_envoi) 
            VALUES (?, 'email', ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $client_id, $subject, $message);
    
    if($stmt->execute()) {
        // Afficher des informations pour le débogage (à retirer en production)
        echo "Debug: Envoi de l'email à " . $email_to . "<br>";
        
        // Utilisation de PHPMailer pour envoyer l'email
        try {
            // Créer une instance de PHPMailer
            $mail = new PHPMailer(true); // true active les exceptions

            // Activer le mode débogage (à retirer en production)
            $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Options: DEBUG_OFF, DEBUG_CLIENT, DEBUG_SERVER

            // Configuration du serveur
            $mail->isSMTP();                                      // Utilisation de SMTP
            $mail->Host       = 'smtp.gmail.com';                 // Serveur SMTP de Gmail
            $mail->SMTPAuth   = true;                             // Activer l'authentification SMTP
            $mail->Username   = 'mouhamedprogramer@gmail.com';    // Votre adresse email
            $mail->Password   = 'gokm lnuk hsoi gxor';            // Votre mot de passe d'application
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // Activer le chiffrement TLS
            $mail->Port       = 587;                              // Port TCP pour la connexion

            // Destinataires
            $mail->setFrom('mouhamedprogramer@gmail.com', 'Lameca');
            
            // Vérifier une dernière fois l'email avant d'ajouter le destinataire
            if (filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
                $mail->addAddress($email_to, $client_name);          // Ajouter un destinataire
            } else {
                throw new Exception("Adresse email non valide: " . $email_to);
            }

            // Contenu
            $mail->isHTML(false);                                  // Format email en texte brut
            $mail->Subject = $subject;
            $mail->Body    = $message;

            // Envoyer l'email
            $mail->send();
            $_SESSION['success'] = 'Email envoyé avec succès à ' . $email_to;
        } catch (Exception $e) {
            $_SESSION['error'] = "L'email n'a pas pu être envoyé. Erreur: " . $mail->ErrorInfo;
        }
    } else {
        $_SESSION['error'] = 'Erreur lors de l\'enregistrement de l\'email: ' . $conn->error;
    }
} else {
    $_SESSION['error'] = 'Formulaire incomplet';
}

// Redirection classique
header('location: clients.php');
exit();
?>