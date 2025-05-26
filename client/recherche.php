<?php
session_start();
require_once 'includes/conn.php';
include 'includes/header.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$searchTerm = '%' . $query . '%';

$results = [
    'oeuvres' => [],
    'artisans' => [],
    'evenements' => []
];

$totalResults = 0;

if (!empty($query)) {
    try {
        // Recherche dans les œuvres (avec les bons noms de colonnes)
        $sql = "SELECT o.idOeuvre as id, o.titre as nom, o.description, o.prix,
                       CONCAT(u.prenom, ' ', u.nom) as artisan, o.caracteristiques
                FROM Oeuvre o 
                LEFT JOIN Utilisateur u ON o.idArtisan = u.idUtilisateur 
                WHERE (o.titre LIKE ? 
                   OR o.description LIKE ? 
                   OR o.caracteristiques LIKE ?)
                   AND o.disponibilite = 1
                ORDER BY o.titre";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $results['oeuvres'][] = $row;
            }
            mysqli_stmt_close($stmt);
        }

        // Recherche dans les artisans (via la table Artisan et Utilisateur)
        $sql = "SELECT u.idUtilisateur as id, 
                       CONCAT(u.prenom, ' ', u.nom) as nom,
                       a.specialite, u.photo, u.ville
                FROM Utilisateur u 
                INNER JOIN Artisan a ON u.idUtilisateur = a.idArtisan
                WHERE (u.nom LIKE ? 
                       OR u.prenom LIKE ? 
                       OR a.specialite LIKE ?
                       OR CONCAT(u.prenom, ' ', u.nom) LIKE ?)
                ORDER BY u.nom";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $results['artisans'][] = $row;
            }
            mysqli_stmt_close($stmt);
        }

        // Recherche dans les événements (avec les bons noms de colonnes)
        $sql = "SELECT e.idEvenement as id, e.nomEvenement as nom, e.description, e.lieu,
                       DATE_FORMAT(e.dateDebut, '%d/%m/%Y') as date
                FROM Evenement e 
                WHERE e.nomEvenement LIKE ? 
                   OR e.description LIKE ?
                   OR e.lieu LIKE ?
                ORDER BY e.dateDebut";
        
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $searchTerm, $searchTerm, $searchTerm);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            
            while ($row = mysqli_fetch_assoc($result)) {
                $results['evenements'][] = $row;
            }
            mysqli_stmt_close($stmt);
        }

        $totalResults = count($results['oeuvres']) + count($results['artisans']) + count($results['evenements']);

    } catch (Exception $e) {
        $error = "Erreur lors de la recherche: " . $e->getMessage();
        error_log($error);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - <?= htmlspecialchars($query) ?></title>
    <style>
        .search-results {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .search-header {
            text-align: center;
            margin-bottom: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            border-radius: 15px;
        }

        .search-header h1 {
            margin: 0 0 10px 0;
            font-size: 2.5em;
        }

        .search-stats {
            opacity: 0.9;
            font-size: 1.1em;
        }

        .results-section {
            margin-bottom: 50px;
        }

        .section-title {
            font-size: 1.8em;
            color: #667eea;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #667eea;
            display: flex;
            align-items: center;
        }

        .section-title i {
            margin-right: 15px;
        }

        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
        }

        .result-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
        }

        .result-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .card-image {
            height: 200px;
            background: linear-gradient(45deg, #f0f2f5, #e1e5e9);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-image i {
            font-size: 3em;
            color: #999;
        }

        .card-content {
            padding: 20px;
        }

        .card-title {
            font-size: 1.3em;
            font-weight: 600;
            margin-bottom: 10px;
            color: #333;
        }

        .card-subtitle {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .card-description {
            color: #666;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .card-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9em;
            color: #999;
        }

        .price {
            font-size: 1.2em;
            font-weight: 600;
            color: #27ae60;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .no-results i {
            font-size: 4em;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
            
            .search-header h1 {
                font-size: 2em;
            }
        }
    </style>
</head>
<body>

<div class="search-results">
    <div class="search-header">
        <h1>Résultats de recherche</h1>
        <?php if (!empty($query)): ?>
            <div class="search-stats">
                <?= $totalResults ?> résultat(s) trouvé(s) pour "<strong><?= htmlspecialchars($query) ?></strong>"
            </div>
        <?php endif; ?>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($query)): ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h2>Aucune recherche effectuée</h2>
            <p>Utilisez la barre de recherche pour trouver des œuvres, artisans ou événements.</p>
        </div>
    <?php elseif ($totalResults == 0): ?>
        <div class="no-results">
            <i class="fas fa-search"></i>
            <h2>Aucun résultat trouvé</h2>
            <p>Essayez avec d'autres mots-clés ou vérifiez l'orthographe.</p>
        </div>
    <?php else: ?>

        <!-- Œuvres -->
        <?php if (!empty($results['oeuvres'])): ?>
        <div class="results-section">
            <h2 class="section-title">
                <i class="fas fa-palette"></i>
                Œuvres (<?= count($results['oeuvres']) ?>)
            </h2>
            <div class="results-grid">
                <?php foreach ($results['oeuvres'] as $oeuvre): ?>
                    <a href="oeuvre.php?id=<?= $oeuvre['id'] ?>" class="result-card">
                        <div class="card-image">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-title"><?= htmlspecialchars($oeuvre['nom']) ?></div>
                            <div class="card-subtitle">Par <?= htmlspecialchars($oeuvre['artisan'] ?? 'Artisan inconnu') ?></div>
                            <?php if (!empty($oeuvre['description'])): ?>
                                <div class="card-description"><?= htmlspecialchars(substr($oeuvre['description'], 0, 100)) ?>...</div>
                            <?php endif; ?>
                            <div class="card-meta">
                                <span>Œuvre d'art</span>
                                <span class="price"><?= number_format($oeuvre['prix'], 2) ?>€</span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Artisans -->
        <?php if (!empty($results['artisans'])): ?>
        <div class="results-section">
            <h2 class="section-title">
                <i class="fas fa-users"></i>
                Artisans (<?= count($results['artisans']) ?>)
            </h2>
            <div class="results-grid">
                <?php foreach ($results['artisans'] as $artisan): ?>
                    <a href="artisan.php?id=<?= $artisan['id'] ?>" class="result-card">
                        <div class="card-image">
                            <?php if (!empty($artisan['photo'])): ?>
                                <img src="images/<?= htmlspecialchars($artisan['photo']) ?>" alt="<?= htmlspecialchars($artisan['nom']) ?>">
                            <?php else: ?>
                                <i class="fas fa-user"></i>
                            <?php endif; ?>
                        </div>
                        <div class="card-content">
                            <div class="card-title"><?= htmlspecialchars($artisan['nom']) ?></div>
                            <div class="card-subtitle"><?= htmlspecialchars($artisan['specialite'] ?? 'Spécialité non définie') ?></div>
                            <div class="card-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($artisan['ville'] ?? 'Localisation non définie') ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Événements -->
        <?php if (!empty($results['evenements'])): ?>
        <div class="results-section">
            <h2 class="section-title">
                <i class="fas fa-calendar-alt"></i>
                Événements (<?= count($results['evenements']) ?>)
            </h2>
            <div class="results-grid">
                <?php foreach ($results['evenements'] as $evenement): ?>
                    <a href="evenement.php?id=<?= $evenement['id'] ?>" class="result-card">
                        <div class="card-image">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="card-content">
                            <div class="card-title"><?= htmlspecialchars($evenement['nom']) ?></div>
                            <div class="card-subtitle"><i class="fas fa-calendar"></i> <?= $evenement['date'] ?></div>
                            <?php if (!empty($evenement['description'])): ?>
                                <div class="card-description"><?= htmlspecialchars(substr($evenement['description'], 0, 100)) ?>...</div>
                            <?php endif; ?>
                            <div class="card-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($evenement['lieu'] ?? 'Lieu non défini') ?></span>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

</body>
</html>