<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['idUtilisateur'])) {
    header('Location: connexion.php');
    exit();
}

$idUtilisateur = $_SESSION['idUtilisateur'];
$role = $_SESSION['role'];

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM Utilisateur WHERE idUtilisateur = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$utilisateur = mysqli_fetch_assoc($result);

// Statistiques selon le rôle
$stats = [];

if ($role === 'Client') {
    // Statistiques client
    $queries = [
        'commandes' => "SELECT COUNT(*) as total FROM Commande WHERE idClient = ?",
        'commandes_confirmees' => "SELECT COUNT(*) as total FROM Commande WHERE idClient = ? AND statut != 'En attente'",
        'wishlist' => "SELECT COUNT(*) as total FROM wishlist WHERE idClient = ?",
        'evenements' => "SELECT COUNT(*) as total FROM Clientevenement WHERE idClient = ?",
        'favoris_evenements' => "SELECT COUNT(*) as total FROM favoris_evenements WHERE idClient = ?",
        'avis_donnes' => "SELECT COUNT(*) as total FROM Avisoeuvre WHERE idClient = ?"
    ];
    
    foreach ($queries as $key => $query) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats[$key] = mysqli_fetch_assoc($result)['total'];
    }
    
    // Montant total dépensé
    $query = "SELECT SUM(o.prix * c.nombreArticles) as total 
              FROM Commande c 
              JOIN Oeuvre o ON c.idOeuvre = o.idOeuvre 
              WHERE c.idClient = ? AND c.statut = 'Livrée'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['montant_depense'] = mysqli_fetch_assoc($result)['total'] ?? 0;
    
} elseif ($role === 'Artisan') {
    // Statistiques artisan
    $queries = [
        'oeuvres' => "SELECT COUNT(*) as total FROM Oeuvre WHERE idArtisan = ?",
        'oeuvres_vendues' => "SELECT COUNT(DISTINCT o.idOeuvre) as total FROM Oeuvre o JOIN Commande c ON o.idOeuvre = c.idOeuvre WHERE o.idArtisan = ? AND c.statut = 'Livrée'",
        'evenements' => "SELECT COUNT(*) as total FROM Evenement WHERE idArtisan = ?",
        'avis_recus' => "SELECT COUNT(*) as total FROM Avisartisan WHERE idArtisan = ?"
    ];
    
    foreach ($queries as $key => $query) {
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $stats[$key] = mysqli_fetch_assoc($result)['total'];
    }
    
    // Note moyenne
    $query = "SELECT AVG(notation) as moyenne FROM Avisartisan WHERE idArtisan = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['note_moyenne'] = round(mysqli_fetch_assoc($result)['moyenne'] ?? 0, 1);
    
    // Revenus générés
    $query = "SELECT SUM(o.prix * c.nombreArticles) as total 
              FROM Oeuvre o 
              JOIN Commande c ON o.idOeuvre = c.idOeuvre 
              WHERE o.idArtisan = ? AND c.statut = 'Livrée'";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $stats['revenus'] = mysqli_fetch_assoc($result)['total'] ?? 0;
}

// Traitement de la mise à jour du profil
$message = '';
$messageType = '';

if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        // Récupérer les données avec des valeurs par défaut pour éviter les erreurs
        $nom = trim($_POST['nom'] ?? '');
        $prenom = trim($_POST['prenom'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telephone = trim($_POST['telephone'] ?? '');
        $adresse = trim($_POST['adresse'] ?? '');
        $ville = trim($_POST['ville'] ?? '');
        $code_postal = trim($_POST['code_postal'] ?? '');
        $pays = trim($_POST['pays'] ?? '');
        $date_naissance = $_POST['date_naissance'] ?? '';
        $genre = $_POST['genre'] ?? '';
        
        // Validation des champs obligatoires uniquement
        if (empty($nom) || empty($prenom) || empty($email)) {
            $message = "Les champs nom, prénom et email sont obligatoires.";
            $messageType = 'error';
        } else {
            // Vérifier si l'email n'existe pas déjà pour un autre utilisateur
            $query = "SELECT idUtilisateur FROM Utilisateur WHERE email = ? AND idUtilisateur != ?";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "si", $email, $idUtilisateur);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            if (mysqli_num_rows($result) > 0) {
                $message = "Cette adresse email est déjà utilisée par un autre compte.";
                $messageType = 'error';
            } else {
                // Mettre à jour les informations (tous les champs, même s'ils sont vides)
                $query = "UPDATE Utilisateur SET nom = ?, prenom = ?, email = ?, telephone = ?, adresse = ?, ville = ?, code_postal = ?, pays = ?, date_naissance = ?, genre = ? WHERE idUtilisateur = ?";
                $stmt = mysqli_prepare($conn, $query);
                
                // Convertir les dates vides en NULL pour la base de données
                $date_naissance = !empty($date_naissance) ? $date_naissance : null;
                
                mysqli_stmt_bind_param($stmt, "ssssssssssi", $nom, $prenom, $email, $telephone, $adresse, $ville, $code_postal, $pays, $date_naissance, $genre, $idUtilisateur);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Profil mis à jour avec succès !";
                    $messageType = 'success';
                    
                    // Mettre à jour les variables de session
                    $_SESSION['nomUtilisateur'] = $nom;
                    $_SESSION['prenomUtilisateur'] = $prenom;
                    
                    // Récupérer les nouvelles données
                    $query = "SELECT * FROM Utilisateur WHERE idUtilisateur = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "i", $idUtilisateur);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $utilisateur = mysqli_fetch_assoc($result);
                } else {
                    $message = "Erreur lors de la mise à jour du profil.";
                    $messageType = 'error';
                }
            }
        }
    } elseif ($_POST['action'] === 'change_password') {
        // Récupérer les données avec des valeurs par défaut
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Vérifier que tous les champs de mot de passe sont remplis
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $message = "Tous les champs de mot de passe sont obligatoires.";
            $messageType = 'error';
        } elseif ($new_password !== $confirm_password) {
            $message = "Les nouveaux mots de passe ne correspondent pas.";
            $messageType = 'error';
        } elseif (strlen($new_password) < 6) {
            $message = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
            $messageType = 'error';
        } else {
            // Vérifier l'ancien mot de passe
            if (password_verify($current_password, $utilisateur['mot_de_passe'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $query = "UPDATE Utilisateur SET mot_de_passe = ? WHERE idUtilisateur = ?";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "si", $hashed_password, $idUtilisateur);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Mot de passe modifié avec succès !";
                    $messageType = 'success';
                } else {
                    $message = "Erreur lors du changement de mot de passe.";
                    $messageType = 'error';
                }
            } else {
                $message = "Mot de passe actuel incorrect.";
                $messageType = 'error';
            }
        }
    }
}

// Inclure le header
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Artisano</title>
    <link rel="stylesheet" href="css/profil.css">
</head>
<body>
<br><br><br><br><br><br>
<div class="profile-container">
    <div class="profile-header">
        <div class="profile-banner">
            <div class="profile-avatar-container">
                <div class="profile-avatar">
                    <?php if (!empty($utilisateur['photo'])): ?>
                        <img src="<?= htmlspecialchars((!empty($utilisateur['photo'])) ? '../images/'.$utilisateur['photo'] : 'images/profile.jpg') ?>" alt="Photo de profil">
                         <?php else: ?>
                        <i class="fas fa-user-circle"></i>
                    <?php endif; ?>
                </div>
                <button class="change-photo-btn" onclick="changePhoto()">
                    <i class="fas fa-camera"></i>
                </button>
            </div>
            <div class="profile-info">
                <h1><?= htmlspecialchars($utilisateur['prenom'] . ' ' . $utilisateur['nom']) ?></h1>
                <p class="profile-role">
                    <i class="fas fa-user-tag"></i>
                    <?= htmlspecialchars($role) ?>
                    <?php if ($role === 'Artisan'): ?>
                        <span class="verification-badge <?= $utilisateur['statut_verification'] ? 'verified' : 'unverified' ?>">
                            <i class="fas fa-<?= $utilisateur['statut_verification'] ? 'check-circle' : 'clock' ?>"></i>
                            <?= $utilisateur['statut_verification'] ? 'Vérifié' : 'En attente' ?>
                        </span>
                    <?php endif; ?>
                </p>
                <p class="member-since">
                    <i class="fas fa-calendar"></i>
                    Membre depuis <?= date('F Y', strtotime($utilisateur['date_creation'] ?? $utilisateur['date_naissance'])) ?>
                </p>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?= $messageType ?>">
            <i class="fas fa-<?= $messageType === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <div class="profile-content">
        <!-- Statistics Cards -->
        <div class="stats-grid">
            <?php if ($role === 'Client'): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['commandes'] ?></h3>
                        <p>Commandes</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['wishlist'] ?></h3>
                        <p>Favoris</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['evenements'] ?></h3>
                        <p>Événements</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['montant_depense'], 2) ?>€</h3>
                        <p>Total dépensé</p>
                    </div>
                </div>
            <?php elseif ($role === 'Artisan'): ?>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['oeuvres'] ?></h3>
                        <p>Œuvres</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['oeuvres_vendues'] ?></h3>
                        <p>Vendues</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= $stats['note_moyenne'] ?>/5</h3>
                        <p>Note moyenne</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-euro-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?= number_format($stats['revenus'], 2) ?>€</h3>
                        <p>Revenus</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabs Navigation -->
        <div class="tabs-container">
            <div class="tabs-nav">
                <button class="tab-btn active" onclick="showTab('profile')">
                    <i class="fas fa-user"></i>
                    Informations personnelles
                </button>
                <button class="tab-btn" onclick="showTab('security')">
                    <i class="fas fa-shield-alt"></i>
                    Sécurité
                </button>
                <button class="tab-btn" onclick="showTab('preferences')">
                    <i class="fas fa-cog"></i>
                    Préférences
                </button>
            </div>

            <!-- Profile Tab -->
            <div id="profile-tab" class="tab-content active">
                <div class="form-section">
                    <h2>Informations personnelles</h2>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="prenom">Prénom *</label>
                                <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($utilisateur['prenom'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="nom">Nom *</label>
                                <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($utilisateur['nom'] ?? '') ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($utilisateur['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="telephone">Téléphone</label>
                                <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($utilisateur['telephone'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="adresse">Adresse</label>
                            <textarea id="adresse" name="adresse" rows="3"><?= htmlspecialchars($utilisateur['adresse'] ?? '') ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="ville">Ville</label>
                                <input type="text" id="ville" name="ville" value="<?= htmlspecialchars($utilisateur['ville'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="code_postal">Code postal</label>
                                <input type="text" id="code_postal" name="code_postal" value="<?= htmlspecialchars($utilisateur['code_postal'] ?? '') ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="pays">Pays</label>
                                <input type="text" id="pays" name="pays" value="<?= htmlspecialchars($utilisateur['pays'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label for="date_naissance">Date de naissance</label>
                                <input type="date" id="date_naissance" name="date_naissance" value="<?= $utilisateur['date_naissance'] ?? '' ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="genre">Genre</label>
                            <select id="genre" name="genre">
                                <option value="">Sélectionner...</option>
                                <option value="Homme" <?= ($utilisateur['genre'] ?? '') === 'Homme' ? 'selected' : '' ?>>Homme</option>
                                <option value="Femme" <?= ($utilisateur['genre'] ?? '') === 'Femme' ? 'selected' : '' ?>>Femme</option>
                                <option value="Autre" <?= ($utilisateur['genre'] ?? '') === 'Autre' ? 'selected' : '' ?>>Autre</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Sauvegarder les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="tab-content">
                <div class="form-section">
                    <h2>Changer le mot de passe</h2>
                    <form method="POST" class="profile-form">
                        <input type="hidden" name="action" value="change_password">
                        
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel *</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe *</label>
                            <input type="password" id="new_password" name="new_password" required minlength="6">
                            <small class="form-hint">Minimum 6 caractères</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-key"></i>
                                Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Preferences Tab -->
            <div id="preferences-tab" class="tab-content">
                <div class="form-section">
                    <h2>Préférences</h2>
                    <div class="preferences-grid">
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>Notifications par email</h3>
                                <p>Recevoir des notifications par email</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>Newsletter</h3>
                                <p>Recevoir la newsletter hebdomadaire</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                        
                        <div class="preference-item">
                            <div class="preference-info">
                                <h3>Promotions</h3>
                                <p>Recevoir les offres promotionnelles</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="js/profil.js"></script>

<?php include 'includes/footer.php'; ?>

</body>
</html>