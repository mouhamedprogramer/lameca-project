<?php include('config.php'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="../Styles/accueil.css">
</head>
<body>
<header>
    <nav class="navbar">
        <a href="accueil.html">
            <img src="../Images/Logo.png" alt="Logo Artisano" width="100" class="logo">
        </a>
        <div class="nav-link">
            <ul>
                <li><a href="accueil.html">Accueil</a></li>
                <li><a href="#">Artisans</a></li>
                <li><a href="#">Oeuvres</a></li>
                <li><a href="#">Evenements</a></li>
                <li><a href="#">Galerie Virtuelle</a></li>
                <li><a href="FAQ.html">FAQ</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">
                    <img src="../Images/Coeur vide.svg" alt="WishList" width="30" class="WishList">
                </a></li>
                <li><a href="#">
                    <img src="../Images/panier.svg" alt="Mon panier" width="30" class="panier"></a></li>
                <li><a href="#">
                    <img src="../Images/enveloppe.svg" alt="Mail" width="30" class="mail">
                </a></li>
                <li><a href="connexion.html">
                    <img src="../Images/Compte.svg" alt="Mon compte" width="30" class="compte">
                </a></li>
            </ul>
        </div>
    </nav>
</header>

<main>

<!-- Bannière dynamique depuis la table evenement -->
<section class="bandeau">
    <?php
    $stmt = $pdo->query("SELECT * FROM evenement WHERE mis_en_avant = 1 ORDER BY dateDebut DESC LIMIT 1");
    $event = $stmt->fetch();
    ?>
    <img src="../Images/BanniereEvenement.jpg" alt="Image Principale" width="100%" class="imagePrincipale">
    <div class="titre"><?= htmlspecialchars($event['nomEvenement']) ?></div>
</section>

<!-- Œuvres dynamiques -->
<section class="oeuvres">
    <?php
    $stmt = $pdo->query("SELECT * FROM oeuvre");
    while ($oeuvre = $stmt->fetch()):
    ?>
        <section class="uneOeuvre">
            <img src="<?= htmlspecialchars($oeuvre['image']) ?>" alt="<?= htmlspecialchars($oeuvre['titre']) ?>">
            <div class="legende">
                <?= htmlspecialchars($oeuvre['titre']) ?><br>
                <?= nl2br(htmlspecialchars($oeuvre['description'])) ?><br><br>
                <?= number_format($oeuvre['prix'], 2, ',', ' ') ?> €
            </div>
        </section>
    <?php endwhile; ?>
</section>

</main>

<footer>
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
