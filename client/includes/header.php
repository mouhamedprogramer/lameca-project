<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artisano - Découvrez l'art authentique</title>
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- CSS personnalisé -->
    
    <link rel="stylesheet" href="css/modern.css">
    <script src="js/modern.js" defer></script>
</head>
<body>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la connexion à la base de données une seule fois
require_once 'includes/conn.php';

// Fonction pour récupérer le nombre d'articles dans le panier pour le client connecté
if (!function_exists('getNombreArticlesPanier')) {
    function getNombreArticlesPanier($conn) {
        // Vérifier si l'utilisateur est connecté
        if (!isset($_SESSION['idUtilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Client') {
            return 0;
        }
        
        try {
            // Vérifier si la connexion est établie
            if (!isset($conn) || $conn === null) {
                return 0;
            }
            
            // Récupérer le nombre d'articles dans le panier pour ce client
            $idClient = $_SESSION['idUtilisateur'];
            
            // Version MySQLi - Méthode 1: Requête simple
            $query = "SELECT COUNT(*) as total FROM Commande WHERE idClient = $idClient AND statut = 'En attente'";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                return intval($row['total']);
            }
            
            return 0;
            
            /* Version MySQLi préparée si nécessaire (mais nécessite plus de débogage)
            $query = "SELECT COUNT(*) as total FROM Commande WHERE idClient = ? AND statut = 'En attente'";
            $stmt = mysqli_prepare($conn, $query);
            
            if (!$stmt) {
                return 0;
            }
            
            mysqli_stmt_bind_param($stmt, "i", $idClient);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_bind_result($stmt, $total);
            mysqli_stmt_fetch($stmt);
            mysqli_stmt_close($stmt);
            
            return intval($total);
            */
        } catch (Exception $e) {
            return 0;
        }
    }
}

// Déterminer si l'utilisateur est un client
$estClient = isset($_SESSION['idUtilisateur']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Client';

// Récupérer le nombre d'articles dans le panier
$nombreArticlesPanier = 0;
if ($estClient && isset($conn)) {
    $nombreArticlesPanier = getNombreArticlesPanier($conn);
}
?>

<header>
    <nav class="navbar">
        <div class="logo-container">
            <a href="accueil.php">
                <img src="Images/Logo.png" alt="Logo Artisano" class="logo">
            </a>
        </div>
        <div class="nav-toggle">
            <i class="fas fa-bars"></i>
        </div>
        <div class="nav-links">
            <ul>
                <li><a href="accueil.php" class="active">Accueil</a></li>
                <li><a href="artisans.php">Artisans</a></li>
                <li><a href="oeuvre.php">œuvres</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="galerie.php">Galerie Virtuelle</a></li>
                <li><a href="FAQ.php">FAQ</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
        <div class="user-actions">
            <?php if ($estClient): ?>
                <a href="wishlist.php" class="icon-link" title="Liste de souhaits"><i class="far fa-heart"></i></a>
                <a href="panier.php" class="icon-link" title="Panier"><i class="fas fa-shopping-cart"></i><span class="badge" id="cart-count"><?= $nombreArticlesPanier ?></span></a>
                <a href="messages.php" class="icon-link" title="Messages"><i class="far fa-envelope"></i></a>
            <?php endif; ?>

            <?php if (isset($_SESSION['nomUtilisateur'])): ?>
                <div class="user-info" style="
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    background-color: #f0f0f0;
                    padding: 6px 14px;
                    border-radius: 30px;
                    font-family: 'Poppins', sans-serif;
                    min-width: 200px;
                ">
                    <i class="fas fa-user-circle" style="font-size: 20px; color: #2c3e50;"></i>

                    <span style="font-weight: 500; font-size: 14px; color: #2c3e50;">
                        <?= htmlspecialchars($_SESSION['prenomUtilisateur'] . ' ' . $_SESSION['nomUtilisateur']) ?>
                    </span>

                    <a href="logout.php" title="Déconnexion" style="color: #e74c3c; font-size: 16px; margin-left: 30px;">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="icon-link" title="Mon compte"><i class="far fa-user"></i></a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<?php if (isset($_SESSION['idUtilisateur']) && $estClient): ?>
<!-- Code de débogage temporaire -->
<div style="background: #f8f8f8; border: 1px solid #ddd; padding: 10px; margin: 20px; display: none;">
    <h4>Informations de débogage</h4>
    <p>ID Client: <?= $_SESSION['idUtilisateur'] ?></p>
    <p>Requête: SELECT COUNT(*) as total FROM Commande WHERE idClient = <?= $_SESSION['idUtilisateur'] ?> AND statut = 'En attente'</p>
    <p>Nombre d'articles: <?= $nombreArticlesPanier ?></p>
</div>
<?php endif; ?>