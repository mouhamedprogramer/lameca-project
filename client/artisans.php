<?php
session_start();
require_once 'includes/conn.php';

// Récupérer les paramètres de filtre
$filtreSpecialite = isset($_GET['specialite']) ? $_GET['specialite'] : '';
$rechercheTexte = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'recent';

// Construction de la requête SQL pour récupérer les artisans
$sql = "SELECT a.*, u.nom, u.prenom, u.email, u.telephone, u.ville, u.pays, u.photo,
        (SELECT COUNT(*) FROM Oeuvre o WHERE o.idArtisan = a.idArtisan AND o.disponibilite = 1) as nb_oeuvres,
        (SELECT AVG(av.notation) FROM Avisartisan av WHERE av.idArtisan = a.idArtisan) as note_moyenne,
        (SELECT COUNT(*) FROM Avisartisan av WHERE av.idArtisan = a.idArtisan) as nb_avis
        FROM Artisan a 
        JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur 
        WHERE 1=1";

$params = [];
$types = "";

// Appliquer les filtres
if (!empty($filtreSpecialite)) {
    $sql .= " AND a.specialite LIKE ?";
    $params[] = "%$filtreSpecialite%";
    $types .= "s";
}

if (!empty($rechercheTexte)) {
    $sql .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR a.specialite LIKE ?)";
    $params[] = "%$rechercheTexte%";
    $params[] = "%$rechercheTexte%";
    $params[] = "%$rechercheTexte%";
    $types .= "sss";
}

// Appliquer le tri
switch($tri) {
    case 'nom':
        $sql .= " ORDER BY u.nom ASC, u.prenom ASC";
        break;
    case 'specialite':
        $sql .= " ORDER BY a.specialite ASC";
        break;
    case 'note':
        $sql .= " ORDER BY note_moyenne DESC, nb_avis DESC";
        break;
    case 'oeuvres':
        $sql .= " ORDER BY nb_oeuvres DESC";
        break;
    default:
        $sql .= " ORDER BY u.idUtilisateur DESC"; // Plus récents
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Récupérer toutes les spécialités pour le filtre
$sql_specialites = "SELECT DISTINCT a.specialite 
                   FROM Artisan a 
                   WHERE a.specialite IS NOT NULL AND a.specialite != ''
                   ORDER BY a.specialite";
$result_specialites = $conn->query($sql_specialites);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos Artisans - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/artisans.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Section Hero -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Découvrez nos Artisans</h1>
                <p>Rencontrez les créateurs talentueux qui donnent vie à l'art authentique</p>
                <div class="hero-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $result->num_rows; ?></span>
                        <span class="stat-label">Artisans</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php 
                            $sql_total_oeuvres = "SELECT COUNT(*) as total FROM Oeuvre o JOIN Artisan a ON o.idArtisan = a.idArtisan WHERE o.disponibilite = 1";
                            $result_total = $conn->query($sql_total_oeuvres);
                            echo $result_total->fetch_assoc()['total'];
                            ?>
                        </span>
                        <span class="stat-label">Œuvres</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php 
                            $sql_specialites_count = "SELECT COUNT(DISTINCT specialite) as total FROM Artisan WHERE specialite IS NOT NULL";
                            $result_spec = $conn->query($sql_specialites_count);
                            echo $result_spec->fetch_assoc()['total'];
                            ?>
                        </span>
                        <span class="stat-label">Spécialités</span>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <!-- Filtres -->
            <div class="filters-section">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <input type="text" name="recherche" placeholder="Rechercher un artisan..." 
                               value="<?php echo htmlspecialchars($rechercheTexte); ?>" class="search-input">
                    </div>
                    
                    <div class="filter-group">
                        <select name="specialite" class="filter-select">
                            <option value="">Toutes les spécialités</option>
                            <?php while ($specialite = $result_specialites->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($specialite['specialite']); ?>"
                                        <?php echo $filtreSpecialite === $specialite['specialite'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($specialite['specialite']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="tri" class="filter-select">
                            <option value="recent" <?php echo $tri === 'recent' ? 'selected' : ''; ?>>Plus récents</option>
                            <option value="nom" <?php echo $tri === 'nom' ? 'selected' : ''; ?>>Nom (A-Z)</option>
                            <option value="specialite" <?php echo $tri === 'specialite' ? 'selected' : ''; ?>>Spécialité</option>
                            <option value="note" <?php echo $tri === 'note' ? 'selected' : ''; ?>>Mieux notés</option>
                            <option value="oeuvres" <?php echo $tri === 'oeuvres' ? 'selected' : ''; ?>>Plus d'œuvres</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-search"></i> Filtrer
                    </button>
                    
                    <a href="artisans.php" class="btn-outline">
                        <i class="fas fa-times"></i> Réinitialiser
                    </a>
                </form>
            </div>

            <!-- Grille des artisans -->
            <div class="artisans-grid">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($artisan = $result->fetch_assoc()): ?>
                        <div class="artisan-card" data-id="<?php echo $artisan['idArtisan']; ?>">
                            <div class="artisan-header">
                                <div class="artisan-photo">
                                    <?php 
                                    $photo_src = !empty($artisan['photo'])? '../images/' . $artisan['photo'] : 'images/profile-placeholder.jpg';
                                    ?>
                                    <img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?>">
                                    
                                    <?php if ($artisan['statut_verification']): ?>
                                        <div class="verified-badge" title="Artisan vérifié">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="artisan-info">
                                    <h3 class="artisan-name"><?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?></h3>
                                    <p class="artisan-specialite"><?php echo htmlspecialchars($artisan['specialite']); ?></p>
                                    <div class="artisan-location">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($artisan['ville'] . ', ' . $artisan['pays']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if ($artisan['note_moyenne']): ?>
                                <div class="artisan-rating">
                                    <div class="stars">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?php echo $i <= round($artisan['note_moyenne']) ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="rating-text">
                                        <?php echo number_format($artisan['note_moyenne'], 1); ?> 
                                        (<?php echo $artisan['nb_avis']; ?> avis)
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="artisan-stats">
                                <div class="stat">
                                    <i class="fas fa-palette"></i>
                                    <span><?php echo $artisan['nb_oeuvres']; ?> œuvre<?php echo $artisan['nb_oeuvres'] > 1 ? 's' : ''; ?></span>
                                </div>
                                <?php if (!empty($artisan['certification'])): ?>
                                    <div class="stat">
                                        <i class="fas fa-certificate"></i>
                                        <span>Certifié</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($artisan['portfolio'])): ?>
                                <div class="artisan-description">
                                    <p><?php echo htmlspecialchars(substr($artisan['portfolio'], 0, 120)); ?>...</p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="artisan-actions">
                                <a href="profil-artisan.php?id=<?php echo $artisan['idArtisan']; ?>" class="btn-primary">
                                    <i class="fas fa-user"></i> Voir le profil
                                </a>
                                <a href="oeuvres-artisan.php?id=<?php echo $artisan['idArtisan']; ?>" class="btn-outline">
                                    <i class="fas fa-palette"></i> Ses œuvres
                                </a>
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                                    <button class="btn-message" data-artisan="<?php echo $artisan['idArtisan']; ?>" title="Contacter">
                                        <i class="far fa-envelope"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="no-results">
                        <div class="no-results-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>Aucun artisan trouvé</h3>
                        <p>Essayez de modifier vos critères de recherche ou explorez toutes nos spécialités</p>
                        <a href="artisans.php" class="btn-primary">
                            <i class="fas fa-refresh"></i> Voir tous les artisans
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Section appel à l'action -->
            <div class="cta-section">
                <div class="cta-content">
                    <h2>Vous êtes artisan ?</h2>
                    <p>Rejoignez notre communauté et partagez vos créations avec des passionnés d'art authentique</p>
                    <a href="http://localhost/lameca/artisan/sign_up.php" class="btn-cta">
                        <i class="fas fa-plus-circle"></i> Devenir artisan
                    </a>
                </div>
                <div class="cta-image">
                    <img src="../images/20211028_164306.jpg" alt="Atelier d'artisan">
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Gestion des messages aux artisans
        document.addEventListener('DOMContentLoaded', function() {
            const messageButtons = document.querySelectorAll('.btn-message');
            
            messageButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const artisanId = this.dataset.artisan;
                    window.location.href = `contact-artisan.php?id=${artisanId}`;
                });
            });

            // Animation des cartes au scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            const cards = document.querySelectorAll('.artisan-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
                observer.observe(card);
            });

            // Gestion du clic sur les cartes
            cards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Éviter le clic si on clique sur un bouton
                    if (e.target.closest('button') || e.target.closest('a')) {
                        return;
                    }
                    
                    const artisanId = this.dataset.id;
                    window.location.href = `profil-artisan.php?id=${artisanId}`;
                });
            });
        });

        // Animation des statistiques dans le hero
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const target = parseInt(stat.textContent);
                let current = 0;
                const increment = target / 50;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        stat.textContent = target;
                        clearInterval(timer);
                    } else {
                        stat.textContent = Math.floor(current);
                    }
                }, 40);
            });
        }

        // Lancer l'animation au chargement
        window.addEventListener('load', () => {
            setTimeout(animateStats, 500);
        });
    </script>
</body>
</html>