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
?>

<header>
    <nav class="navbar">
        <div class="logo-container">
            <a href="index.php">
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
            <a href="wishlist.php" class="icon-link" title="Liste de souhaits"><i class="far fa-heart"></i></a>
            <a href="panier.php" class="icon-link" title="Panier"><i class="fas fa-shopping-cart"></i><span class="badge" id="cart-count">0</span></a>
            <a href="messages.php" class="icon-link" title="Messages"><i class="far fa-envelope"></i></a>

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



    </nav>
</header>
