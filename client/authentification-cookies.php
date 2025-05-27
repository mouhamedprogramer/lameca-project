<?php
session_start();
require_once 'includes/conn.php';

// Fonction pour créer un token sécurisé
function generateSecureToken() {
    return bin2hex(random_bytes(32));
}

// Vérification de la connexion automatique via cookies
if (!isset($_SESSION['idUtilisateur']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    // Vérifier le token dans la base de données
    $stmt = $conn->prepare("SELECT u.idUtilisateur, u.nom, u.prenom, u.role 
                           FROM Utilisateur u 
                           INNER JOIN remember_tokens rt ON u.idUtilisateur = rt.user_id 
                           WHERE rt.token = ? AND rt.expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        // Connecter automatiquement l'utilisateur
        $_SESSION['idUtilisateur'] = $user['idUtilisateur'];
        $_SESSION['nomUtilisateur'] = $user['nom'];
        $_SESSION['prenomUtilisateur'] = $user['prenom'];
        $_SESSION['role'] = $user['role'];
        
        // Rediriger vers l'accueil
        header("Location: accueil.php");
    } else {
        // Token invalide, supprimer le cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    }
    $stmt->close();
}

// Traitement du formulaire AVANT d'inclure le header
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['mail']);
    $password = trim($_POST['password']);
    $remember = isset($_POST['remember']) ? true : false;

    $stmt = $conn->prepare("SELECT idUtilisateur, nom, prenom, mot_de_passe, role FROM Utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($idUtilisateur, $nom, $prenom, $hashedPassword, $role);

    if ($stmt->fetch()) {
        if (password_verify($password, $hashedPassword) && $role === 'Client') {
            // Créer la session
            $_SESSION['idUtilisateur'] = $idUtilisateur;
            $_SESSION['nomUtilisateur'] = $nom;
            $_SESSION['prenomUtilisateur'] = $prenom;
            $_SESSION['role'] = $role;
            
            // Si "Se souvenir de moi" est coché
            if ($remember) {
                $token = generateSecureToken();
                $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 jours
                
                // Supprimer les anciens tokens de cet utilisateur
                $cleanStmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
                $cleanStmt->bind_param("i", $idUtilisateur);
                $cleanStmt->execute();
                $cleanStmt->close();
                
                // Insérer le nouveau token
                $tokenStmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token, expires_at, created_at) VALUES (?, ?, ?, NOW())");
                $tokenStmt->bind_param("iss", $idUtilisateur, $token, $expires);
                $tokenStmt->execute();
                $tokenStmt->close();
                
                // Créer le cookie sécurisé (30 jours)
                setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            }
            
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
    
    <style>
        .remember-checkbox {
            display: flex;
            align-items: center;
            margin: 15px 0;
            gap: 8px;
        }

        .remember-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #667eea;
            cursor: pointer;
        }

        .remember-checkbox label {
            cursor: pointer;
            font-size: 0.9em;
            color: #666;
            user-select: none;
        }

        .remember-checkbox label:hover {
            color: #333;
        }

        .security-notice {
            background: #e8f4fd;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 12px;
            margin: 15px 0;
            font-size: 0.85em;
            color: #0c5460;
        }

        .security-notice i {
            margin-right: 8px;
            color: #17a2b8;
        }
    </style>
</head>

<main>
    <section class="ModuleConnexion">
        <div class="formulaire">
            <h2>Connexion</h2>
            <?php if (isset($erreur)) echo "<p style='color:red;'>$erreur</p>"; ?>
            <form class="form-connexion" method="POST" action="">
                <label class="connexion-label" for="mail">Adresse mail</label>
                <input class="connexion-input" type="email" id="mail" name="mail" placeholder="example@gmail.com" required><br>

                <label class="connexion-label" for="password">Mot de passe</label>
                <input class="connexion-input" type="password" id="password" name="password" placeholder="@#%" required><br>

                <div class="remember-checkbox">
                    <input type="checkbox" id="remember" name="remember" value="1">
                    <label for="remember">Se souvenir de moi pendant 30 jours</label>
                </div>

                <div class="security-notice">
                    <i class="fas fa-shield-alt"></i>
                    Cochez cette option uniquement sur vos appareils personnels de confiance.
                </div>

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

<script>
// Gestion de la checkbox "Se souvenir de moi"
document.addEventListener('DOMContentLoaded', function() {
    const rememberCheckbox = document.getElementById('remember');
    const securityNotice = document.querySelector('.security-notice');
    
    // Afficher/masquer la notice de sécurité
    rememberCheckbox.addEventListener('change', function() {
        if (this.checked) {
            securityNotice.style.display = 'block';
        } else {
            securityNotice.style.display = 'none';
        }
    });
    
    // Masquer la notice au chargement si la checkbox n'est pas cochée
    if (!rememberCheckbox.checked) {
        securityNotice.style.display = 'none';
    }
});
</script>

</body>
</html>