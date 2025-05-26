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
                        <a href="profil.php" class="user-name-link">
                            <span class="user-name">
                                <?= htmlspecialchars($_SESSION['prenomUtilisateur'] . ' ' . $_SESSION['nomUtilisateur']) ?>
                            </span>
                        </a>
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
    <!-- À ajouter dans votre includes/header.php -->

<style>
/* CSS pour la barre de recherche fixe centrée sous le header */
.header-search {
    position: fixed;
    top: calc(var(--header-height, 80px) + 0.5cm); /* Position fixe en dessous du header */
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 600px; /* Largeur maximale */
    padding: 0 20px; /* Espacement sur les côtés pour mobile */
    z-index: 999; /* Légèrement en dessous du header mais au-dessus du contenu */
    box-sizing: border-box;
}

/* Assurez-vous que le header a une position fixe avec un z-index supérieur */
.modern-header {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 1000;
    --header-height: 80px; /* Variable CSS pour la hauteur du header */
    height: var(--header-height);
}

.search-wrapper {
    position: relative;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 30px;
    padding: 2px;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
    width: 100%;
}

.search-wrapper.focused {
    transform: scale(1.02);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.search-box {
    position: relative;
    background: white;
    border-radius: 28px;
    overflow: hidden;
}

.search-input {
    width: 100%;
    padding: 15px 60px 15px 25px; /* Padding plus généreux */
    border: none;
    outline: none;
    font-size: 16px; /* Taille de police plus grande */
    background: transparent;
    color: #333;
    font-family: inherit;
    box-sizing: border-box;
}

.search-input::placeholder {
    color: #999;
    font-style: italic;
}

.search-btn {
    position: absolute;
    right: 5px;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50%;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.search-btn:hover {
    transform: translateY(-50%) scale(1.1);
}

.search-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border-radius: 12px;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
    max-height: 400px;
    overflow-y: auto;
    z-index: 1001;
    margin-top: 8px;
    border: 1px solid #e0e0e0;
    display: none;
}

.search-dropdown.show {
    display: block;
    animation: slideDown 0.3s ease;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-section {
    padding: 12px 0;
}

.dropdown-section:not(:last-child) {
    border-bottom: 1px solid #f0f0f0;
}

.dropdown-title {
    padding: 0 16px 8px;
    font-weight: 600;
    color: #667eea;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.dropdown-item {
    padding: 10px 16px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    color: #333;
    text-decoration: none;
    font-size: 13px;
}

.dropdown-item:hover {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: translateX(3px);
}

.dropdown-item i {
    margin-right: 10px;
    width: 16px;
    text-align: center;
    opacity: 0.7;
    font-size: 12px;
}

.dropdown-item-info {
    flex: 1;
}

.dropdown-item-title {
    font-weight: 500;
    margin-bottom: 2px;
}

.dropdown-item-subtitle {
    font-size: 11px;
    opacity: 0.7;
}

.no-results, .loading {
    padding: 16px;
    text-align: center;
    color: #999;
    font-size: 13px;
}

.loading {
    color: #667eea;
}

/* Ajustement pour éviter que le contenu principal soit masqué */
body {
    padding-top: calc(var(--header-height, 80px) + 0.5cm + 60px); /* Header + espacement + hauteur barre de recherche */
}

/* Pour compenser l'espace pris par la barre de recherche fixe */
.main-content {
    margin-top: 0; /* Plus besoin de margin car body a déjà le padding */
}

/* Responsive */
@media (max-width: 768px) {
    .modern-header {
        --header-height: 70px; /* Header plus petit sur mobile */
    }
    
    .header-search {
        max-width: 95%;
        padding: 0 10px;
        top: calc(var(--header-height, 70px) + 0.3cm); /* Réduire l'espacement sur mobile */
    }
    
    body {
        padding-top: calc(var(--header-height, 70px) + 0.3cm + 50px);
    }
    
    .search-input {
        padding: 12px 50px 12px 20px;
        font-size: 14px;
    }
    
    .search-btn {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }
    
    .main-content {
        margin-top: 0;
    }
}

@media (max-width: 480px) {
    .modern-header {
        --header-height: 60px; /* Header encore plus petit sur très petit écran */
    }
    
    .header-search {
        max-width: 98%;
        padding: 0 5px;
        top: calc(var(--header-height, 60px) + 0.2cm);
    }
    
    body {
        padding-top: calc(var(--header-height, 60px) + 0.2cm + 45px);
    }
    
    .search-input {
        padding: 10px 45px 10px 15px;
        font-size: 13px;
    }
    
    .search-btn {
        width: 32px;
        height: 32px;
        font-size: 13px;
    }
}
</style>

<!-- HTML à intégrer dans votre header -->
<div class="header-search">
    <div class="search-wrapper" id="searchWrapper">
        <div class="search-box">
            <input 
                type="text" 
                class="search-input" 
                id="globalSearch" 
                placeholder="Rechercher une œuvre, un artisan..."
                autocomplete="off"
            >
            <button class="search-btn" type="button" id="searchBtn">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </div>
    
    <div class="search-dropdown" id="searchDropdown">
        <div id="dropdownContent">
            <!-- Le contenu sera injecté par JavaScript -->
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('globalSearch');
    const searchWrapper = document.getElementById('searchWrapper');
    const dropdown = document.getElementById('searchDropdown');
    const dropdownContent = document.getElementById('dropdownContent');
    const searchBtn = document.getElementById('searchBtn');
    
    let searchTimeout;
    let currentQuery = '';

    // Animation focus
    searchInput.addEventListener('focus', function() {
        searchWrapper.classList.add('focused');
    });

    searchInput.addEventListener('blur', function() {
        searchWrapper.classList.remove('focused');
        // Delay pour permettre le clic sur les suggestions
        setTimeout(() => {
            dropdown.classList.remove('show');
        }, 200);
    });

    // Recherche en temps réel
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        currentQuery = query;
        
        if (query.length < 2) {
            dropdown.classList.remove('show');
            return;
        }

        // Debounce
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    // Gestion du bouton de recherche
    searchBtn.addEventListener('click', function() {
        if (currentQuery.length >= 2) {
            window.location.href = `recherche.php?q=${encodeURIComponent(currentQuery)}`;
        }
    });

    // Recherche sur Entrée
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter' && currentQuery.length >= 2) {
            window.location.href = `recherche.php?q=${encodeURIComponent(currentQuery)}`;
        }
    });

    function performSearch(query) {
        // Afficher le loading
        dropdownContent.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Recherche...</div>';
        dropdown.classList.add('show');

        // Appel AJAX
        fetch('search_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'query=' + encodeURIComponent(query)
        })
        .then(response => response.json())
        .then(data => {
            displayResults(data);
        })
        .catch(error => {
            console.error('Erreur:', error);
            dropdownContent.innerHTML = '<div class="no-results">Erreur lors de la recherche</div>';
        });
    }

    function displayResults(data) {
        let html = '';
        
        if (data.oeuvres && data.oeuvres.length > 0) {
            html += '<div class="dropdown-section">';
            html += '<div class="dropdown-title"><i class="fas fa-palette"></i> Œuvres</div>';
            data.oeuvres.slice(0, 3).forEach(item => {
                html += `
                    <a href="oeuvre.php?id=${item.id}" class="dropdown-item">
                        <i class="fas fa-palette"></i>
                        <div class="dropdown-item-info">
                            <div class="dropdown-item-title">${item.nom}</div>
                            <div class="dropdown-item-subtitle">${item.artisan} - ${item.prix}€</div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
        }

        if (data.artisans && data.artisans.length > 0) {
            html += '<div class="dropdown-section">';
            html += '<div class="dropdown-title"><i class="fas fa-users"></i> Artisans</div>';
            data.artisans.slice(0, 3).forEach(item => {
                html += `
                    <a href="artisan.php?id=${item.id}" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <div class="dropdown-item-info">
                            <div class="dropdown-item-title">${item.nom}</div>
                            <div class="dropdown-item-subtitle">${item.specialite}</div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
        }

        if (data.evenements && data.evenements.length > 0) {
            html += '<div class="dropdown-section">';
            html += '<div class="dropdown-title"><i class="fas fa-calendar"></i> Événements</div>';
            data.evenements.slice(0, 2).forEach(item => {
                html += `
                    <a href="evenement.php?id=${item.id}" class="dropdown-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div class="dropdown-item-info">
                            <div class="dropdown-item-title">${item.nom}</div>
                            <div class="dropdown-item-subtitle">${item.date}</div>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
        }

        if (html === '') {
            html = '<div class="no-results"><i class="fas fa-search"></i> Aucun résultat</div>';
        } else {
            // Ajouter un lien "Voir tous les résultats"
            html += `
                <div class="dropdown-section">
                    <a href="recherche.php?q=${encodeURIComponent(currentQuery)}" class="dropdown-item" style="background: #f8f9fa; font-weight: 600; justify-content: center;">
                        <i class="fas fa-arrow-right"></i>
                        Voir tous les résultats
                    </a>
                </div>
            `;
        }

        dropdownContent.innerHTML = html;
        dropdown.classList.add('show');
    }

    // Fermer le dropdown en cliquant ailleurs
    document.addEventListener('click', function(e) {
        if (!searchWrapper.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });
});
</script>
    
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