<?php
include 'includes/session.php';

if(isset($_POST['send_email'])){
    $id = $_POST['id'];
    $email_to = $_POST['email_to'];
    $subject = $_POST['email_subject'];
    $message = $_POST['email_message'];
    $template = $_POST['email_template'];
    
    // Récupérer les informations du client pour personnaliser le message
    $sql = "SELECT prenom, nom FROM utilisateur WHERE idUtilisateur = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $client = $result->fetch_assoc();
    
    // Si un modèle est sélectionné, remplacer le message
    if(!empty($template)){
        switch($template){
            case 'welcome':
                $message = "Bonjour " . $client['prenom'] . ",\n\n";
                $message .= "Nous sommes ravis de vous accueillir sur notre plateforme d'artisanat d'art.\n\n";
                $message .= "N'hésitez pas à parcourir notre catalogue d'œuvres uniques créées par nos artisans talentueux.\n\n";
                $message .= "Si vous avez des questions, notre équipe est à votre disposition.\n\n";
                $message .= "Cordialement,\nL'équipe Lameca";
                $subject = "Bienvenue chez Lameca !";
                break;
                
            case 'promo':
                $message = "Cher(e) " . $client['prenom'] . ",\n\n";
                $message .= "Nous avons le plaisir de vous annoncer notre promotion spéciale du moment :\n\n";
                $message .= "15% de réduction sur toutes nos œuvres d'art avec le code PROMO15\n\n";
                $message .= "Cette offre est valable jusqu'au " . date('d/m/Y', strtotime('+7 days')) . ".\n\n";
                $message .= "Profitez-en vite !\n\n";
                $message .= "L'équipe Lameca";
                $subject = "Promotion spéciale pour vous !";
                break;
                
            case 'reminder':
                $message = "Bonjour " . $client['prenom'] . ",\n\n";
                $message .= "Nous avons remarqué que vous avez récemment consulté nos œuvres d'art mais n'avez pas finalisé votre commande.\n\n";
                $message .= "Les pièces que vous avez sélectionnées sont toujours disponibles, et nous serions ravis de vous aider si vous avez des questions.\n\n";
                $message .= "N'hésitez pas à nous contacter ou à revenir sur notre site pour finaliser votre choix.\n\n";
                $message .= "Cordialement,\nL'équipe Lameca";
                $subject = "Votre panier vous attend !";
                break;
                
            case 'feedback':
                $message = "Cher(e) " . $client['prenom'] . ",\n\n";
                $message .= "Nous espérons que vous êtes satisfait(e) de votre expérience avec Lameca.\n\n";
                $message .= "Votre avis est très important pour nous aider à améliorer nos services. Pourriez-vous prendre quelques minutes pour répondre à notre court questionnaire de satisfaction ?\n\n";
                $message .= "Lien vers le questionnaire : [URL_QUESTIONNAIRE]\n\n";
                $message .= "Merci pour votre contribution !\n\n";
                $message .= "L'équipe Lameca";
                $subject = "Votre avis nous intéresse !";
                break;
        }
    }
    
    // En environnement de production, vous utiliseriez une bibliothèque comme PHPMailer
    // Ici, nous allons simuler l'envoi d'email et enregistrer l'action dans la base de données
    
    // Enregistrer l'email dans la base de données (historique de communication)
    $sql = "INSERT INTO communication (idClient, type, sujet, contenu, date_envoi) VALUES (?, 'email', ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $id, $subject, $message);
    
    if($stmt->execute()){
        $_SESSION['success'] = 'Email envoyé avec succès à ' . $client['prenom'] . ' ' . $client['nom'];
    }
    else{
        $_SESSION['error'] = 'Erreur lors de l\'envoi de l\'email';
    }
}
else{
    $_SESSION['error'] = 'Veuillez remplir le formulaire d\'envoi d\'email';
}

header('location: clients.php');
?>