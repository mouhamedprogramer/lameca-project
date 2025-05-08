<?php
// Connexion à la base de données
require_once 'includes/conn.php';
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
    <link rel="stylesheet" href="Styles/login.css">

</head>
<body>
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
                    <li><a href="index.php" class="active">Accueil</a></li>
                    <li><a href="artisans.php">Artisans</a></li>
                    <li><a href="oeuvres.php">Œuvres</a></li>
                    <li><a href="evenements.php">Événements</a></li>
                    <li><a href="galerie.php">Galerie Virtuelle</a></li>
                    <li><a href="FAQ.php">FAQ</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="user-actions">
                <a href="wishlist.php" class="icon-link" title="Liste de souhaits">
                    <i class="far fa-heart"></i>
                </a>
                <a href="panier.php" class="icon-link" title="Panier">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="badge" id="cart-count">0</span>
                </a>
                <a href="messages.php" class="icon-link" title="Messages">
                    <i class="far fa-envelope"></i>
                </a>
                <a href="connexion.php" class="icon-link" title="Mon compte">
                    <i class="far fa-user"></i>
                </a>
            </div>
        </nav>
    </header>

    <main>

        <!-- Section de connexion -->
        <section class="ModuleConnexion">
            <div class="formulaire">
                <h2>Connexion</h2>
                
                <form class="form-connexion">
                    <label class="connexion-label" for="mail">Adresse mail</label><br>
                    <input class="connexion-input" type="text" id="mail" name="mail" placeholder="example@gmail.com" required><br><br>
                    <label class="connexion-label" for="mot de passe">Mot de passe </label><br>
                    <input class="connexion-input" type="password" id="password" name="password" placeholder="@#%" required><br><br>
                </form>
                <div class="remember">
                    <a href="#">Mot de passe oublié</a>
                </div>
                <p>Vous n'avez pas de compte ? <a href="#">Inscrivez-vous</a></p>
                <button type="submit">Envoyer</button><br>
            </div>

            <div class="image">
                <img src="Images/Image connexion.jpg" alt="Image Connexion" width="100%">
                <div class="legende">
                    <h2>Rejoignez-nous !</h2><br>
                    <p>Découvrez une marketplace unique où les artisans peuvent exposer et vendre leurs créations à un large public. <br> Rejoignez une communauté passionnée, gagnez en visibilité et développez votre activité facilement !</p>
                </div>
            </div>
        </section>
    </main>



    <footer>
        <div class="footer-content">
            <div class="footer-logo">
                <img src="Images/Logo.png" alt="Logo Artisano" class="logo">
                <p>Artisano - La place de marché pour l'art authentique et le savoir-faire artisanal.</p>
            </div>
            
            <div class="footer-links">
                <div class="footer-column">
                    <h3>Navigation</h3>
                    <ul>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="artisans.php">Artisans</a></li>
                        <li><a href="oeuvres.php">Œuvres</a></li>
                        <li><a href="evenements.php">Événements</a></li>
                        <li><a href="galerie.php">Galerie Virtuelle</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Informations</h3>
                    <ul>
                        <li><a href="a-propos.php">À propos</a></li>
                        <li><a href="FAQ.php">FAQ</a></li>
                        <li><a href="contact.php">Contact</a></li>
                        <li><a href="mentions-legales.php">Mentions Légales</a></li>
                        <li><a href="politique-confidentialite.php">Politique de confidentialité</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul class="contact-info">
                        <li><i class="fas fa-map-marker-alt"></i> 123 Rue de l'Art, 75000 Paris</li>
                        <li><i class="fas fa-phone"></i> +33 1 23 45 67 89</li>
                        <li><i class="fas fa-envelope"></i> contact@artisano.fr</li>
                    </ul>
                </div>
            </div>
            
            <div class="footer-social">
                <h3>Suivez-nous</h3>
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Artisano. Tous droits réservés. Réalisé par LAMECA.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>