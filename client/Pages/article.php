<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page article</title>
    <link rel="stylesheet" href="../Styles/accueil.css">
    <link rel="stylesheet" href="../Styles/article.css">

</head>

<body class="body1">

    <header>
        <nav class="navbar">
            <a href="#">
                <img src="../Images/Logo.png" alt="Logo Artisano" width="100" class="logo">
            </a>
            <div class="nav-link">
                <ul>
                    <li><a href="#">Accueil</a></li>
                    <li><a href="#">Artisans</a></li>
                    <li><a href="#">Oeuvres</a></li>
                    <li><a href="#">Evenements</a></li>
                    <li><a href="#">Galerie Virtuelle</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Contact</a></li>
                    <li><a href="#">
                        <img src="../Images/Coeur vide.svg" alt="WishList" width="30" class="WishList">
                    </a></li>
                    <li><a href="#">
                        <img src="../Images/panier.svg" alt="Mon panier" width="30" class="panier"></a></li>
                    <li><a href="#">
                        <img src="../Images/enveloppe.svg" alt="Mail" width="30" class="mail">
                    </a></li>
                    <li><a href="#">
                        <img src="../Images/Compte.svg" alt="Mon compte" width="30" class="compte">
                    </a></li>
                </ul>
            </div>
        </nav>
    </header>

    <section id="prestentation_article" class="align">
        <div>
            <img src="../Images/article1.jpg" alt="photo de l'article : un vase">
        </div>
        <div>
            <h2>Cercle de Terre</h2>
            <p class="big-price">122€</p>
            <p class="text-grey">Idéal pour vos soupes, céréales ou boissons chaudes. Durable, passe au lave vaisselle et au micro-ondes. Une touche artisanale pour embellir votre quotidien !</p>
            <br>
            <div class="dropdown-container">
                <input type="checkbox" id="toggle-description">
                <label for="toggle-description" class="dropdown-label">Description</label>
                <div class="dropdown-content">Taille : 50 × 20 × 30</div>
              </div>
        </div>
    </section>

    <section class="container">
        <h3 class="left">Commentaires</h3>
    </section>
    
    <section id="note">
        <svg style="display:none">
            <symbol id="star" viewBox="-2 -2 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="m10 15-5.9 3 1.1-6.5L.5 7 7 6 10 0l3 6 6.5 1-4.7 4.5 1 6.6z"/>
            </symbol>
        </svg>
        
        <?php
        $data = json_decode(file_get_contents("../Dynamique/commentaires.json"), true);
        if ($data) {
            foreach (array_reverse($data) as $commentaire) {
                echo '<div class="rectangle">';
                echo '<p class="wrapper-rating">';
                for ($i = 1; $i <= 5; $i++) {
                    $filled = $i <= $commentaire["note"] ? 'fill="#FD0"' : 'fill="#ccc"';
                    echo "<svg $filled><use href=\"#star\"></use></svg>";
                }
                echo '</p>';
                echo '<p>' . htmlspecialchars($commentaire["prenom"]) . ' ' . htmlspecialchars($commentaire["nom"]) . '</p>';
                echo '<p>' . htmlspecialchars($commentaire["message"]) . '</p>';
                echo '<p class="text-grey">' . htmlspecialchars($commentaire["date"]) . '</p>';
                echo '</div>';
            }
        } else {
            echo "<p>Aucun commentaire pour le moment.</p>";
        }
        ?>
        
    </section>
    <br><br>

    <section id="commentaire" class="comment">
        <fieldset>
            <form class="form1" action="../Dynamique/commentaire.php" method="post">
                <h3>Message</h3><br>
                <label for="nom">Nom</label><br>
                <input type="text" id="nom" name="nom" required><br>
                <label for="prenom">Prénom</label><br>
                <input type="text" id="prenom" name="prenom" required><br>
                <label for="email">Email </label><br>
                <input type="email" id="email" name="email" required><br>
                <label for="message">Message</label><br>
                <textarea id="message" name="message" rows="3" required></textarea><br>

                <label for="note">Note :</label><br>
                <select id="note" name="note" required>
                    <option value="">--Choisir une note--</option>
                    <option value="1">1 étoile</option>
                    <option value="2">2 étoiles</option>
                    <option value="3">3 étoiles</option>
                    <option value="4">4 étoiles</option>
                    <option value="5">5 étoiles</option>
                </select><br>

                <input type="submit" value="Envoyer" class="button-blue"><br>
                
            </form>
        </fieldset>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Sécurisation des données
            $nom = htmlspecialchars(trim($_POST["nom"]));
            $prenom = htmlspecialchars(trim($_POST["prenom"]));
            $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
            $message = htmlspecialchars(trim($_POST["message"]));

            // Vérification basique
            if (!empty($nom) && !empty($prenom) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
                // Exemple : enregistrement ou envoi d'e-mail
                echo "Merci $prenom $nom, votre message a été envoyé avec succès.";
            } else {
                echo "Veuillez remplir tous les champs correctement.";
            }
        } else {
            echo "Méthode non autorisée.";
        }
        ?>

    </section>
    <br><br>


    <footer class="footer">
        <section class="reseau">
            <ul>
                <li><a href="#">
                    <img src="../Images/Linkedin.webp" alt="Twitter" width="30" class="Twitter">
                </a></li>
                <li><a href="#">
                    <img src="../Images/instagram.png" alt="Instagram" width="30" class="Instagram">
                </a></li>
                <li><a href="#">
                    <img src="../Images/Youtube.png" alt="Youtube" width="30" class="Youtube">
                </a></li>
                <li><a href="#">
                    <img src="../Images/Twitter.png" alt="Linkedin" width="30" class="Linkedin">
                </a></li>
            </ul>
        </section>
        <section class="partie1">
            <ul>
                <li><a href="#">Plan du site Internet</a></li>
                <li><a href="#">Mentions Légales</a></li>
            </ul>
        </section>
        <section class="partie2">
            <ul>
                <li><a href="#">Nous contacter</a></li>
                <li><a href="#">Réalisé par LAMECA</a></li>
            </ul>
        </section>
        <section class="sectionLogo">
            <a href="#">
                <img src="../Images/Logo.png" alt="Logo Artisano" width="150" class="logo">
            </a>
        </section>
    </footer>
</body>


</html>