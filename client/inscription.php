<?php
session_start();
require_once 'includes/conn.php';
require_once 'includes/header.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['mail']);
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $role = 'Client'; // Par défaut, le rôle est 'Client'
    $telephone = trim($_POST['telephone']);
    $adresse = trim($_POST['adresse']);
    $pays = trim($_POST['pays']);
    $ville = trim($_POST['ville']);
    $codePostal = trim($_POST['code_postal']);
    $dateNaissance = $_POST['date_naissance'];
    $genre = $_POST['genre'];
    $photo = $_FILES['photo']['name'];
    $conditionsAcceptees = isset($_POST['conditions']) ? true : false;

    // Validation des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirmPassword)) {
        $erreur = "Tous les champs obligatoires doivent être remplis.";
    }
    // Validation du mot de passe robuste
    elseif (strlen($password) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } elseif (!preg_match("/[A-Z]/", $password)) {
        $erreur = "Le mot de passe doit contenir au moins une majuscule.";
    } elseif (!preg_match("/[a-z]/", $password)) {
        $erreur = "Le mot de passe doit contenir au moins une minuscule.";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $erreur = "Le mot de passe doit contenir au moins un chiffre.";
    } elseif (!preg_match("/[!@#$%^&*(),.?\":{}|<>]/", $password)) {
        $erreur = "Le mot de passe doit contenir au moins un caractère spécial.";
    }
    // Validation si les mots de passe ne correspondent pas
    elseif ($password !== $confirmPassword) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (!$conditionsAcceptees) {
        $erreur = "Vous devez accepter les conditions générales d'utilisation.";
    } else {
        // Hachage du mot de passe
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertion dans la base de données
        $stmt = $conn->prepare("INSERT INTO Utilisateur (nom, prenom, email, mot_de_passe, telephone, adresse, pays, ville, code_postal, date_naissance, genre, role, photo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", $nom, $prenom, $email, $hashedPassword, $telephone, $adresse, $pays, $ville, $codePostal, $dateNaissance, $genre, $role, $photo);

        if ($stmt->execute()) {
            header("Location: connexion.php");
            exit();
        } else {
            $erreur = "Erreur lors de l'inscription. Veuillez réessayer.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/login.css">
</head>

<main>
    <section class="ModuleConnexion">
        <div class="formulaire">
            <h2>Inscription</h2>
            <?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <!-- Informations personnelles -->
                <h3>Informations personnelles</h3>
                <label class="connexion-label" for="nom">Nom<span style="color:red;">*</span></label>
                <input class="connexion-input" type="text" id="nom" name="nom" required><br><br>

                <label class="connexion-label" for="prenom">Prénom<span style="color:red;">*</span></label>
                <input class="connexion-input" type="text" id="prenom" name="prenom" required><br><br>

                <label class="connexion-label" for="date_naissance">Date de naissance<span style="color:red;">*</span></label>
                <input class="connexion-input" type="date" id="date_naissance" name="date_naissance" required><br><br>

                <label class="connexion-label" for="genre">Genre</label>
                <select class="connexion-input" id="genre" name="genre">
                    <option value="">Selection</option>
                    <option value="Homme">Homme</option>
                    <option value="Femme">Femme</option>
                </select><br><br>

                <label class="connexion-label" for="mail">Email<span style="color:red;">*</span></label>
                <input class="connexion-input" type="email" id="mail" name="mail" required><br><br>

                <label class="connexion-label" for="password">Mot de passe<span style="color:red;">*</span></label>
                <input class="connexion-input" type="password" id="password" name="password" required><br><br>

                <label class="connexion-label" for="confirmPassword">Confirmer le mot de passe<span style="color:red;">*</span></label>
                <input class="connexion-input" type="password" id="confirmPassword" name="confirmPassword" required><br><br>

                <!-- Informations de contact -->
                <h3>Informations de contact</h3>
                <label class="connexion-label" for="telephone">Téléphone</label>
                <input class="connexion-input" type="text" id="telephone" name="telephone"><br><br>

                <label class="connexion-label" for="adresse">Adresse</label>
                <input class="connexion-input" type="text" id="adresse" name="adresse"><br><br>

                <label class="connexion-label" for="pays">Pays</label>
                <input class="connexion-input" type="text" id="pays" name="pays"><br><br>

                <label class="connexion-label" for="ville">Ville</label>
                <input class="connexion-input" type="text" id="ville" name="ville"><br><br>

                <label class="connexion-label" for="code_postal">Code Postal</label>
                <input class="connexion-input" type="text" id="code_postal" name="code_postal"><br><br>

                <label>
                    <input type="checkbox" name="conditions" required>
                    J'accepte les <a href="conditions.php">conditions générales d'utilisation</a><span style="color:red;">*</span>
                </label><br><br>

                <!-- Photo de profil -->
                <h3>Photo de profil</h3>
                <label class="connexion-label" for="photo">Choisir une photo</label>
                <input class="connexion-input" type="file" id="photo" name="photo"><br><br>

                <button type="submit">S'inscrire</button><br>
            </form>

            <p>Vous avez déjà un compte ? <a href="connexion.php">Connectez-vous</a></p>
        </div>
    </section>
</main>

<?php require_once 'includes/footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const form = document.querySelector("form");
        form.addEventListener("submit", function (event) {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirmPassword").value;
            let errorMessages = [];

            // Validation du mot de passe côté client
            if (password.length < 8) {
                errorMessages.push("Le mot de passe doit contenir au moins 8 caractères.");
            }
            if (!/[A-Z]/.test(password)) {
                errorMessages.push("Le mot de passe doit contenir au moins une majuscule.");
            }
            if (!/[a-z]/.test(password)) {
                errorMessages.push("Le mot de passe doit contenir au moins une minuscule.");
            }
            if (!/[0-9]/.test(password)) {
                errorMessages.push("Le mot de passe doit contenir au moins un chiffre.");
            }
            if (!/[!@#$%^&*(),.?\":{}|<>]/.test(password)) {
                errorMessages.push("Le mot de passe doit contenir au moins un caractère spécial.");
            }

            // Validation de la correspondance des mots de passe
            if (password !== confirmPassword) {
                errorMessages.push("Les mots de passe ne correspondent pas.");
            }

            // Afficher les erreurs si elles existent
            if (errorMessages.length > 0) {
                event.preventDefault();
                let errorHtml = "<ul>";
                errorMessages.forEach(function (message) {
                    errorHtml += `<li>${message}</li>`;
                });
               
                errorHtml += "</ul>";
                document.querySelector("form").insertAdjacentHTML("beforebegin", <div style="color:red;">${errorHtml}</div>);
}
});
});
</script>

<style>
    .connexion-input {
        width: 750px; /* Ajustez la largeur selon vos besoins */
        padding: 8px; /* Réduit l'espace intérieur */
        font-size: 24px; /* Diminue la taille de la police */
    }

    .ModuleConnexion{
        width: 950px;
    }
</style>


</html>