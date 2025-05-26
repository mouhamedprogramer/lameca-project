<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'ID de l'artisan est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: artisans.php');
    exit;
}

$idArtisan = intval($_GET['id']);

// Récupérer les informations de l'artisan
$sql_artisan = "SELECT a.*, u.nom, u.prenom, u.email, u.telephone, u.ville, u.pays, u.photo
                FROM Artisan a 
                JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur 
                WHERE a.idArtisan = ?";

$stmt_artisan = $conn->prepare($sql_artisan);
$stmt_artisan->bind_param("i", $idArtisan);
$stmt_artisan->execute();
$result_artisan = $stmt_artisan->get_result();

if ($result_artisan->num_rows === 0) {
    header('Location: artisans.php');
    exit;
}

$artisan = $result_artisan->fetch_assoc();

// Récupérer les paramètres de filtre
$tri = isset($_GET['tri']) ? $_GET['tri'] : 'recent';
$recherche = isset($_GET['recherche']) ? $_GET['recherche'] : '';

// Construction de la requête SQL pour récupérer les œuvres
$sql_oeuvres = "SELECT o.*, 
                (SELECT url FROM Photooeuvre p WHERE p.idOeuvre = o.idOeuvre LIMIT 1) as photo_principale,
                (SELECT COUNT(*) FROM Photooeuvre p WHERE p.idOeuvre = o.idOeuvre) as nb_photos
                FROM Oeuvre o 
                WHERE o.idArtisan = ?";

$params = [$idArtisan];
$types = "i";

// Appliquer le filtre de recherche
if (!empty($recherche)) {
    $sql_oeuvres .= " AND (o.titre LIKE ? OR o.description LIKE ?)";
    $params[] = "%$recherche%";
    $params[] = "%$recherche%";
    $types .= "ss";
}

// Appliquer le tri
switch($tri) {
    case 'titre':
        $sql_oeuvres .= " ORDER BY o.titre ASC";
        break;
    case 'prix_asc':
        $sql_oeuvres .= " ORDER BY o.prix ASC";
        break;
    case 'prix_desc':
        $sql_oeuvres .= " ORDER BY o.prix DESC";
        break;
    case 'disponible':
        $sql_oeuvres .= " ORDER BY o.disponibilite DESC, o.datePublication DESC";
        break;
    default:
        $sql_oeuvres .= " ORDER BY o.datePublication DESC"; // Plus récentes
}

$stmt_oeuvres = $conn->prepare($sql_oeuvres);
$stmt_oeuvres->bind_param($types, ...$params);
$stmt_oeuvres->execute();
$result_oeuvres = $stmt_oeuvres->get_result();

// Statistiques de l'artisan
$sql_stats = "SELECT 
                COUNT(*) as total_oeuvres,
                COUNT(CASE WHEN disponibilite = 1 THEN 1 END) as oeuvres_disponibles,
                AVG(prix) as prix_moyen,
                MIN(prix) as prix_min,
                MAX(prix) as prix_max
              FROM Oeuvre 
              WHERE idArtisan = ?";

$stmt_stats = $conn->prepare($sql_stats);
$stmt_stats->bind_param("i", $idArtisan);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

function formaterPrix($prix) {
    return number_format($prix, 0, ',', ' ') . '€';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Œuvres de <?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?> - Artisano</title>
    <meta name="description" content="Découvrez toutes les œuvres de <?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?>, artisan spécialisé en <?php echo htmlspecialchars($artisan['specialite']); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    
    <style>
        /* ===============================================
           STYLES POUR LA PAGE OEUVRES ARTISAN - VERSION FIXE
           =============================================== */
        
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --card-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --hover-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            --border-radius: 20px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* CORRECTION GLOBALE - Fixer tous les composants */
        * {
            will-change: auto !important;
        }
        
        html, body {
            transform: none !important;
            position: relative;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .main-content {
            padding-top: 2rem;
            transform: none !important;
        }
        
        /* Hero Section - FIXE */
        .artist-hero {
            background: var(--primary-gradient);
            color: white;
            padding: 4rem 0 2rem;
            position: relative;
            overflow: hidden;
            transform: none !important;
            will-change: auto;
        }
        
        .artist-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="a" cx="50%" cy="0%" r="100%"><stop offset="0%" stop-color="%23fff" stop-opacity="0.1"/><stop offset="100%" stop-color="%23fff" stop-opacity="0"/></radialGradient></defs><rect width="100" height="20" fill="url(%23a)"/></svg>');
            opacity: 0.3;
        }
        
        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            position: relative;
            z-index: 1;
            transform: none !important;
        }
        
        .breadcrumb {
            margin-bottom: 2rem;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .breadcrumb a {
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }
        
        .breadcrumb a:hover {
            opacity: 0.8;
        }
        
        .breadcrumb span {
            margin: 0 0.5rem;
            opacity: 0.7;
        }
        
        .artist-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            transform: none !important;
        }
        
        .artist-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }
        
        .artist-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .artist-info h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }
        
        .artist-specialty {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 1rem;
            font-weight: 500;
        }
        
        .artist-location {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            opacity: 0.8;
            font-size: 0.95rem;
        }
        
        .hero-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            transform: none !important;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transform: none !important;
        }
        
        .stat-number {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        /* Filters Section - FIXE */
        .filters-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-top: -3rem;
            position: relative;
            z-index: 2;
            margin-bottom: 3rem;
            transform: none !important;
            will-change: auto;
        }
        
        .filters-form {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .search-input, .filter-select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: var(--transition);
            background: white;
        }
        
        .search-input:focus, .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-primary, .btn-outline {
            padding: 0.8rem 1.5rem;
            border-radius: 12px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: 2px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
            transform: none;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px) !important;
            box-shadow: var(--hover-shadow);
        }
        
        .btn-outline {
            background: white;
            color: #667eea;
            border-color: #667eea;
        }
        
        .btn-outline:hover {
            background: #667eea;
            color: white;
        }
        
        /* Gallery Grid - FIXE */
        .gallery-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            transform: none !important;
        }
        
        .gallery-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
            transform: none !important;
        }
        
        .gallery-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
        }
        
        .results-count {
            color: #718096;
            font-size: 0.95rem;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
            transform: none !important;
        }
        
        .artwork-card {
            background: white;
            border-radius: var(--border-radius);
            overflow: hidden;
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            cursor: pointer;
            position: relative;
            transform: none !important;
            will-change: transform, box-shadow;
        }
        
        .artwork-card:hover {
            transform: translateY(-5px) !important;
            box-shadow: var(--hover-shadow);
        }
        
        .artwork-image-container {
            position: relative;
            height: 250px;
            overflow: hidden;
        }
        
        .artwork-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
            transform: none;
        }
        
        .artwork-card:hover .artwork-image {
            transform: scale(1.05);
        }
        
        .artwork-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            opacity: 0;
            transition: var(--transition);
        }
        
        .artwork-card:hover .artwork-overlay {
            opacity: 1;
        }
        
        .overlay-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            color: #333;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            transform: none;
        }
        
        .overlay-btn:hover {
            background: white;
            transform: scale(1.1) !important;
        }
        
        .artwork-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #48bb78;
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .artwork-badge.unavailable {
            background: #e53e3e;
        }
        
        .photo-count {
            position: absolute;
            top: 1rem;
            left: 1rem;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .artwork-content {
            padding: 1.5rem;
        }
        
        .artwork-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .artwork-description {
            color: #718096;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .artwork-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
        }
        
        .artwork-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: #667eea;
        }
        
        .artwork-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-btn {
            width: 35px;
            height: 35px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #718096;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            transform: none;
        }
        
        .action-btn:hover {
            background: #f7fafc;
            color: #667eea;
            border-color: #667eea;
        }
        
        /* No Results */
        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: #718096;
        }
        
        .no-results-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .no-results h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }
        
        /* Back Button - FIXE */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem 1.5rem;
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            text-decoration: none;
            border-radius: 12px;
            transition: var(--transition);
            margin-bottom: 2rem;
            transform: none;
        }
        
        .back-button:hover {
            background: white;
            transform: translateY(-2px) !important;
            box-shadow: var(--card-shadow);
        }
        
        /* Modal - FIXE */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(5px);
            transform: none !important;
        }
        
        .modal-content {
            position: relative;
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: var(--border-radius);
            width: 90%;
            max-width: 800px;
            max-height: 85vh;
            overflow-y: auto;
        }
        
        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 1001;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Notifications - FIXES */
        .notification {
            position: fixed !important;
            top: 20px !important;
            right: 20px !important;
            padding: 15px 20px;
            border-radius: 12px;
            color: white;
            font-weight: 500;
            z-index: 10000 !important;
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(400px);
            transition: transform 0.3s ease;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            font-family: 'Poppins', sans-serif;
        }
        
        .notification.show {
            transform: translateX(0) !important;
        }
        
        .notification-success {
            background: linear-gradient(135deg, #48bb78, #38a169);
        }
        
        .notification-error {
            background: linear-gradient(135deg, #e53e3e, #c53030);
        }
        
        .notification-info {
            background: linear-gradient(135deg, #4299e1, #3182ce);
        }
        
        /* Animation d'apparition fixe */
        @keyframes fadeInUpFixed {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .artwork-card {
            animation: fadeInUpFixed 0.6s ease-out;
            animation-fill-mode: both;
        }
        
        /* Classe pour marquer les éléments visibles */
        .visible {
            opacity: 1 !important;
        }
        
        /* Responsive Design - FIXE */
        @media (max-width: 768px) {
            .artist-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
                transform: none !important;
            }
            
            .artist-info h1 {
                font-size: 2rem;
            }
            
            .hero-stats {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
                transform: none !important;
            }
            
            .filters-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                min-width: auto;
            }
            
            .gallery-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                transform: none !important;
            }
            
            .gallery-header {
                flex-direction: column;
                align-items: flex-start;
                transform: none !important;
            }
            
            .artwork-card {
                transform: none !important;
            }
            
            .artwork-card:hover {
                transform: translateY(-3px) !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Hero Section -->
        <section class="artist-hero">
            <div class="hero-container">
                <!-- Breadcrumb -->
                <nav class="breadcrumb">
                    <a href="accueil.php">Accueil</a>
                    <span>/</span>
                    <a href="artisans.php">Artisans</a>
                    <span>/</span>
                    <a href="profil-artisan.php?id=<?php echo $idArtisan; ?>">
                        <?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?>
                    </a>
                    <span>/</span>
                    <span>Œuvres</span>
                </nav>

                <div class="artist-header">
                    <div class="artist-photo">
                        <?php 
                        $photo_src = !empty($artisan['photo']) ? '../images/' . $artisan['photo'] : 'images/profile-placeholder.jpg';
                        ?>
                        <img src="<?php echo htmlspecialchars($photo_src); ?>" 
                             alt="<?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?>">
                    </div>
                    
                    <div class="artist-info">
                        <h1><?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?></h1>
                        <div class="artist-specialty"><?php echo htmlspecialchars($artisan['specialite']); ?></div>
                        <div class="artist-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($artisan['ville'] . ', ' . $artisan['pays']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="hero-stats">
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $stats['total_oeuvres']; ?></span>
                        <span class="stat-label">Œuvre<?php echo $stats['total_oeuvres'] > 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo $stats['oeuvres_disponibles']; ?></span>
                        <span class="stat-label">Disponible<?php echo $stats['oeuvres_disponibles'] > 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number"><?php echo formaterPrix($stats['prix_moyen']); ?></span>
                        <span class="stat-label">Prix moyen</span>
                    </div>
                    <div class="stat-card">
                        <span class="stat-number">
                            <?php echo formaterPrix($stats['prix_min']); ?> - <?php echo formaterPrix($stats['prix_max']); ?>
                        </span>
                        <span class="stat-label">Gamme de prix</span>
                    </div>
                </div>
            </div>
        </section>
        <br><br><br><br>
        <!-- Filters Section -->
        <div class="filters-section">
            <form method="GET" class="filters-form">
                <input type="hidden" name="id" value="<?php echo $idArtisan; ?>">
                
                <div class="filter-group">
                    <input type="text" name="recherche" placeholder="Rechercher dans les œuvres..." 
                           value="<?php echo htmlspecialchars($recherche); ?>" class="search-input">
                </div>
                
                <div class="filter-group">
                    <select name="tri" class="filter-select">
                        <option value="recent" <?php echo $tri === 'recent' ? 'selected' : ''; ?>>Plus récentes</option>
                        <option value="titre" <?php echo $tri === 'titre' ? 'selected' : ''; ?>>Titre (A-Z)</option>
                        <option value="prix_asc" <?php echo $tri === 'prix_asc' ? 'selected' : ''; ?>>Prix croissant</option>
                        <option value="prix_desc" <?php echo $tri === 'prix_desc' ? 'selected' : ''; ?>>Prix décroissant</option>
                        <option value="disponible" <?php echo $tri === 'disponible' ? 'selected' : ''; ?>>Disponibles d'abord</option>
                    </select>
                </div>
                
                <button type="submit" class="btn-primary">
                    <i class="fas fa-search"></i> Filtrer
                </button>
                
                <a href="oeuvres-artisan.php?id=<?php echo $idArtisan; ?>" class="btn-outline">
                    <i class="fas fa-times"></i> Réinitialiser
                </a>
            </form>
        </div>

        <!-- Gallery -->
        <div class="gallery-container">
            <div class="gallery-header">
                <h2 class="gallery-title">Galerie d'œuvres</h2>
                <div class="results-count">
                    <?php echo $result_oeuvres->num_rows; ?> œuvre<?php echo $result_oeuvres->num_rows > 1 ? 's' : ''; ?> trouvée<?php echo $result_oeuvres->num_rows > 1 ? 's' : ''; ?>
                </div>
            </div>

            <?php if ($result_oeuvres->num_rows > 0): ?>
                <div class="gallery-grid">
                    <?php while ($oeuvre = $result_oeuvres->fetch_assoc()): ?>
                        <div class="artwork-card" onclick="openArtworkModal(<?php echo $oeuvre['idOeuvre']; ?>)">
                            <div class="artwork-image-container">
                                <?php 
                                $image_src = !empty($oeuvre['photo_principale']) ? '../' . $oeuvre['photo_principale'] : 'images/artwork-placeholder.jpg';
                                ?>
                                <img src="<?php echo htmlspecialchars($image_src); ?>" 
                                     alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>" 
                                     class="artwork-image">
                                
                                <div class="artwork-overlay">
                                    <button class="overlay-btn" title="Voir les détails" onclick="event.stopPropagation(); openArtworkModal(<?php echo $oeuvre['idOeuvre']; ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="overlay-btn" title="Ajouter aux favoris" onclick="event.stopPropagation(); toggleFavorite(<?php echo $oeuvre['idOeuvre']; ?>)">
                                        <i class="far fa-heart"></i>
                                    </button>
                                    <?php if ($oeuvre['disponibilite']): ?>
                                        <button class="overlay-btn" title="Contacter pour acheter" onclick="event.stopPropagation(); contactForPurchase(<?php echo $oeuvre['idOeuvre']; ?>)">
                                            <i class="fas fa-envelope"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <?php if ($oeuvre['nb_photos'] > 1): ?>
                                    <div class="photo-count">
                                        <i class="fas fa-images"></i>
                                        <span><?php echo $oeuvre['nb_photos']; ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="artwork-badge <?php echo $oeuvre['disponibilite'] ? '' : 'unavailable'; ?>">
                                    <?php echo $oeuvre['disponibilite'] ? 'Disponible' : 'Vendu'; ?>
                                </div>
                            </div>

                            <div class="artwork-content">
                                <h3 class="artwork-title"><?php echo htmlspecialchars($oeuvre['titre']); ?></h3>
                                
                                <?php if (!empty($oeuvre['description'])): ?>
                                    <p class="artwork-description"><?php echo htmlspecialchars($oeuvre['description']); ?></p>
                                <?php endif; ?>

                                <div class="artwork-footer">
                                    <div class="artwork-price"><?php echo formaterPrix($oeuvre['prix']); ?></div>
                                    <div class="artwork-actions">
                                        <button class="action-btn" title="Voir les détails" onclick="event.stopPropagation(); openArtworkModal(<?php echo $oeuvre['idOeuvre']; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="action-btn" title="Ajouter aux favoris" onclick="event.stopPropagation(); toggleFavorite(<?php echo $oeuvre['idOeuvre']; ?>)">
                                            <i class="far fa-heart"></i>
                                        </button>
                                        <?php if ($oeuvre['disponibilite']): ?>
                                            <button class="action-btn" title="Contacter" onclick="event.stopPropagation(); contactForPurchase(<?php echo $oeuvre['idOeuvre']; ?>)">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-results">
                    <div class="no-results-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <h3>Aucune œuvre trouvée</h3>
                    <p>
                        <?php if (!empty($recherche)): ?>
                            Aucune œuvre ne correspond à votre recherche "<?php echo htmlspecialchars($recherche); ?>".
                        <?php else: ?>
                            Cet artisan n'a pas encore publié d'œuvres.
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($recherche)): ?>
                        <a href="oeuvres-artisan.php?id=<?php echo $idArtisan; ?>" class="btn-primary">
                            <i class="fas fa-palette"></i> Voir toutes les œuvres
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Back to Artist Profile -->
            <div style="text-align: center; margin: 3rem 0;">
                <a href="profil-artisan.php?id=<?php echo $idArtisan; ?>" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Retour au profil de <?php echo htmlspecialchars($artisan['prenom']); ?>
                </a>
            </div>
        </div>
    </main>

    <!-- Modal for Artwork Details -->
    <div id="artworkModal" class="modal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeArtworkModal()">
                <i class="fas fa-times"></i>
            </button>
            <div id="modalContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // JavaScript modifié pour composants fixes - SANS EFFET PARALLAX
        document.addEventListener('DOMContentLoaded', function() {
            // Animation des cartes au scroll - SANS déplacement permanent
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach((entry, index) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => {
                            entry.target.style.opacity = '1';
                            // Supprimer transform pour éviter le déplacement
                            entry.target.classList.add('visible');
                        }, index * 100);
                    }
                });
            }, observerOptions);

            const cards = document.querySelectorAll('.artwork-card');
            cards.forEach(card => {
                card.style.opacity = '0';
                // Ne pas appliquer de transform initial
                card.style.transition = 'opacity 0.6s ease';
                observer.observe(card);
            });

            // Animation des statistiques - SANS modification de position
            animateStats();
        });

        // Animation des statistiques - modifiée pour rester fixe
        function animateStats() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const text = stat.textContent;
                const number = parseInt(text.match(/\d+/));
                
                if (number && !isNaN(number)) {
                    let current = 0;
                    const increment = Math.ceil(number / 30);
                    
                    const timer = setInterval(() => {
                        current += increment;
                        if (current >= number) {
                            stat.textContent = text;
                            clearInterval(timer);
                        } else {
                            stat.textContent = text.replace(/\d+/, current);
                        }
                    }, 50);
                }
            });
        }

        // Fonction pour ouvrir le modal des détails d'œuvre
        function openArtworkModal(idOeuvre) {
            const modal = document.getElementById('artworkModal');
            const modalContent = document.getElementById('modalContent');
            
            // Afficher le modal avec un loader
            modalContent.innerHTML = `
                <div style="padding: 3rem; text-align: center;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: #667eea;"></i>
                    <p style="margin-top: 1rem; color: #718096;">Chargement des détails...</p>
                </div>
            `;
            
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
            
            // Charger le contenu via AJAX (vous devrez créer cette page)
            fetch(`oeuvre-details-ajax.php?id=${idOeuvre}`)
                .then(response => response.text())
                .then(html => {
                    modalContent.innerHTML = html;
                })
                .catch(error => {
                    modalContent.innerHTML = `
                        <div style="padding: 3rem; text-align: center;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #e53e3e;"></i>
                            <p style="margin-top: 1rem; color: #718096;">Erreur lors du chargement</p>
                            <button onclick="closeArtworkModal()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer;">Fermer</button>
                        </div>
                    `;
                });
        }

        // Fonction pour fermer le modal
        function closeArtworkModal() {
            const modal = document.getElementById('artworkModal');
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Fermer le modal en cliquant à l'extérieur
        window.onclick = function(event) {
            const modal = document.getElementById('artworkModal');
            if (event.target === modal) {
                closeArtworkModal();
            }
        };

        // Fonction pour gérer les favoris
        function toggleFavorite(idOeuvre) {
            // Vérifier si l'utilisateur est connecté
            <?php if (!isset($_SESSION['idUtilisateur'])): ?>
                window.location.href = 'connexion.php';
                return;
            <?php endif; ?>

            const btn = event.target.closest('.action-btn, .overlay-btn');
            const icon = btn.querySelector('i');
            
            // Animation de chargement
            btn.disabled = true;
            icon.className = 'fas fa-spinner fa-spin';
            
            // Simulation d'ajout aux favoris (remplacez par votre logique AJAX)
            setTimeout(() => {
                const isActive = btn.classList.contains('active');
                
                if (isActive) {
                    btn.classList.remove('active');
                    icon.className = 'far fa-heart';
                    showNotification('Retiré des favoris', 'info');
                } else {
                    btn.classList.add('active');
                    icon.className = 'fas fa-heart';
                    showNotification('Ajouté aux favoris !', 'success');
                }
                
                btn.disabled = false;
            }, 500);
        }

        // Fonction pour contacter pour achat
        function contactForPurchase(idOeuvre) {
            <?php if (!isset($_SESSION['idUtilisateur'])): ?>
                window.location.href = 'connexion.php';
                return;
            <?php endif; ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                window.location.href = `contact-artisan.php?id=<?php echo $idArtisan; ?>&oeuvre=${idOeuvre}`;
            <?php else: ?>
                showNotification('Seuls les clients peuvent contacter les artisans', 'info');
            <?php endif; ?>
        }

        // Fonction pour afficher les notifications - FIXES
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;

            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }

        // Gestion du clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeArtworkModal();
            }
        });

        // Lazy loading des images - SANS transformation
        const images = document.querySelectorAll('.artwork-image');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '1';
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            img.style.opacity = '0';
            img.style.transition = 'opacity 0.3s ease';
            imageObserver.observe(img);
        });

        // SUPPRESSION COMPLÈTE DE L'EFFET PARALLAX
        // Plus de window.addEventListener('scroll') qui modifie les transforms

        console.log('🎨 Galerie d\'œuvres initialisée (composants fixes)');
        console.log('👨‍🎨 Artisan: <?php echo htmlspecialchars($artisan['prenom'] . " " . $artisan['nom']); ?>');
        console.log('🖼️ Œuvres affichées: <?php echo $result_oeuvres->num_rows; ?>');
        console.log('🔒 Effet parallax désactivé pour stabilité');
    </script>
</body>
</html>