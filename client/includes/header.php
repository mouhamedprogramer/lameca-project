<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclure la connexion à la base de données une seule fois
require_once 'includes/conn.php';

// Fonction pour déterminer la page actuelle
function getCurrentPageName() {
    $currentPage = basename($_SERVER['PHP_SELF'], '.php');
    return strtolower($currentPage);
}

// Fonction pour récupérer le nombre d'articles dans le panier
if (!function_exists('getNombreArticlesPanier')) {
    function getNombreArticlesPanier($conn) {
        if (!isset($_SESSION['idUtilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Client') {
            return 0;
        }
        
        try {
            if (!isset($conn) || $conn === null) {
                return 0;
            }
            
            $idClient = $_SESSION['idUtilisateur'];
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

// Fonction pour récupérer le nombre d'items dans la wishlist
if (!function_exists('getNombreWishlist')) {
    function getNombreWishlist($conn) {
        if (!isset($_SESSION['idUtilisateur']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Client') {
            return 0;
        }
        
        try {
            if (!isset($conn) || $conn === null) {
                return 0;
            }
            
            // Vérifier si la table wishlist existe
            $result = mysqli_query($conn, "SHOW TABLES LIKE 'wishlist'");
            if (mysqli_num_rows($result) == 0) {
                return 0;
            }
            
            $idClient = $_SESSION['idUtilisateur'];
            $query = "SELECT COUNT(*) as total FROM wishlist WHERE idClient = $idClient";
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

// Page actuelle pour la gestion de l'état actif
$currentPage = getCurrentPageName();

// Récupérer les compteurs
$nombreArticlesPanier = 0;
$nombreWishlist = 0;
$nombreFavorisEvenements = 0;
$nombreEvenementsParticipes = 0;

if ($estClient && isset($conn)) {
    $nombreArticlesPanier = getNombreArticlesPanier($conn);
    $nombreWishlist = getNombreWishlist($conn);
    $nombreFavorisEvenements = getNombreFavorisEvenements($conn);
    $nombreEvenementsParticipes = getNombreEvenementsParticipes($conn);
}

// Configuration des pages de navigation
$navPages = [
    'accueil' => ['url' => 'accueil.php', 'label' => 'Accueil', 'icon' => 'fas fa-home'],
    'artisans' => ['url' => 'artisans.php', 'label' => 'Artisans', 'icon' => 'fas fa-users'],
    'oeuvres' => ['url' => 'oeuvres.php', 'label' => 'Œuvres', 'icon' => 'fas fa-palette'],
    'evenements' => ['url' => 'evenements.php', 'label' => 'Événements', 'icon' => 'fas fa-calendar-alt'],
    'galerie' => ['url' => 'galerie.php', 'label' => 'Galerie Virtuelle', 'icon' => 'fas fa-images'],
    'faq' => ['url' => 'faq.php', 'label' => 'FAQ', 'icon' => 'fas fa-question-circle'],
    'contact' => ['url' => 'contact.php', 'label' => 'Contact', 'icon' => 'fas fa-envelope']
];
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

<header class="modern-header">
    <nav class="navbar modern-navbar">
        <div class="logo-container">
            <a href="accueil.php" class="logo-link">
                <img src="Images/Logo.png" alt="Logo Artisano" class="logo">
                <span class="logo-text">Artisano</span>
            </a>
        </div>
        
        <div class="nav-toggle" id="navToggle">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </div>
        
        <div class="nav-links modern-nav-links" id="navLinks">
            <ul class="nav-menu">
                <?php foreach ($navPages as $page => $data): ?>
                    <li class="nav-item">
                        <a href="<?= $data['url'] ?>" 
                           class="nav-link <?= ($currentPage === $page) ? 'active' : '' ?>" 
                           data-page="<?= $page ?>">
                            <i class="<?= $data['icon'] ?>"></i>
                            <span><?= $data['label'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        
        <div class="user-actions modern-user-actions">
            <?php if ($estClient): ?>
                <!-- Wishlist -->
                <a href="wishlist.php" class="icon-link wishlist-link" title="Liste de souhaits">
                    <i class="far fa-heart"></i>
                    <span class="badge badge-wishlist"><?= $nombreWishlist ?></span>
                </a>
                
                <!-- Panier -->
                <a href="panier.php" class="icon-link cart-link" title="Panier">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge badge-cart" id="cart-count"><?= $nombreArticlesPanier ?></span>
                </a>
                
                <!-- Favoris d'événements -->
                <a href="mes-favoris-evenements.php" class="icon-link favorites-link" title="Événements favoris">
                    <i class="far fa-star"></i>
                    <span class="badge badge-favorites"><?= $nombreFavorisEvenements ?></span>
                </a>
                
                <!-- Mes participations aux événements -->
                <a href="mes-evenements.php" class="icon-link events-link" title="Mes événements">
                    <i class="fas fa-calendar-check"></i>
                    <span class="badge badge-events"><?= $nombreEvenementsParticipes ?></span>
                </a>
                
                <!-- Messages -->
                <a href="messages.php" class="icon-link messages-link" title="Messages">
                    <i class="far fa-envelope"></i>
                </a>
            <?php endif; ?>

            <?php if (isset($_SESSION['nomUtilisateur'])): ?>
                <div class="user-info modern-user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <span class="user-name">
                            <?= htmlspecialchars($_SESSION['prenomUtilisateur'] . ' ' . $_SESSION['nomUtilisateur']) ?>
                        </span>
                        <span class="user-role"><?= htmlspecialchars($_SESSION['role']) ?></span>
                    </div>
                    <a href="logout.php" title="Déconnexion" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            <?php else: ?>
                <a href="connexion.php" class="icon-link login-link" title="Mon compte">
                    <i class="far fa-user"></i>
                    <span class="login-text">Connexion</span>
                </a>
            <?php endif; ?>
        </div>
    </nav>
</header>

<script>
// Gestion moderne de la navigation
document.addEventListener('DOMContentLoaded', function() {
    const navToggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    const navItems = document.querySelectorAll('.nav-link');
    const header = document.querySelector('.modern-header');
    
    // Toggle menu mobile
    navToggle.addEventListener('click', function() {
        navLinks.classList.toggle('active');
        navToggle.classList.toggle('active');
        document.body.classList.toggle('nav-open');
    });
    
    // Fermer le menu mobile lors du clic sur un lien
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navLinks.classList.remove('active');
            navToggle.classList.remove('active');
            document.body.classList.remove('nav-open');
        });
    });
    
    // Effet de scroll sur le header
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', function() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
        
        
        lastScrollY = currentScrollY;
    });
    
    // Animation des badges
    function animateBadge(badge) {
        badge.style.transform = 'scale(1.3)';
        setTimeout(() => {
            badge.style.transform = 'scale(1)';
        }, 200);
    }
    
    // Fonction pour mettre à jour les badges (utilisable depuis d'autres scripts)
    window.updateBadgeCount = function(badgeClass, count) {
        const badge = document.querySelector(`.${badgeClass}`);
        if (badge) {
            badge.textContent = count;
            animateBadge(badge);
        }
    };
    
    // Smooth scroll pour les liens internes
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>