<?php
// Connexion à la base de données
require_once 'includes/conn.php';

// Requête pour récupérer une question
$sql_commentaires = "SELECT `id`, `nom`, `prenom`, `email`, `message`, `note`, `date` FROM `commentaires`"; // ou autre critère
$result_commentaires = $conn->query($sql_commentaires);
//$faq = $result_faq->fetch();
$commentaires = [];
if ($result_commentaires->num_rows > 0) {
    while($row = $result_commentaires->fetch_assoc()) {
        $commentaires[] = $row;
    }
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
    <link rel="stylesheet" href="Styles/article.css">
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
        
        <!-- Section deprésentation de l'œuvre d'art -->
        <section id="prestentation_article" class="align">
        <div class="gauche">
            <img src="Images/article1.jpg" alt="photo de l'article : un vase">
        </div>
        <div class="droite ma-div">
            <h2>Cercle de Terre</h2>
            <p class="big-price">122€</p>
            <p class="text-grey">Idéal pour vos soupes, céréales ou boissons chaudes. Durable, passe au lave vaisselle et au micro-ondes. Une touche artisanale pour embellir votre quotidien au quotidien !</p>
            <br>
            <button class="basket">Ajouter au panier</button>
            <br><br>
            <div class="dropdown-container">
                <input type="checkbox" id="toggle-description">
                <label for="toggle-description" class="dropdown-label">Description</label>
                <div class="dropdown-content">Taille : 50 × 20 × 30</div>
              </div>
        </div>
    </section>

    <!-- Section titre commentaires -->
    <section>
        <h2 class="latest-reviews">Derniers commentaires</h2>
    </section>

    <!-- Section voir les commentaires -->
    
    <section class="rereviews">
        <?php foreach ($commentaires as $row): ?>
            <div class="card">
            <div class="stars">
            <?php
                // Récupérer la note de chaque commentaire
                $note = intval($row['note']); // Assurer que la note est un entier
                // Affichage des étoiles pour chaque commentaire
                // Afficher les étoiles pleines
                for ($i = 0; $i < $note; $i++) {
                    echo '★ ';
                }
                // Afficher les étoiles vides
                for ($i = $note; $i < 5; $i++) {
                    echo '☆ ';
                }
                ?>
            </div>
            <h3><?= htmlspecialchars($row['prenom']) ?> <?= htmlspecialchars($row['nom']) ?></h3>
            <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
            <div class="reviewer">
                <img src="https://i.pravatar.cc/40" alt="Reviewer">
                <div>
                <span><?= htmlspecialchars($row['email']) ?></span><br>
                <small><?= $row['date'] ?></small>
                </div>
            </div>
            </div>
        <?php endforeach; ?>

        
    </section>

    <!-- Section ajout d'un commentaire avec un formulaire -->
    <section class="comment-center" id="commentaire">
        <fieldset>
            <form class="form1" action="commentaire.php" method="post">
                <h3>Ajouter un commentaire</h3><br>
                <label for="nom">Nom</label><br>
                <input type="text" id="nom" name="nom" required placeholder="Value"><br>
                <label for="prenom">Prénom</label><br>
                <input type="text" id="prenom" name="prenom" required placeholder="Value"><br>
                <label for="email">Email </label><br>
                <input type="email" id="email" name="email" required placeholder="Value"><br>
                <label for="message">Message</label><br>
                <textarea id="message" name="message" rows="3" required placeholder="Value"></textarea><br>

                <label for="note">Note</label><br>
                <select id="note" name="note" required>
                    <option value="">--Choisir une note--</option>
                    <option value="1">1 étoile</option>
                    <option value="2">2 étoiles</option>
                    <option value="3">3 étoiles</option>
                    <option value="4">4 étoiles</option>
                    <option value="5">5 étoiles</option>
                </select><br>

                <input type="submit" value="Envoyer" class="basket"></input>
                
            </form>
        </fieldset>
        <?php
        $commentaire = null;
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $nom = htmlspecialchars(trim($_POST["nom"]));
            $prenom = htmlspecialchars(trim($_POST["prenom"]));
            $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
            $message = htmlspecialchars(trim($_POST["message"]));
            $note = intval($_POST["note"]);
        
            if (!empty($nom) && !empty($prenom) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message) && $note >= 1 && $note <= 5) {
                $stmt = $pdo->prepare("INSERT INTO commentaires (nom, prenom, email, message, note) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$nom, $prenom, $email, $message, $note]);
            } else {
                $erreur = "Veuillez remplir tous les champs correctement.";
            }
        }
        ?>
    </section>



    <!-- Section donner une note aux commentaires -->
    <section id="note">
        <svg style="display:none">
            <symbol id="star" viewBox="-2 -2 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="m10 15-5.9 3 1.1-6.5L.5 7 7 6 10 0l3 6 6.5 1-4.7 4.5 1 6.6z"/>
            </symbol>
        </svg>
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