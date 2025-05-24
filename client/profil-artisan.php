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
$sql_artisan = "SELECT a.*, u.nom, u.prenom, u.email, u.telephone, u.adresse, u.ville, u.pays, u.photo, u.date_creation,
                (SELECT COUNT(*) FROM Oeuvre o WHERE o.idArtisan = a.idArtisan AND o.disponibilite = 1) as nb_oeuvres,
                (SELECT AVG(av.notation) FROM Avisartisan av WHERE av.idArtisan = a.idArtisan) as note_moyenne,
                (SELECT COUNT(*) FROM Avisartisan av WHERE av.idArtisan = a.idArtisan) as nb_avis
                FROM Artisan a 
                JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur 
                WHERE a.idArtisan = ?";

$stmt = $conn->prepare($sql_artisan);
$stmt->bind_param("i", $idArtisan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: artisans.php');
    exit;
}

$artisan = $result->fetch_assoc();

// Récupérer les œuvres de l'artisan (limitées)
$sql_oeuvres = "SELECT o.*, 
                (SELECT p.url FROM Photooeuvre p WHERE p.idOeuvre = o.idOeuvre ORDER BY p.idPhoto ASC LIMIT 1) as photo_principale
                FROM Oeuvre o 
                WHERE o.idArtisan = ? AND o.disponibilite = 1
                ORDER BY o.datePublication DESC 
                LIMIT 6";

$stmt_oeuvres = $conn->prepare($sql_oeuvres);
$stmt_oeuvres->bind_param("i", $idArtisan);
$stmt_oeuvres->execute();
$result_oeuvres = $stmt_oeuvres->get_result();

// Récupérer les avis de l'artisan
$sql_avis = "SELECT av.*, u.nom, u.prenom, u.photo
             FROM Avisartisan av
             JOIN Utilisateur u ON av.idClient = u.idUtilisateur
             WHERE av.idArtisan = ?
             ORDER BY av.dateAvisoeuvre DESC
             LIMIT 5";

$stmt_avis = $conn->prepare($sql_avis);
$stmt_avis->bind_param("i", $idArtisan);
$stmt_avis->execute();
$result_avis = $stmt_avis->get_result();

function formaterPrix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}

function tempsEcoule($date) {
    $timestamp = strtotime($date);
    $difference = time() - $timestamp;
    
    if ($difference < 60) return "À l'instant";
    if ($difference < 3600) return floor($difference / 60) . " min";
    if ($difference < 86400) return floor($difference / 3600) . " h";
    if ($difference < 2592000) return floor($difference / 86400) . " j";
    if ($difference < 31536000) return floor($difference / 2592000) . " mois";
    return floor($difference / 31536000) . " ans";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?> - Artisan - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/profil-artisan.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Header du profil -->
        <div class="profile-header">
            <div class="container">
                <div class="profile-info">
                    <div class="profile-photo">
                        <?php 
                        $photo_src = !empty($artisan['photo']) ? '../' . $artisan['photo'] : 'images/profile-placeholder.jpg';
                        ?>
                        <img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?>">
                        
                        <?php if ($artisan['statut_verification']): ?>
                            <div class="verified-badge">
                                <i class="fas fa-check-circle"></i>
                                <span>Vérifié</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-details">
                        <h1 class="profile-name"><?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?></h1>
                        <p class="profile-specialite"><?php echo htmlspecialchars($artisan['specialite']); ?></p>
                        
                        <div class="profile-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($artisan['ville'] . ', ' . $artisan['pays']); ?></span>
                        </div>
                        
                        <div class="profile-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $artisan['nb_oeuvres']; ?></span>
                                <span class="stat-label">Œuvres</span>
                            </div>
                            
                            <?php if ($artisan['note_moyenne']): ?>
                            <div class="stat-item">
                                <span class="stat-number"><?php echo number_format($artisan['note_moyenne'], 1); ?></span>
                                <span class="stat-label">Note moyenne</span>
                            </div>
                            
                            <div class="stat-item">
                                <span class="stat-number"><?php echo $artisan['nb_avis']; ?></span>
                                <span class="stat-label">Avis</span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="stat-item">
                                <span class="stat-number"><?php echo tempsEcoule($artisan['date_creation']); ?></span>
                                <span class="stat-label">Depuis</span>
                            </div>
                        </div>
                        
                        <?php if ($artisan['note_moyenne']): ?>
                        <div class="profile-rating">
                            <div class="stars">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="<?php echo $i <= round($artisan['note_moyenne']) ? 'fas' : 'far'; ?> fa-star"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text">
                                <?php echo number_format($artisan['note_moyenne'], 1); ?>/5 
                                (<?php echo $artisan['nb_avis']; ?> avis)
                            </span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="profile-actions">
                        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                            <a href="contact-artisan.php?id=<?php echo $idArtisan; ?>" class="btn-primary">
                                <i class="far fa-envelope"></i> Contacter
                            </a>
                            <button class="btn-outline follow-btn" data-artisan="<?php echo $idArtisan; ?>">
                                <i class="fas fa-plus"></i> Suivre
                            </button>
                        <?php else: ?>
                            <a href="connexion.php" class="btn-primary">
                                <i class="fas fa-user"></i> Se connecter pour contacter
                            </a>
                        <?php endif; ?>
                        
                        <button class="btn-share" onclick="partagerProfil()">
                            <i class="fas fa-share-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="profile-content">
                <!-- À propos -->
                <div class="about-section">
                    <h2><i class="fas fa-user"></i> À propos</h2>
                    <div class="about-content">
                        <?php if (!empty($artisan['portfolio'])): ?>
                            <p><?php echo nl2br(htmlspecialchars($artisan['portfolio'])); ?></p>
                        <?php else: ?>
                            <p class="no-info">Cet artisan n'a pas encore ajouté de description.</p>
                        <?php endif; ?>
                        
                        <?php if (!empty($artisan['certification'])): ?>
                            <div class="certifications">
                                <h3><i class="fas fa-certificate"></i> Certifications</h3>
                                <p><?php echo nl2br(htmlspecialchars($artisan['certification'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Œuvres récentes -->
                <div class="works-section">
                    <div class="section-header">
                        <h2><i class="fas fa-palette"></i> Œuvres récentes</h2>
                        <?php if ($artisan['nb_oeuvres'] > 6): ?>
                            <a href="oeuvres-artisan.php?id=<?php echo $idArtisan; ?>" class="view-all">
                                Voir toutes (<?php echo $artisan['nb_oeuvres']; ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($result_oeuvres->num_rows > 0): ?>
                        <div class="works-grid">
                            <?php while ($oeuvre = $result_oeuvres->fetch_assoc()): ?>
                                <div class="work-card" onclick="window.location.href='oeuvre-details.php?id=<?php echo $oeuvre['idOeuvre']; ?>'">
                                    <div class="work-image">
                                        <?php 
                                        $image_src = !empty($oeuvre['photo_principale']) ? $oeuvre['photo_principale'] : 'images/oeuvre-placeholder.jpg';
                                        ?>
                                        <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
                                        <div class="work-overlay">
                                            <div class="work-price"><?php echo formaterPrix($oeuvre['prix']); ?></div>
                                        </div>
                                    </div>
                                    <div class="work-info">
                                        <h4><?php echo htmlspecialchars($oeuvre['titre']); ?></h4>
                                        <p><?php echo htmlspecialchars(substr($oeuvre['description'], 0, 80)) . '...'; ?></p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-works">
                            <i class="fas fa-palette"></i>
                            <p>Cet artisan n'a pas encore d'œuvres disponibles.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Avis clients -->
                <?php if ($result_avis->num_rows > 0): ?>
                <div class="reviews-section">
                    <div class="section-header">
                        <h2><i class="fas fa-star"></i> Avis clients</h2>
                        <?php if ($artisan['nb_avis'] > 5): ?>
                            <a href="avis-artisan.php?id=<?php echo $idArtisan; ?>" class="view-all">
                                Voir tous (<?php echo $artisan['nb_avis']; ?>)
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="reviews-list">
                        <?php while ($avis = $result_avis->fetch_assoc()): ?>
                            <div class="review-card">
                                <div class="review-header">
                                    <div class="reviewer-info">
                                        <div class="reviewer-photo">
                                            <?php 
                                            $reviewer_photo = !empty($avis['photo']) ? 'images/' . $avis['photo'] : 'images/profile-placeholder.jpg';
                                            ?>
                                            <img src="<?php echo $reviewer_photo; ?>" alt="<?php echo htmlspecialchars($avis['prenom'] . ' ' . $avis['nom']); ?>">
                                        </div>
                                        <div>
                                            <h4><?php echo htmlspecialchars($avis['prenom'] . ' ' . substr($avis['nom'], 0, 1) . '.'); ?></h4>
                                            <div class="review-date"><?php echo tempsEcoule($avis['dateAvisoeuvre']); ?></div>
                                        </div>
                                    </div>
                                    
                                    <div class="review-rating">
                                        <?php for($i = 1; $i <= 5; $i++): ?>
                                            <i class="<?php echo $i <= $avis['notation'] ? 'fas' : 'far'; ?> fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <?php if (!empty($avis['message'])): ?>
                                    <div class="review-message">
                                        <p><?php echo htmlspecialchars($avis['message']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Gestion du bouton suivre
        document.addEventListener('DOMContentLoaded', function() {
            const followBtn = document.querySelector('.follow-btn');
            
            if (followBtn) {
                followBtn.addEventListener('click', function() {
                    const artisanId = this.dataset.artisan;
                    const isFollowing = this.classList.contains('following');
                    
                    // Simuler le suivi/désuivi
                    if (isFollowing) {
                        this.classList.remove('following');
                        this.innerHTML = '<i class="fas fa-plus"></i> Suivre';
                        showNotification('Vous ne suivez plus cet artisan', 'info');
                    } else {
                        this.classList.add('following');
                        this.innerHTML = '<i class="fas fa-check"></i> Suivi';
                        showNotification('Vous suivez maintenant cet artisan !', 'success');
                    }
                });
            }
        });

        // Fonction de partage
        function partagerProfil() {
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: window.location.href
                });
            } else {
                // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
                navigator.clipboard.writeText(window.location.href).then(() => {
                    showNotification('Lien copié dans le presse-papiers !', 'success');
                });
            }
        }

        // Fonction de notification
        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;

            const styles = `
                .notification {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    padding: 15px 20px;
                    border-radius: 10px;
                    color: white;
                    font-weight: 500;
                    z-index: 10000;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    transform: translateX(400px);
                    transition: transform 0.3s ease;
                    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                }
                .notification.show { transform: translateX(0); }
                .notification-success { background: linear-gradient(135deg, #27ae60, #229954); }
                .notification-info { background: linear-gradient(135deg, #3498db, #2980b9); }
            `;

            if (!document.getElementById('notification-styles')) {
                const styleElement = document.createElement('style');
                styleElement.id = 'notification-styles';
                styleElement.textContent = styles;
                document.head.appendChild(styleElement);
            }

            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }
    </script>
</body>
</html>