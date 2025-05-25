<?php
session_start();
require_once 'includes/conn.php';

// Fonction pour récupérer toutes les œuvres
function getOeuvres($conn, $filtre = null, $tri = 'recent') {
    $query = "SELECT o.*, a.idArtisan, u.nom, u.prenom, a.specialite,
              (SELECT url FROM Photooeuvre WHERE idOeuvre = o.idOeuvre LIMIT 1) as photo_principale,
              (SELECT AVG(notation) FROM Avisoeuvre WHERE idOeuvre = o.idOeuvre) as note_moyenne,
              (SELECT COUNT(*) FROM Avisoeuvre WHERE idOeuvre = o.idOeuvre) as nb_avis
              FROM Oeuvre o
              JOIN Artisan a ON o.idArtisan = a.idArtisan
              JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
              WHERE o.disponibilite = TRUE";
    
    // Appliquer les filtres
    if ($filtre) {
        switch($filtre) {
            case 'prix-bas':
                $query .= " AND o.prix < 100";
                break;
            case 'prix-moyen':
                $query .= " AND o.prix BETWEEN 100 AND 500";
                break;
            case 'prix-haut':
                $query .= " AND o.prix > 500";
                break;
        }
    }
    
    // Appliquer le tri
    switch($tri) {
        case 'prix-asc':
            $query .= " ORDER BY o.prix ASC";
            break;
        case 'prix-desc':
            $query .= " ORDER BY o.prix DESC";
            break;
        case 'note':
            $query .= " ORDER BY note_moyenne DESC";
            break;
        default:
            $query .= " ORDER BY o.datePublication DESC";
    }
    
    $result = mysqli_query($conn, $query);
    $oeuvres = [];
    
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $oeuvres[] = $row;
        }
    }
    
    return $oeuvres;
}

// Récupérer les paramètres de filtre et tri
$filtre = isset($_GET['filtre']) ? $_GET['filtre'] : null;
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'recent';

// Récupérer toutes les œuvres
$oeuvres = getOeuvres($conn, $filtre, $tri);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Œuvres - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/oeuvres.css">
    <script src="js/oeuvres.js"></script>
    <script src="js/wishlist.js"></script>

</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Découvrez nos Œuvres d'Art</h1>
                <p>Une collection unique d'œuvres créées par nos artisans talentueux</p>
            </div>
        </section>

        <div class="container">
            <!-- Filtres et Tri -->
            <section class="filters-section">
                <div class="filters-form">
                    <div class="filter-group">
                        <label>Filtrer par prix:</label>
                        <select id="filtre-prix" class="filter-select" onchange="appliquerFiltres()">
                            <option value="">Tous les prix</option>
                            <option value="prix-bas" <?= $filtre == 'prix-bas' ? 'selected' : '' ?>>Moins de 100€</option>
                            <option value="prix-moyen" <?= $filtre == 'prix-moyen' ? 'selected' : '' ?>>100€ - 500€</option>
                            <option value="prix-haut" <?= $filtre == 'prix-haut' ? 'selected' : '' ?>>Plus de 500€</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Trier par:</label>
                        <select id="tri" class="filter-select" onchange="appliquerFiltres()">
                            <option value="recent" <?= $tri == 'recent' ? 'selected' : '' ?>>Plus récents</option>
                            <option value="prix-asc" <?= $tri == 'prix-asc' ? 'selected' : '' ?>>Prix croissant</option>
                            <option value="prix-desc" <?= $tri == 'prix-desc' ? 'selected' : '' ?>>Prix décroissant</option>
                            <option value="note" <?= $tri == 'note' ? 'selected' : '' ?>>Meilleures notes</option>
                        </select>
                    </div>
                    
                    <div class="results-count">
                        <?= count($oeuvres) ?> œuvre(s) trouvée(s)
                    </div>
                </div>
            </section>
            
            <!-- Grille des œuvres -->
            <section class="oeuvres-grid">
                <?php if (!empty($oeuvres)): ?>
                    <?php foreach ($oeuvres as $oeuvre): ?>
                        <article class="oeuvre-card" data-id="<?= $oeuvre['idOeuvre'] ?>">
                            <div class="oeuvre-image">
                                <?php 
                                $image_src = !empty($oeuvre['photo_principale']) ? '../'.$oeuvre['photo_principale'] : 'images/oeuvre-placeholder.jpg';
                                ?>
                                <img src="<?= htmlspecialchars($image_src) ?>" alt="<?= htmlspecialchars($oeuvre['titre']) ?>">
                                
                                <?php if (isset($_SESSION['idUtilisateur']) && $_SESSION['role'] == 'Client'): ?>
                                    <button class="wishlist-btn add-to-wishlist" data-id="<?= $oeuvre['idOeuvre'] ?>" onclick="event.stopPropagation();">
                                        <i class="far fa-heart"></i>
                                    </button>
                                <?php endif; ?>
                                
                                <div class="oeuvre-overlay">
                                    <a href="oeuvre-details.php?id=<?= $oeuvre['idOeuvre'] ?>" class="btn-view" onclick="event.stopPropagation();">
                                        <i class="fas fa-eye"></i> Voir détails
                                    </a>
                                </div>
                            </div>
                            
                            <div class="oeuvre-info">
                                <h3 class="oeuvre-title"><?= htmlspecialchars($oeuvre['titre']) ?></h3>
                                <p class="oeuvre-artisan">Par <?= htmlspecialchars($oeuvre['prenom'] . ' ' . $oeuvre['nom']) ?></p>
                                <p class="oeuvre-specialite"><?= htmlspecialchars($oeuvre['specialite']) ?></p>
                                
                                <?php if ($oeuvre['note_moyenne']): ?>
                                    <div class="rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?= $i <= round($oeuvre['note_moyenne']) ? 'fas' : 'far' ?> fa-star"></i>
                                        <?php endfor; ?>
                                        <span class="nb-avis">(<?= $oeuvre['nb_avis'] ?> avis)</span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="oeuvre-price"><?= number_format($oeuvre['prix'], 2, ',', ' ') ?> €</div>
                                
                                <div class="oeuvre-actions">
                                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                                        <button class="btn-cart add-to-cart" data-id="<?= $oeuvre['idOeuvre'] ?>" onclick="event.stopPropagation();">
                                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                        </button>
                                        <button class="btn-wishlist add-to-wishlist" data-id="<?= $oeuvre['idOeuvre'] ?>" onclick="event.stopPropagation();">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    <?php else: ?>
                                        <a href="connexion.php" class="btn-cart" onclick="event.stopPropagation();">
                                            <i class="fas fa-user"></i> Se connecter pour acheter
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <i class="fas fa-search"></i>
                        <h3>Aucune œuvre trouvée</h3>
                        <p>Essayez de modifier vos critères de recherche</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
        // Fonction pour appliquer les filtres
        function appliquerFiltres() {
            const filtre = document.getElementById('filtre-prix').value;
            const tri = document.getElementById('tri').value;
            
            let url = 'oeuvres.php?';
            if (filtre) url += 'filtre=' + filtre + '&';
            if (tri) url += 'tri=' + tri;
            
            window.location.href = url;
        }

        // Gestion du clic sur les cartes pour rediriger vers les détails
        document.addEventListener('DOMContentLoaded', function() {
            const oeuvreCards = document.querySelectorAll('.oeuvre-card');
            
            oeuvreCards.forEach(card => {
                card.addEventListener('click', function() {
                    const oeuvreId = this.dataset.id;
                    window.location.href = `oeuvre-details.php?id=${oeuvreId}`;
                });
            });
        });
    </script>
    <script src="js/oeuvres.js"></script>
</body>
</html>