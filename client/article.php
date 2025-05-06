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
            <button class="basket">Ajouter au panier</button>
            <br><br>
            <div class="dropdown-container">
                <input type="checkbox" id="toggle-description">
                <label for="toggle-description" class="dropdown-label">Description</label>
                <div class="dropdown-content">Taille : 50 × 20 × 30</div>
              </div>
        </div>
    </section>
    <br><br><br>

    

    <section id="commentaire">
        <fieldset>
            <form class="form1" action="../Dynamique/commentaire.php" method="post">
                <h3 class="title1">Avis</h3><br>
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
    <br><br>

    
    <section>
        <h2 class="latest-reviews">Derniers commentaires</h2>
    </section>


    <section id="note">
        <svg style="display:none">
            <symbol id="star" viewBox="-2 -2 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="m10 15-5.9 3 1.1-6.5L.5 7 7 6 10 0l3 6 6.5 1-4.7 4.5 1 6.6z"/>
            </symbol>
        </svg>
        
        <?php 
        $stmt = $pdo->query("SELECT nom, prenom, message, note, date FROM commentaires ORDER BY date DESC LIMIT 5");
        $commentaires = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <?php if ($commentaires): ?>
            <?php foreach ($commentaires as $commentaire): ?>
                <div class="rectangle">
                    <p class="wrapper-rating">
                        <?php
                        for ($i = 1; $i <= 5; $i++) {
                            $filled = $i <= $commentaire["note"] ? 'fill="#FD0"' : 'fill="#ccc"';
                            echo "<svg $filled><use href=\"#star\"></use></svg>";
                        }
                        ?>
                    </p>
                    <p><?= htmlspecialchars($commentaire["prenom"]) . ' ' . htmlspecialchars($commentaire["nom"]) ?></p>
                    <p><?= htmlspecialchars($commentaire["message"]) ?></p>
                    <p class="text-grey"><?= htmlspecialchars($commentaire["date"]) ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucun commentaire pour le moment.</p>
        <?php endif; ?>

        
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