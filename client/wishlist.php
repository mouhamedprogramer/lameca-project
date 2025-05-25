<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté en tant que client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    header('Location: connexion.php');
    exit;
}

$idClient = $_SESSION['idUtilisateur'];

// Vérifier si la table wishlist existe
$wishlistTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'wishlist'");
if ($result->num_rows > 0) {
    $wishlistTableExists = true;
}

$favoris = [];
$stats = [
    'total' => 0,
    'valeur_totale' => 0,
    'prix_moyen' => 0,
    'derniere_ajout' => null
];

if ($wishlistTableExists) {
    // Récupérer les œuvres en favoris avec leurs détails
    $sql = "SELECT o.*, w.date_ajout, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom, 
            a.specialite,
            (SELECT url FROM Photooeuvre WHERE idOeuvre = o.idOeuvre LIMIT 1) as photo_principale,
            (SELECT AVG(notation) FROM Avisoeuvre WHERE idOeuvre = o.idOeuvre) as note_moyenne,
            (SELECT COUNT(*) FROM Avisoeuvre WHERE idOeuvre = o.idOeuvre) as nb_avis,
            (SELECT COUNT(*) FROM Commande WHERE idOeuvre = o.idOeuvre AND statut != 'En attente') as nb_ventes
            FROM wishlist w
            JOIN Oeuvre o ON w.idOeuvre = o.idOeuvre
            JOIN Artisan a ON o.idArtisan = a.idArtisan
            JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
            WHERE w.idClient = ? AND o.disponibilite = TRUE
            ORDER BY w.date_ajout DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idClient);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $favoris[] = $row;
        $stats['total']++;
        $stats['valeur_totale'] += $row['prix'];
        
        if (!$stats['derniere_ajout'] || $row['date_ajout'] > $stats['derniere_ajout']) {
            $stats['derniere_ajout'] = $row['date_ajout'];
        }
    }
    
    if ($stats['total'] > 0) {
        $stats['prix_moyen'] = $stats['valeur_totale'] / $stats['total'];
    }
}

function formaterDate($date) {
    if (!$date) return '';
    
    $timestamp = strtotime($date);
    $maintenant = time();
    $difference = $maintenant - $timestamp;
    
    if ($difference < 3600) { // Moins d'1 heure
        $minutes = floor($difference / 60);
        return "Il y a " . ($minutes <= 1 ? "1 minute" : "$minutes minutes");
    } elseif ($difference < 86400) { // Moins d'1 jour
        $heures = floor($difference / 3600);
        return "Il y a " . ($heures <= 1 ? "1 heure" : "$heures heures");
    } elseif ($difference < 2592000) { // Moins d'1 mois
        $jours = floor($difference / 86400);
        return "Il y a " . ($jours <= 1 ? "1 jour" : "$jours jours");
    } else {
        return date('d/m/Y', $timestamp);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ma Liste de Souhaits - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/navbar-enhanced.css">
    <link rel="stylesheet" href="css/wishlist.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="page-hero">
            <div class="hero-background">
                <div class="hero-overlay"></div>
            </div>
            <div class="container">
                <div class="hero-content">
                    <div class="breadcrumb">
                        <a href="accueil.php">Accueil</a>
                        <span>/</span>
                        <a href="oeuvres.php">Œuvres</a>
                        <span>/</span>
                        <span>Ma Liste de Souhaits</span>
                    </div>
                    <h1><i class="fas fa-heart"></i> Ma Liste de Souhaits</h1>
                    <p>Retrouvez toutes vos œuvres d'art favorites en un seul endroit</p>
                    
                    <?php if ($stats['total'] > 0): ?>
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['total'] ?></span>
                            <span class="stat-label">Œuvre<?= $stats['total'] > 1 ? 's' : '' ?> favorite<?= $stats['total'] > 1 ? 's' : '' ?></span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= number_format($stats['valeur_totale'], 0, ',', ' ') ?>€</span>
                            <span class="stat-label">Valeur totale</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= number_format($stats['prix_moyen'], 0, ',', ' ') ?>€</span>
                            <span class="stat-label">Prix moyen</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= formaterDate($stats['derniere_ajout']) ?></span>
                            <span class="stat-label">Dernier ajout</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <div class="container">
            <?php if (!$wishlistTableExists): ?>
                <!-- Message si la table n'existe pas -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Fonctionnalité en cours d'initialisation</h3>
                    <p>Le système de liste de souhaits est en cours de mise en place.</p>
                    <a href="oeuvres.php" class="btn-primary">
                        <i class="fas fa-palette"></i> Découvrir les œuvres
                    </a>
                </div>

            <?php elseif (empty($favoris)): ?>
                <!-- État vide -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-heart-broken"></i>
                    </div>
                    <h3>Votre liste de souhaits est vide</h3>
                    <p>Vous n'avez pas encore ajouté d'œuvres à vos favoris.<br>Explorez notre galerie et ajoutez vos coups de cœur !</p>
                    <a href="oeuvres.php" class="btn-primary">
                        <i class="fas fa-palette"></i> Découvrir les œuvres
                    </a>
                </div>

            <?php else: ?>
                <!-- Actions de gestion -->
                <div class="wishlist-actions">
                    <div class="view-options">
                        <button class="view-btn active" data-view="grid">
                            <i class="fas fa-th-large"></i> Grille
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i> Liste
                        </button>
                    </div>
                    
                    <div class="sort-options">
                        <select class="sort-select" id="sortBy">
                            <option value="recent">Plus récemment ajouté</option>
                            <option value="price-asc">Prix croissant</option>
                            <option value="price-desc">Prix décroissant</option>
                            <option value="rating">Meilleures notes</option>
                            <option value="name">Nom A-Z</option>
                        </select>
                    </div>
                    
                    <div class="bulk-actions">
                        <button class="btn-outline" id="selectAll">
                            <i class="far fa-check-square"></i> Tout sélectionner
                        </button>
                        <button class="btn-outline" id="addSelectedToCart" style="display: none;">
                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                        </button>
                        <button class="btn-danger" id="removeSelected" style="display: none;">
                            <i class="fas fa-trash"></i> Retirer la sélection
                        </button>
                    </div>
                </div>

                <!-- Grille des favoris -->
                <div class="wishlist-grid" id="wishlistGrid">
                    <?php foreach ($favoris as $oeuvre): ?>
                        <div class="wishlist-item" 
                             data-id="<?= $oeuvre['idOeuvre'] ?>"
                             data-price="<?= $oeuvre['prix'] ?>"
                             data-rating="<?= $oeuvre['note_moyenne'] ?: 0 ?>"
                             data-name="<?= htmlspecialchars($oeuvre['titre']) ?>"
                             data-date="<?= $oeuvre['date_ajout'] ?>">
                            
                            <div class="item-checkbox">
                                <input type="checkbox" class="oeuvre-checkbox" value="<?= $oeuvre['idOeuvre'] ?>">
                            </div>
                            
                            <div class="item-image">
                                <?php 
                                $image_src = !empty($oeuvre['photo_principale']) ? $oeuvre['photo_principale'] : 'images/oeuvre-placeholder.jpg';
                                ?>
                                <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($oeuvre['titre']) ?>">
                                
                                <div class="image-overlay">
                                    <a href="oeuvre-details.php?id=<?= $oeuvre['idOeuvre'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <button class="btn-quick-view" data-id="<?= $oeuvre['idOeuvre'] ?>">
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                </div>
                                
                                <div class="price-tag">
                                    <?= number_format($oeuvre['prix'], 0, ',', ' ') ?>€
                                </div>
                                
                                <?php if ($oeuvre['nb_ventes'] > 0): ?>
                                <div class="popularity-badge">
                                    <i class="fas fa-fire"></i> Populaire
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="item-content">
                                <div class="item-header">
                                    <h3 class="item-title">
                                        <a href="oeuvre-details.php?id=<?= $oeuvre['idOeuvre'] ?>">
                                            <?= htmlspecialchars($oeuvre['titre']) ?>
                                        </a>
                                    </h3>
                                    <button class="btn-remove-wishlist" data-id="<?= $oeuvre['idOeuvre'] ?>" title="Retirer des favoris">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                </div>
                                
                                <div class="item-meta">
                                    <div class="artisan-info">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?= htmlspecialchars($oeuvre['artisan_prenom'] . ' ' . $oeuvre['artisan_nom']) ?></span>
                                    </div>
                                    <div class="specialite">
                                        <i class="fas fa-palette"></i>
                                        <span><?= htmlspecialchars($oeuvre['specialite']) ?></span>
                                    </div>
                                </div>
                                
                                <?php if ($oeuvre['note_moyenne']): ?>
                                <div class="rating">
                                    <div class="stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?= $i <= round($oeuvre['note_moyenne']) ? 'fas' : 'far' ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-text">
                                        <?= number_format($oeuvre['note_moyenne'], 1) ?>/5 
                                        (<?= $oeuvre['nb_avis'] ?> avis)
                                    </span>
                                </div>
                                <?php endif; ?>
                                
                                <div class="item-description">
                                    <?= htmlspecialchars(substr($oeuvre['description'], 0, 100)) ?>...
                                </div>
                                
                                <div class="wishlist-info">
                                    <i class="fas fa-heart"></i>
                                    <span>Ajouté <?= formaterDate($oeuvre['date_ajout']) ?></span>
                                </div>
                            </div>
                            
                            <div class="item-actions">
                                <button class="btn-primary btn-add-cart" data-id="<?= $oeuvre['idOeuvre'] ?>">
                                    <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                </button>
                                
                                <div class="secondary-actions">
                                    <button class="btn-outline btn-share" data-id="<?= $oeuvre['idOeuvre'] ?>">
                                        <i class="fas fa-share-alt"></i> Partager
                                    </button>
                                    <a href="oeuvre-details.php?id=<?= $oeuvre['idOeuvre'] ?>" class="btn-outline">
                                        <i class="fas fa-info-circle"></i> Détails
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Recommandations -->
                <section class="recommendations">
                    <h2><i class="fas fa-magic"></i> Vous pourriez aussi aimer</h2>
                    <p>Basé sur vos goûts et vos œuvres favorites</p>
                    
                    <div class="recommendations-grid">
                        <?php
                        // Récupérer quelques œuvres similaires basées sur les artisans favoris
                        $artistesFavoris = array_unique(array_column($favoris, 'idArtisan'));
                        if (!empty($artistesFavoris)) {
                            $placeholders = str_repeat('?,', count($artistesFavoris) - 1) . '?';
                            $sqlReco = "SELECT o.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom,
                                       (SELECT url FROM Photooeuvre WHERE idOeuvre = o.idOeuvre LIMIT 1) as photo_principale
                                       FROM Oeuvre o
                                       JOIN Artisan a ON o.idArtisan = a.idArtisan
                                       JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                                       WHERE o.idArtisan IN ($placeholders) 
                                       AND o.disponibilite = TRUE
                                       AND o.idOeuvre NOT IN (SELECT idOeuvre FROM wishlist WHERE idClient = ?)
                                       ORDER BY RAND()
                                       LIMIT 4";
                            
                            $stmtReco = $conn->prepare($sqlReco);
                            $params = array_merge($artistesFavoris, [$idClient]);
                            $types = str_repeat('i', count($params));
                            $stmtReco->bind_param($types, ...$params);
                            $stmtReco->execute();
                            $recos = $stmtReco->get_result();
                            
                            while ($reco = $recos->fetch_assoc()):  
                        ?>
                                <div class="reco-card">
                                    <div class="reco-image">
                                        <img src="<?= htmlspecialchars($reco['photo_principale'] ?: 'images/oeuvre-placeholder.jpg') ?>" 
                                             alt="<?= htmlspecialchars($reco['titre']) ?>">
                                    </div>
                                    <div class="reco-content">
                                        <h4><?= htmlspecialchars($reco['titre']) ?></h4>
                                        <p><?= htmlspecialchars($reco['artisan_prenom'] . ' ' . $reco['artisan_nom']) ?></p>
                                        <div class="reco-price"><?= number_format($reco['prix'], 0, ',', ' ') ?>€</div>
                                        <div class="reco-actions">
                                            <button class="btn-add-wishlist" data-id="<?= $reco['idOeuvre'] ?>">
                                                <i class="far fa-heart"></i>
                                            </button>
                                            <a href="oeuvre-details.php?id=<?= $reco['idOeuvre'] ?>" class="btn-view-reco">
                                                Voir
                                            </a>
                                        </div>
                                    </div>
                                </div>
                        <?php 
                            endwhile;
                        }
                        ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/wishlist.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initWishlistPage();
        });
    </script>
</body>
</html>