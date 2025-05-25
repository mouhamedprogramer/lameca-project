<?php
session_start();
require_once 'includes/conn.php';

// Traitement du formulaire AVANT d'inclure le header
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['mail']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT idUtilisateur, nom, prenom, mot_de_passe, role FROM Utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($idUtilisateur, $nom, $prenom, $hashedPassword, $role);

    if ($stmt->fetch()) {
        if (password_verify($password, $hashedPassword) && $role === 'Client') {
            $_SESSION['idUtilisateur'] = $idUtilisateur;
            $_SESSION['nomUtilisateur'] = $nom;
            $_SESSION['prenomUtilisateur'] = $prenom;
            $_SESSION['role'] = $role;
            header("Location: accueil.php");
            exit();
        } else {
            $erreur = "Mot de passe incorrect ou rôle invalide.";
        }
    } else {
        $erreur = "Aucun utilisateur trouvé avec cet email.";
    }

    $stmt->close();
}

// Inclure le header APRÈS le traitement
require_once 'includes/header.php';
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
    <link rel="stylesheet" href="css/login.css">

</head>


<main>
    <section class="ModuleConnexion">
        <div class="formulaire">
            <h2>Connexion</h2>
            <?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
            <form class="form-connexion" method="POST" action="">
                <label class="connexion-label" for="mail">Adresse mail</label>
                <input class="connexion-input" type="text" id="mail" name="mail" placeholder="example@gmail.com" required><br>

                <label class="connexion-label" for="password">Mot de passe</label>
                <input class="connexion-input" type="password" id="password" name="password" placeholder="@#%" required><br>

                <button type="submit">Envoyer</button><br>
            </form>
            <div class="remember">
                <a href="#">Mot de passe oublié</a>
            </div>
            <p>Vous n'avez pas de compte ? <a href="inscription.php">Inscrivez-vous</a></p>
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

<?php require_once 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
