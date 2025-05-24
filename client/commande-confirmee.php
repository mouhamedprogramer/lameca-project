<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    header('Location: connexion.php');
    exit;
}

// Vérifier si un ID de commande est fourni
if (!isset($_GET['id'])) {
    header('Location: panier.php');
    exit;
}

$numeroCommande = $_GET['id'];
$idClient = $_SESSION['idUtilisateur'];

// Récupérer les détails de la commande
$sql_commande = "SELECT c.*, o.titre, o.prix, o.description,
                 (SELECT p.url FROM Photooeuvre p WHERE p.idOeuvre = o.idOeuvre ORDER BY p.idPhoto ASC LIMIT 1) as photo,
                 a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom
                 FROM Commande c
                 JOIN Oeuvre o ON c.idOeuvre = o.idOeuvre
                 JOIN Artisan a ON o.idArtisan = a.idArtisan
                 JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                 WHERE c.idClient = ? AND c.statut = 'Confirmée'
                 ORDER BY c.dateCommande DESC
                 LIMIT 10"; // Limiter aux dernières commandes confirmées

$stmt = $conn->prepare($sql_commande);
$stmt->bind_param("i", $idClient);
$stmt->execute();
$result = $stmt->get_result();

$commandes = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $commandes[] = $row;
    $total += $row['prix'] * $row['nombreArticles'];
}

// Si aucune commande trouvée, rediriger
if (empty($commandes)) {
    header('Location: panier.php');
    exit;
}

// Récupérer les informations du client
$sql_client = "SELECT * FROM Utilisateur WHERE idUtilisateur = ?";
$stmt_client = $conn->prepare($sql_client);
$stmt_client->bind_param("i", $idClient);
$stmt_client->execute();
$client = $stmt_client->get_result()->fetch_assoc();

function formaterPrix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}

// Générer un numéro de suivi aléatoire
$numeroSuivi = 'ARTISANO-' . strtoupper(substr(md5($numeroCommande . time()), 0, 8));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande confirmée - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <style>
        .confirmation-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .success-header {
            text-align: center;
            background: linear-gradient(135deg, #27ae60, #229954);
            color: white;
            padding: 50px 30px;
            border-radius: 20px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .success-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .success-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
        }

        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
            position: relative;
            z-index: 2;
        }

        .success-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            position: relative;
            z-index: 2;
        }

        .order-summary {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .order-number {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #2c3e50;
            font-weight: 600;
        }

        .order-date {
            color: #666;
            font-size: 0.95rem;
        }

        .order-items {
            margin-bottom: 25px;
        }

        .order-item {
            display: flex;
            gap: 20px;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .item-details {
            flex: 1;
        }

        .item-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .item-artist {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 5px;
        }

        .item-quantity {
            color: #888;
            font-size: 0.9rem;
        }

        .item-price {
            font-weight: 600;
            color: #27ae60;
            font-size: 1.1rem;
            text-align: right;
        }

        .order-totals {
            border-top: 2px solid #f0f0f0;
            padding-top: 20px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 1rem;
        }

        .total-line.final {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e9ecef;
        }

        .total-line.final span:last-child {
            color: #27ae60;
        }

        .info-cards {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .info-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .info-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .tracking-number {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #2c3e50;
            border: 2px dashed #4a90e2;
        }

        .next-steps {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }

        .next-steps h3 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
        }

        .steps-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .step-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .step-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .step-icon {
            font-size: 2.5rem;
            color: #4a90e2;
            margin-bottom: 15px;
        }

        .step-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .step-description {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.4;
        }

        .action-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .btn {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4a90e2, #357abd);
            color: white;
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(74, 144, 226, 0.3);
        }

        .btn-outline {
            background: white;
            color: #4a90e2;
            border: 2px solid #4a90e2;
        }

        .btn-outline:hover {
            background: #4a90e2;
            color: white;
        }

        .social-share {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .social-share h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .social-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
            font-size: 1.2rem;
        }

        .social-btn:hover {
            transform: scale(1.1);
        }

        .social-btn.facebook { background: #3b5998; }
        .social-btn.twitter { background: #1da1f2; }
        .social-btn.instagram { background: #e4405f; }
        .social-btn.whatsapp { background: #25d366; }

        /* Responsive */
        @media (max-width: 768px) {
            .success-title {
                font-size: 2rem;
            }

            .success-subtitle {
                font-size: 1rem;
            }

            .info-cards {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .order-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .order-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .item-price {
                text-align: center;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .social-buttons {
                flex-wrap: wrap;
            }
        }

        @media (max-width: 480px) {
            .confirmation-container {
                padding: 10px;
            }

            .success-header {
                padding: 30px 20px;
            }

            .order-summary,
            .info-card,
            .next-steps {
                padding: 20px;
            }

            .steps-list {
                grid-template-columns: 1fr;
            }
        }

        /* Animations */
        .confirmation-container {
            animation: fadeInUp 0.8s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .order-item {
            animation: slideInLeft 0.6s ease forwards;
        }

        .order-item:nth-child(even) {
            animation-delay: 0.1s;
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="confirmation-container">
            <!-- En-tête de succès -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="success-title">Commande confirmée !</h1>
                <p class="success-subtitle">
                    Merci <?php echo htmlspecialchars($client['prenom']); ?>, votre commande a été traitée avec succès
                </p>
            </div>

            <!-- Résumé de la commande -->
            <div class="order-summary">
                <div class="order-header">
                    <div>
                        <div class="order-number">Commande #<?php echo htmlspecialchars($numeroCommande); ?></div>
                        <div class="order-date">
                            <i class="far fa-calendar"></i>
                            Passée le <?php echo date('d/m/Y à H:i'); ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="color: #27ae60; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> Confirmée
                        </div>
                    </div>
                </div>

                <div class="order-items">
                    <?php foreach ($commandes as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <?php 
                                $image_src = !empty($item['photo']) ? $item['photo'] : 'images/oeuvre-placeholder.jpg';
                                ?>
                                <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($item['titre']); ?>">
                            </div>
                            <div class="item-details">
                                <div class="item-title"><?php echo htmlspecialchars($item['titre']); ?></div>
                                <div class="item-artist">
                                    Par <?php echo htmlspecialchars($item['artisan_prenom'] . ' ' . $item['artisan_nom']); ?>
                                </div>
                                <div class="item-quantity">Quantité: <?php echo $item['nombreArticles']; ?></div>
                            </div>
                            <div class="item-price">
                                <?php echo formaterPrix($item['prix'] * $item['nombreArticles']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="order-totals">
                    <div class="total-line">
                        <span>Sous-total:</span>
                        <span><?php echo formaterPrix($total); ?></span>
                    </div>
                    <div class="total-line">
                        <span>Frais de livraison:</span>
                        <span style="color: #27ae60;">Gratuit</span>
                    </div>
                    <div class="total-line">
                        <span>TVA (20%):</span>
                        <span><?php echo formaterPrix($total * 0.2); ?></span>
                    </div>
                    <div class="total-line final">
                        <span>Total TTC:</span>
                        <span><?php echo formaterPrix($total); ?></span>
                    </div>
                </div>
            </div>

            <!-- Informations importantes -->
            <div class="info-cards">
                <div class="info-card">
                    <h3>
                        <i class="fas fa-truck"></i>
                        Livraison
                    </h3>
                    <p><strong>Adresse:</strong><br>
                    <?php echo nl2br(htmlspecialchars($client['adresse'])); ?><br>
                    <?php echo htmlspecialchars($client['code_postal'] . ' ' . $client['ville']); ?><br>
                    <?php echo htmlspecialchars($client['pays']); ?></p>
                    
                    <p><strong>Délai estimé:</strong> 3-5 jours ouvrés</p>
                    
                    <p><strong>Numéro de suivi:</strong></p>
                    <div class="tracking-number"><?php echo $numeroSuivi; ?></div>
                </div>

                <div class="info-card">
                    <h3>
                        <i class="fas fa-envelope"></i>
                        Confirmation
                    </h3>
                    <p>Un email de confirmation a été envoyé à:</p>
                    <p><strong><?php echo htmlspecialchars($client['email']); ?></strong></p>
                    
                    <p>Vous y trouverez:</p>
                    <ul style="margin: 10px 0; padding-left: 20px; color: #666;">
                        <li>Le récapitulatif de votre commande</li>
                        <li>Les informations de livraison</li>
                        <li>La facture en PDF</li>
                    </ul>
                </div>
            </div>

            <!-- Prochaines étapes -->
            <div class="next-steps">
                <h3>Et maintenant ?</h3>
                <div class="steps-list">
                    <div class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-cogs"></i>
                        </div>
                        <div class="step-title">Préparation</div>
                        <div class="step-description">
                            Nos artisans préparent soigneusement votre commande
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="step-title">Emballage</div>
                        <div class="step-description">
                            Emballage sécurisé pour protéger vos œuvres
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-shipping-fast"></i>
                        </div>
                        <div class="step-title">Expédition</div>
                        <div class="step-description">
                            Livraison rapide et suivie jusqu'à votre porte
                        </div>
                    </div>
                    
                    <div class="step-item">
                        <div class="step-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="step-title">Évaluation</div>
                        <div class="step-description">
                            Partagez votre expérience et notez vos achats
                        </div>
                    </div>
                </div>
            </div>

            <!-- Boutons d'action -->
            <div class="action-buttons">
                <a href="oeuvres.php" class="btn btn-primary">
                    <i class="fas fa-palette"></i>
                    Continuer mes achats
                </a>
                <a href="mes-commandes.php" class="btn btn-outline">
                    <i class="fas fa-list"></i>
                    Voir mes commandes
                </a>
            </div>

            <!-- Partage social -->
            <div class="social-share">
                <h4>Partagez votre découverte artistique !</h4>
                <div class="social-buttons">
                    <a href="#" class="social-btn facebook" onclick="shareOnFacebook()">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-btn twitter" onclick="shareOnTwitter()">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-btn instagram" onclick="shareOnInstagram()">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-btn whatsapp" onclick="shareOnWhatsApp()">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Fonctions de partage social
        function shareOnFacebook() {
            const url = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(window.location.origin);
            window.open(url, '_blank', 'width=600,height=400');
        }

        function shareOnTwitter() {
            const text = 'Je viens de découvrir de magnifiques œuvres d\'art sur Artisano ! 🎨✨';
            const url = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(window.location.origin);
            window.open(url, '_blank', 'width=600,height=400');
        }

        function shareOnInstagram() {
            // Instagram ne permet pas le partage direct via URL, on peut rediriger vers l'app
            alert('Partagez votre découverte sur Instagram en nous mentionnant @artisano_officiel !');
        }

        function shareOnWhatsApp() {
            const text = 'Je viens de faire un achat sur Artisano ! Découvrez cette plateforme d\'art authentique : ' + window.location.origin;
            const url = 'https://wa.me/?text=' + encodeURIComponent(text);
            window.open(url, '_blank');
        }

        // Animation de confettis (optionnel)
        function createConfetti() {
            // Code pour animation de confettis si souhaité
        }

        // Lancer les confettis au chargement de la page
        document.addEventListener('DOMContentLoaded', function() {
            // createConfetti();
            
            // Effet de notification de succès
            setTimeout(() => {
                showNotification('Votre commande a été confirmée avec succès !', 'success');
            }, 1000);
        });

        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            `;

            const styles = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 10px;
                    color: white;
                    font-weight: 500;
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    transform: translateX(400px);
                    transition: transform 0.3s ease;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                    min-width: 300px;
                }
                .notification.show { transform: translateX(0); }
                .notification-success { background: linear-gradient(135deg, #27ae60, #229954); }
                .notification-close {
                    background: none; border: none; color: white; font-size: 18px;
                    cursor: pointer; padding: 0; margin-left: auto;
                }
            `;

            if (!document.getElementById('notification-styles')) {
                const styleElement = document.createElement('style');
                styleElement.id = 'notification-styles';
                styleElement.textContent = styles;
                document.head.appendChild(styleElement);
            }

            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);

            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            });

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }
    </script>
</body>
</html>