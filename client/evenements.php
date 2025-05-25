<?php
session_start();
require_once 'includes/conn.php';

// Récupérer les paramètres de filtre
$filtreDate = isset($_GET['date']) ? $_GET['date'] : '';
$filtreLieu = isset($_GET['lieu']) ? $_GET['lieu'] : '';
$rechercheTexte = isset($_GET['recherche']) ? $_GET['recherche'] : '';
$filtreArtisan = isset($_GET['artisan']) ? $_GET['artisan'] : '';

// Construction de la requête SQL
$sql = "SELECT e.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom, 
        u.photo as artisan_photo, a.specialite,
        (SELECT COUNT(*) FROM Clientevenement ce WHERE ce.idEvenement = e.idEvenement) as nb_participants
        FROM Evenement e
        LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
        LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
        WHERE 1=1";

$params = [];
$types = "";

// Appliquer les filtres
if (!empty($filtreDate)) {
    switch($filtreDate) {
        case 'aujourd_hui':
            $sql .= " AND DATE(e.dateDebut) = CURDATE()";
            break;
        case 'cette_semaine':
            $sql .= " AND WEEK(e.dateDebut) = WEEK(CURDATE()) AND YEAR(e.dateDebut) = YEAR(CURDATE())";
            break;
        case 'ce_mois':
            $sql .= " AND MONTH(e.dateDebut) = MONTH(CURDATE()) AND YEAR(e.dateDebut) = YEAR(CURDATE())";
            break;
        case 'a_venir':
            $sql .= " AND e.dateDebut >= CURDATE()";
            break;
    }
}

if (!empty($filtreLieu)) {
    $sql .= " AND e.lieu LIKE ?";
    $params[] = "%$filtreLieu%";
    $types .= "s";
}

if (!empty($rechercheTexte)) {
    $sql .= " AND (e.nomEvenement LIKE ? OR e.description LIKE ? OR e.lieu LIKE ?)";
    $params[] = "%$rechercheTexte%";
    $params[] = "%$rechercheTexte%";
    $params[] = "%$rechercheTexte%";
    $types .= "sss";
}

if (!empty($filtreArtisan)) {
    $sql .= " AND u.nom LIKE ? OR u.prenom LIKE ?";
    $params[] = "%$filtreArtisan%";
    $params[] = "%$filtreArtisan%";
    $types .= "ss";
}

$sql .= " ORDER BY e.mis_en_avant DESC, e.dateDebut ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Récupérer les événements mis en avant
$sql_featured = "SELECT e.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom, 
                 u.photo as artisan_photo, a.specialite,
                 (SELECT COUNT(*) FROM Clientevenement ce WHERE ce.idEvenement = e.idEvenement) as nb_participants
                 FROM Evenement e
                 LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
                 LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                 WHERE e.mis_en_avant = 1 AND e.dateDebut >= CURDATE()
                 ORDER BY e.dateDebut ASC
                 LIMIT 3";

$result_featured = $conn->query($sql_featured);

// Récupérer tous les lieux pour le filtre
$sql_lieux = "SELECT DISTINCT lieu FROM Evenement WHERE lieu IS NOT NULL AND lieu != '' ORDER BY lieu";
$result_lieux = $conn->query($sql_lieux);

function formaterDate($date) {
    $mois = array(
        1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 5 => 'Mai', 6 => 'Juin',
        7 => 'Juil', 8 => 'Août', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
    );
    
    $timestamp = strtotime($date);
    $jour = date('d', $timestamp);
    $moisNum = date('n', $timestamp);
    $annee = date('Y', $timestamp);
    
    return $jour . ' ' . $mois[$moisNum] . ' ' . $annee;
}

function formaterDateComplete($dateDebut, $dateFin = null) {
    $debut = formaterDate($dateDebut);
    if ($dateFin && $dateFin !== $dateDebut) {
        return $debut . ' - ' . formaterDate($dateFin);
    }
    return $debut;
}

function getStatutEvenement($dateDebut, $dateFin = null) {
    $maintenant = time();
    $debut = strtotime($dateDebut);
    $fin = $dateFin ? strtotime($dateFin) : $debut;
    
    if ($maintenant < $debut) {
        return 'a-venir';
    } elseif ($maintenant >= $debut && $maintenant <= $fin + 86400) {
        return 'en-cours';
    } else {
        return 'termine';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Événements Artistiques - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/evenements.css">
    <script src="js/modern.js" ></script>
    <script src="js/modern.js" ></script>
    <script src="js/evenement-details.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Section Hero -->
        <section class="hero-section">
            <div class="hero-background">
                <div class="hero-overlay"></div>
            </div>
            <div class="hero-content">
                <div class="container">
                    <h1>Événements Artistiques</h1>
                    <p>Découvrez des expositions, ateliers et rencontres exceptionnelles avec nos artisans</p>
                    <div class="hero-stats">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo $result->num_rows; ?></span>
                                <span class="stat-label">Événements</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number">
                                    <?php 
                                    $sql_participants = "SELECT COUNT(*) as total FROM Clientevenement";
                                    $result_participants = $conn->query($sql_participants);
                                    echo $result_participants->fetch_assoc()['total'];
                                    ?>
                                </span>
                                <span class="stat-label">Participants</span>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-content">
                                <span class="stat-number"><?php echo $result_featured->num_rows; ?></span>
                                <span class="stat-label">À la une</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <!-- Événements à la une -->
            <?php if ($result_featured->num_rows > 0): ?>
            <section class="featured-events">
                <div class="section-header">
                    <h2><i class="fas fa-star"></i> Événements à la une</h2>
                    <p>Ne manquez pas ces événements exceptionnels</p>
                </div>
                
                <div class="featured-grid">
                    <?php while ($featured = $result_featured->fetch_assoc()): ?>
                        <div class="featured-card" onclick="window.location.href='evenement-details.php?id=<?php echo $featured['idEvenement']; ?>'">
                            <div class="featured-image">
                                <img src="../images/evenement.png">
                                <div class="featured-badge">
                                    <i class="fas fa-star"></i>
                                    <span>À la une</span>
                                </div>
                                <div class="featured-date">
                                    <div class="date-day"><?php echo date('d', strtotime($featured['dateDebut'])); ?></div>
                                    <div class="date-month"><?php echo date('M', strtotime($featured['dateDebut'])); ?></div>
                                </div>
                            </div>
                            
                            <div class="featured-content">
                                <h3><?php echo htmlspecialchars($featured['nomEvenement']); ?></h3>
                                <p class="featured-description">
                                    <?php echo htmlspecialchars(substr($featured['description'], 0, 120)) . '...'; ?>
                                </p>
                                
                                <div class="featured-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars($featured['lieu']); ?></span>
                                    </div>
                                    <?php if ($featured['artisan_nom']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-user"></i>
                                        <span><?php echo htmlspecialchars($featured['artisan_prenom'] . ' ' . $featured['artisan_nom']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span><?php echo $featured['nb_participants']; ?> participants</span>
                                    </div>
                                </div>
                                
                                <div class="featured-actions">
                                    <span class="event-status status-<?php echo getStatutEvenement($featured['dateDebut'], $featured['dateFin']); ?>">
                                        <?php 
                                        $statut = getStatutEvenement($featured['dateDebut'], $featured['dateFin']);
                                        echo $statut === 'a-venir' ? 'À venir' : ($statut === 'en-cours' ? 'En cours' : 'Terminé');
                                        ?>
                                    </span>
                                    <button class="btn-participate" data-event="<?php echo $featured['idEvenement']; ?>">
                                        <i class="fas fa-plus"></i> Participer
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Filtres -->
            <section class="filters-section">
                <div class="filters-container">
                    <form method="GET" class="filters-form">
                        <div class="filter-group">
                            <input type="text" name="recherche" placeholder="Rechercher un événement..." 
                                   value="<?php echo htmlspecialchars($rechercheTexte); ?>" class="search-input">
                        </div>
                        
                        <div class="filter-group">
                            <select name="date" class="filter-select">
                                <option value="">Toutes les dates</option>
                                <option value="aujourd_hui" <?php echo $filtreDate === 'aujourd_hui' ? 'selected' : ''; ?>>Aujourd'hui</option>
                                <option value="cette_semaine" <?php echo $filtreDate === 'cette_semaine' ? 'selected' : ''; ?>>Cette semaine</option>
                                <option value="ce_mois" <?php echo $filtreDate === 'ce_mois' ? 'selected' : ''; ?>>Ce mois</option>
                                <option value="a_venir" <?php echo $filtreDate === 'a_venir' ? 'selected' : ''; ?>>À venir</option>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <select name="lieu" class="filter-select">
                                <option value="">Tous les lieux</option>
                                <?php while ($lieu = $result_lieux->fetch_assoc()): ?>
                                    <option value="<?php echo htmlspecialchars($lieu['lieu']); ?>"
                                            <?php echo $filtreLieu === $lieu['lieu'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($lieu['lieu']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="filter-group">
                            <input type="text" name="artisan" placeholder="Nom de l'artisan..." 
                                   value="<?php echo htmlspecialchars($filtreArtisan); ?>" class="filter-input">
                        </div>
                        
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-search"></i> Filtrer
                        </button>
                        
                        <a href="evenements.php" class="btn-outline">
                            <i class="fas fa-times"></i> Réinitialiser
                        </a>
                    </form>
                </div>
            </section>

            <!-- Liste des événements -->
            <section class="events-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Tous les événements</h2>
                    <div class="view-options">
                        <button class="view-btn active" data-view="grid">
                            <i class="fas fa-th"></i>
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i>
                        </button>
                    </div>
                </div>
                
                <?php if ($result->num_rows > 0): ?>
                    <div class="events-grid" id="events-container">
                        <?php while ($event = $result->fetch_assoc()): ?>
                            <div class="event-card" data-id="<?php echo $event['idEvenement']; ?>">
                                <div class="event-image">
                                    <img src="images/events/Fatima_soukhouna.jpeg" 
                                         alt="<?php echo htmlspecialchars($event['nomEvenement']); ?>"
                                         onerror="this.src='images/event-placeholder.jpg'">
                                    
                                    <div class="event-date-badge">
                                        <div class="date-day"><?php echo date('d', strtotime($event['dateDebut'])); ?></div>
                                        <div class="date-month"><?php echo date('M', strtotime($event['dateDebut'])); ?></div>
                                    </div>
                                    
                                    <?php if ($event['mis_en_avant']): ?>
                                        <div class="featured-ribbon">
                                            <i class="fas fa-star"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="event-overlay">
                                        <a href="evenement-details.php?id=<?php echo $event['idEvenement']; ?>" class="btn-view">
                                            <i class="fas fa-eye"></i> Voir détails
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="event-content">
                                    <div class="event-header">
                                        <h3 class="event-title"><?php echo htmlspecialchars($event['nomEvenement']); ?></h3>
                                        <span class="event-status status-<?php echo getStatutEvenement($event['dateDebut'], $event['dateFin']); ?>">
                                            <?php 
                                            $statut = getStatutEvenement($event['dateDebut'], $event['dateFin']);
                                            echo $statut === 'a-venir' ? 'À venir' : ($statut === 'en-cours' ? 'En cours' : 'Terminé');
                                            ?>
                                        </span>
                                    </div>
                                    
                                    <p class="event-description">
                                        <?php echo htmlspecialchars(substr($event['description'], 0, 100)) . '...'; ?>
                                    </p>
                                    
                                    <div class="event-meta">
                                        <div class="meta-row">
                                            <i class="fas fa-calendar-alt"></i>
                                            <span><?php echo formaterDateComplete($event['dateDebut'], $event['dateFin']); ?></span>
                                        </div>
                                        <div class="meta-row">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?php echo htmlspecialchars($event['lieu']); ?></span>
                                        </div>
                                        <?php if ($event['artisan_nom']): ?>
                                        <div class="meta-row">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($event['artisan_prenom'] . ' ' . $event['artisan_nom']); ?></span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="meta-row">
                                            <i class="fas fa-users"></i>
                                            <span><?php echo $event['nb_participants']; ?> participant<?php echo $event['nb_participants'] > 1 ? 's' : ''; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="event-actions">
                                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                                            <button class="btn-participate" data-event="<?php echo $event['idEvenement']; ?>">
                                                <i class="fas fa-plus"></i> Participer
                                            </button>
                                            <button class="btn-favorite" data-event="<?php echo $event['idEvenement']; ?>">
                                                <i class="far fa-heart"></i>
                                            </button>
                                        <?php else: ?>
                                            <a href="connexion.php" class="btn-participate">
                                                <i class="fas fa-user"></i> Se connecter
                                            </a>
                                        <?php endif; ?>
                                        
                                        <button class="btn-share" onclick="partagerEvenement(<?php echo $event['idEvenement']; ?>)">
                                            <i class="fas fa-share-alt"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="no-events">
                        <div class="no-events-icon">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <h3>Aucun événement trouvé</h3>
                        <p>Essayez de modifier vos critères de recherche ou consultez tous nos événements</p>
                        <a href="evenements.php" class="btn-primary">
                            <i class="fas fa-calendar-alt"></i> Voir tous les événements
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Section Newsletter -->
            <section class="newsletter-section">
                <div class="newsletter-content">
                    <div class="newsletter-text">
                        <h2>Ne manquez aucun événement</h2>
                        <p>Abonnez-vous à notre newsletter pour être informé des prochains événements artistiques</p>
                    </div>
                    <form class="newsletter-form">
                        <input type="email" placeholder="Votre adresse email" class="newsletter-input" required>
                        <button type="submit" class="btn-newsletter">
                            <i class="fas fa-bell"></i> S'abonner
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/evenements.js"></script>
</body>
</html>