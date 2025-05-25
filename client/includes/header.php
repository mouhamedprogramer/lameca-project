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
        } catch (Exception $e) {
            return 0;
        }
    }
}

// Fonction pour récupérer le nombre de favoris d'événements
if (!function_exists('getNombreFavorisEvenements')) {
    function getNombreFavorisEvenements($conn) {
        if (!isset($_SESSION['idUtilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Client') {
            return 0;
        }
        
        try {
            if (!isset($conn) || $conn === null) {
                return 0;
            }
            
            // Vérifier si la table existe
            $result = mysqli_query($conn, "SHOW TABLES LIKE 'favoris_evenements'");
            if (mysqli_num_rows($result) == 0) {
                return 0;
            }
            
            $idClient = $_SESSION['idUtilisateur'];
            $query = "SELECT COUNT(*) as total FROM favoris_evenements WHERE idClient = $idClient";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                return intval($row['total']);
            }
            
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

// Fonction pour récupérer le nombre d'événements auxquels l'utilisateur participe
if (!function_exists('getNombreEvenementsParticipes')) {
    function getNombreEvenementsParticipes($conn) {
        if (!isset($_SESSION['idUtilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Client') {
            return 0;
        }
        
        try {
            if (!isset($conn) || $conn === null) {
                return 0;
            }
            
            $idClient = $_SESSION['idUtilisateur'];
            // Compter seulement les événements à venir ou en cours
            $query = "SELECT COUNT(*) as total 
                      FROM Clientevenement ce 
                      JOIN Evenement e ON ce.idEvenement = e.idEvenement 
                      WHERE ce.idClient = $idClient AND e.dateDebut >= CURDATE()";
            $result = mysqli_query($conn, $query);
            
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                return intval($row['total']);
            }
            
            return 0;
        } catch (Exception $e) {
            return 0;
        }
    }
}

// Déterminer si l'utilisateur est un client
$estClient = isset($_SESSION['idUtilisateur']) && isset($_SESSION['role']) && $_SESSION['role'] === 'Client';

// Récupérer les compteurs
$nombreArticlesPanier = 0;
$nombreFavorisEvenements = 0;
$nombreEvenementsParticipes = 0;

if ($estClient && isset($conn)) {
    $nombreArticlesPanier = getNombreArticlesPanier($conn);
    $nombreFavorisEvenements = getNombreFavorisEvenements($conn);
    $nombreEvenementsParticipes = getNombreEvenementsParticipes($conn);
}
?>

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
    <link rel="stylesheet" href="css/navbar-enhanced.css">
    <script src="js/modern.js" defer></script>
</head>
<body>

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
                <li><a href="oeuvres.php">Œuvres</a></li>
                <li><a href="evenements.php">Événements</a></li>
                <li><a href="galerie.php">Galerie Virtuelle</a></li>
                <li><a href="FAQ.php">FAQ</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </div>
        <div class="user-actions">
            <?php if ($estClient): ?>
                <!-- Wishlist existante -->
                <a href="wishlist.php" class="icon-link" title="Liste de souhaits">
                    <i class="far fa-heart"></i>
                </a>
                
                <!-- Panier existant -->
                <a href="panier.php" class="icon-link" title="Panier">
                    <i class="fas fa-shopping-cart"></i>
                    <?php if ($nombreArticlesPanier > 0): ?>
                        <span class="badge" id="cart-count"><?= $nombreArticlesPanier ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- NOUVEAU: Favoris d'événements -->
                <a href="mes-favoris-evenements.php" class="icon-link" title="Événements favoris">
                <i class="far fa-star"></i>

                    <?php if ($nombreFavorisEvenements > 0): ?>
                        <span class="badge badge-favorites"><?= $nombreFavorisEvenements ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- NOUVEAU: Mes participations aux événements -->
                <a href="mes-evenements.php" class="icon-link" title="Mes événements">
                    <i class="fas fa-calendar-check"></i>
                    <?php if ($nombreEvenementsParticipes > 0): ?>
                        <span class="badge badge-events"><?= $nombreEvenementsParticipes ?></span>
                    <?php endif; ?>
                </a>
                
                <!-- Messages existants -->
                <a href="messages.php" class="icon-link" title="Messages">
                    <i class="far fa-envelope"></i>
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['nomUtilisateur'])): ?>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span>
                        <?= htmlspecialchars($_SESSION['prenomUtilisateur'] . ' ' . $_SESSION['nomUtilisateur']) ?>
                    </span>
                    <a href="logout.php" title="Déconnexion" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="icon-link" title="Mon compte">
                    <i class="far fa-user"></i>
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>