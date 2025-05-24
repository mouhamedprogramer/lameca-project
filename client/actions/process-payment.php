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

if (!isset($input['livraison']) || !isset($input['paiement'])) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$idClient = $_SESSION['idUtilisateur'];
$livraison = $input['livraison'];
$paiement = $input['paiement'];

try {
    // Commencer une transaction
    $conn->begin_transaction();

    // Récupérer les articles du panier
    $sql_panier = "SELECT c.*, o.prix, o.titre FROM Commande c 
                   JOIN Oeuvre o ON c.idOeuvre = o.idOeuvre 
                   WHERE c.idClient = ? AND c.statut = 'En attente'";
    $stmt_panier = $conn->prepare($sql_panier);
    $stmt_panier->bind_param("i", $idClient);
    $stmt_panier->execute();
    $result_panier = $stmt_panier->get_result();

    if ($result_panier->num_rows === 0) {
        throw new Exception('Votre panier est vide');
    }

    $montantTotal = 0;
    $articles = [];
    
    while ($row = $result_panier->fetch_assoc()) {
        $articles[] = $row;
        $montantTotal += $row['prix'] * $row['nombreArticles'];
    }

    // Simuler le traitement du paiement
    $paiementReussi = simulerPaiement($paiement, $montantTotal);
    
    if (!$paiementReussi) {
        throw new Exception('Le paiement a été refusé. Veuillez vérifier vos informations de paiement.');
    }

    // Générer un numéro de commande unique
    $numeroCommande = 'CMD-' . date('Y') . '-' . sprintf('%06d', rand(1, 999999));

    // Mettre à jour les commandes pour marquer comme confirmées
    foreach ($articles as $article) {
        $sql_update = "UPDATE Commande SET statut = 'Confirmée', dateCommande = NOW() WHERE idCommande = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("i", $article['idCommande']);
        
        if (!$stmt_update->execute()) {
            throw new Exception('Erreur lors de la mise à jour de la commande');
        }
    }

    // Enregistrer les informations de livraison si nécessaire
    $sql_update_client = "UPDATE Utilisateur SET 
                         nom = ?, prenom = ?, email = ?, telephone = ?, 
                         adresse = ?, ville = ?, code_postal = ?, pays = ?
                         WHERE idUtilisateur = ?";
    $stmt_update_client = $conn->prepare($sql_update_client);
    $stmt_update_client->bind_param("ssssssssi", 
        $livraison['nom'], $livraison['prenom'], $livraison['email'], $livraison['telephone'],
        $livraison['adresse'], $livraison['ville'], $livraison['code_postal'], $livraison['pays'],
        $idClient
    );
    $stmt_update_client->execute();

    // Créer un enregistrement de commande globale (optionnel)
    $sql_commande_globale = "INSERT INTO CommandeGlobale (numeroCommande, idClient, montantTotal, statut, methodePaiement, dateCommande) 
                            VALUES (?, ?, ?, 'Confirmée', ?, NOW())";
    
    // Si la table CommandeGlobale n'existe pas, on peut l'ignorer
    $stmt_globale = $conn->prepare($sql_commande_globale);
    if ($stmt_globale) {
        $stmt_globale->bind_param("sids", $numeroCommande, $idClient, $montantTotal, $paiement['methode']);
        $stmt_globale->execute();
    }

    // Envoyer un email de confirmation (simulation)
    envoyerEmailConfirmation($livraison['email'], $numeroCommande, $articles, $montantTotal);

    // Valider la transaction
    $conn->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Commande finalisée avec succès',
        'orderId' => $numeroCommande,
        'montant' => $montantTotal
    ]);

} catch (Exception $e) {
    // Annuler la transaction en cas d'erreur
    $conn->rollback();
    
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}

// Fonction pour simuler le traitement du paiement
function simulerPaiement($paiement, $montant) {
    // Dans un vrai système, ici on intégrerait avec un processeur de paiement
    // comme Stripe, PayPal, etc.
    
    if ($paiement['methode'] === 'card') {
        // Validation basique du numéro de carte
        $numeroCard = preg_replace('/\s+/', '', $paiement['carte']['numero']);
        
        // Simuler des cas d'échec
        if (strlen($numeroCard) < 13 || strlen($numeroCard) > 19) {
            return false;
        }
        
        // Simuler un échec aléatoire (5% de chance)
        if (rand(1, 100) <= 5) {
            return false;
        }
        
        // Simuler des numéros de test qui échouent
        $cardsEchec = ['4000000000000002', '4000000000000341'];
        if (in_array($numeroCard, $cardsEchec)) {
            return false;
        }
        
    } elseif ($paiement['methode'] === 'paypal') {
        // Simuler le paiement PayPal
        // Dans un vrai système, on redirigerait vers PayPal
        
        // Simuler un échec aléatoire (3% de chance)
        if (rand(1, 100) <= 3) {
            return false;
        }
    }
    
    // Simuler un délai de traitement
    usleep(500000); // 0.5 seconde
    
    return true;
}

// Fonction pour envoyer l'email de confirmation
function envoyerEmailConfirmation($email, $numeroCommande, $articles, $montantTotal) {
    // Dans un vrai système, on utiliserait une bibliothèque comme PHPMailer
    // ou un service comme SendGrid
    
    $sujet = "Confirmation de votre commande $numeroCommande - Artisano";
    
    $message = "Bonjour,\n\n";
    $message .= "Votre commande $numeroCommande a été confirmée avec succès.\n\n";
    $message .= "Résumé de votre commande :\n";
    
    foreach ($articles as $article) {
        $message .= "- " . $article['titre'] . " (Quantité: " . $article['nombreArticles'] . ") - " . number_format($article['prix'] * $article['nombreArticles'], 2, ',', ' ') . " €\n";
    }
    
    $message .= "\nMontant total : " . number_format($montantTotal, 2, ',', ' ') . " €\n\n";
    $message .= "Vous recevrez bientôt un email avec les détails de livraison.\n\n";
    $message .= "Merci pour votre confiance !\n";
    $message .= "L'équipe Artisano";
    
    // Simuler l'envoi d'email
    // mail($email, $sujet, $message);
    
    // Pour le développement, on peut logger le message
    error_log("Email de confirmation envoyé à $email : $message");
    
    return true;
}

// Fonction pour valider les données de carte de crédit
function validerCarteBancaire($numero, $expiration, $cvv) {
    // Algorithme de Luhn pour valider le numéro de carte
    $numero = preg_replace('/\s+/', '', $numero);
    
    if (!is_numeric($numero)) {
        return false;
    }
    
    $somme = 0;
    $alternatif = false;
    
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $digit = intval($numero[$i]);
        
        if ($alternatif) {
            $digit *= 2;
            if ($digit > 9) {
                $digit = ($digit % 10) + 1;
            }
        }
        
        $somme += $digit;
        $alternatif = !$alternatif;
    }
    
    return ($somme % 10) === 0;
}
?>