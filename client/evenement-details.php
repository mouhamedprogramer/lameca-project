<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'ID de l'événement est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: evenements.php');
    exit;
}

$idEvenement = intval($_GET['id']);

// Récupérer les détails de l'événement
$sql_event = "SELECT e.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom, 
              u.photo as artisan_photo, u.email as artisan_email, u.telephone as artisan_telephone,
              a.specialite, a.portfolio,
              (SELECT COUNT(*) FROM Clientevenement ce WHERE ce.idEvenement = e.idEvenement) as nb_participants
              FROM Evenement e
              LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
              LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
              WHERE e.idEvenement = ?";

$stmt = $conn->prepare($sql_event);
$stmt->bind_param("i", $idEvenement);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: evenements.php');
    exit;
}

$event = $result->fetch_assoc();

// Vérifier si le client est déjà inscrit
$isParticipating = false;
if (isset($_SESSION['idUtilisateur']) && $_SESSION['role'] === 'Client') {
    $sql_check = "SELECT * FROM Clientevenement WHERE idClient = ? AND idEvenement = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $_SESSION['idUtilisateur'], $idEvenement);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    $isParticipating = $result_check->num_rows > 0;
}

// Récupérer les participants (limité pour la confidentialité)
$sql_participants = "SELECT u.prenom, u.nom, u.photo 
                     FROM Clientevenement ce
                     JOIN Utilisateur u ON ce.idClient = u.idUtilisateur
                     WHERE ce.idEvenement = ?
                     LIMIT 12";

$stmt_participants = $conn->prepare($sql_participants);
$stmt_participants->bind_param("i", $idEvenement);
$stmt_participants->execute();
$result_participants = $stmt_participants->get_result();

// Récupérer les événements similaires
$sql_similar = "SELECT e.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom,
                (SELECT COUNT(*) FROM Clientevenement ce WHERE ce.idEvenement = e.idEvenement) as nb_participants
                FROM Evenement e
                LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
                LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
                WHERE e.idEvenement != ? AND e.dateDebut >= CURDATE()
                AND (e.lieu LIKE ? OR a.specialite LIKE ?)
                ORDER BY e.dateDebut ASC
                LIMIT 4";

$lieu_pattern = '%' . explode(',', $event['lieu'])[0] . '%';
$specialite_pattern = '%' . $event['specialite'] . '%';

$stmt_similar = $conn->prepare($sql_similar);
$stmt_similar->bind_param("iss", $idEvenement, $lieu_pattern, $specialite_pattern);
$stmt_similar->execute();
$result_similar = $stmt_similar->get_result();

function formaterDate($date) {
    $mois = array(
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril', 5 => 'Mai', 6 => 'Juin',
        7 => 'Juillet', 8 => 'Août', 9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
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

function tempsAvantEvenement($dateDebut) {
    $maintenant = time();
    $debut = strtotime($dateDebut);
    $difference = $debut - $maintenant;
    
    if ($difference <= 0) return null;
    
    $jours = floor($difference / 86400);
    $heures = floor(($difference % 86400) / 3600);
    
    if ($jours > 0) {
        return $jours . ' jour' . ($jours > 1 ? 's' : '');
    } elseif ($heures > 0) {
        return $heures . ' heure' . ($heures > 1 ? 's' : '');
    } else {
        return 'Bientôt';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['nomEvenement']); ?> - Événement - Artisano</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr($event['description'], 0, 160)); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/evenement-details.css">
    <script src="js/evenement-details.js"></script>
    <script src="js/evenement-interactions.js"></script>

</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <!-- Hero de l'événement -->
        <section class="event-hero">
            <div class="hero-image">
                <img src="images/events/event-<?php echo $event['idEvenement']; ?>.jpg" 
                     alt="<?php echo htmlspecialchars($event['nomEvenement']); ?>"
                     onerror="this.src='images/event-placeholder.jpg'">
                <div class="hero-overlay"></div>
            </div>
            
            <div class="hero-content">
                <div class="container">
                    <div class="hero-info">
                        <!-- Breadcrumb -->
                        <nav class="breadcrumb">
                            <a href="accueil.php">Accueil</a>
                            <span>/</span>
                            <a href="evenements.php">Événements</a>
                            <span>/</span>
                            <span><?php echo htmlspecialchars($event['nomEvenement']); ?></span>
                        </nav>
                        
                        <div class="event-badges">
                            <?php if ($event['mis_en_avant']): ?>
                                <span class="badge featured-badge">
                                    <i class="fas fa-star"></i> À la une
                                </span>
                            <?php endif; ?>
                            
                            <span class="badge status-badge status-<?php echo getStatutEvenement($event['dateDebut'], $event['dateFin']); ?>">
                                <?php 
                                $statut = getStatutEvenement($event['dateDebut'], $event['dateFin']);
                                echo $statut === 'a-venir' ? 'À venir' : ($statut === 'en-cours' ? 'En cours' : 'Terminé');
                                ?>
                            </span>
                        </div>
                        
                        <h1 class="event-title"><?php echo htmlspecialchars($event['nomEvenement']); ?></h1>
                        
                        <div class="event-meta-hero">
                            <div class="meta-item">
                                <i class="fas fa-calendar-alt"></i>
                                <div>
                                    <strong><?php echo formaterDateComplete($event['dateDebut'], $event['dateFin']); ?></strong>
                                    <?php if ($temps = tempsAvantEvenement($event['dateDebut'])): ?>
                                        <span class="countdown">Dans <?php echo $temps; ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="meta-item">
                                <i class="fas fa-map-marker-alt"></i>
                                <div>
                                    <strong><?php echo htmlspecialchars($event['lieu']); ?></strong>
                                    <span>Lieu de l'événement</span>
                                </div>
                            </div>
                            
                            <div class="meta-item">
                                <i class="fas fa-users"></i>
                                <div>
                                    <strong><?php echo $event['nb_participants']; ?> participant<?php echo $event['nb_participants'] > 1 ? 's' : ''; ?></strong>
                                    <span>Inscrit<?php echo $event['nb_participants'] > 1 ? 's' : ''; ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="hero-actions">
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                                <button class="btn-primary btn-participate <?php echo $isParticipating ? 'participating' : ''; ?>" 
                                        data-event="<?php echo $event['idEvenement']; ?>">
                                    <i class="fas fa-<?php echo $isParticipating ? 'check' : 'plus'; ?>"></i>
                                    <?php echo $isParticipating ? 'Inscrit' : 'Participer'; ?>
                                </button>
                                
                                <button class="btn-outline btn-favorite" data-event="<?php echo $event['idEvenement']; ?>">
                                    <i class="far fa-heart"></i> Ajouter aux favoris
                                </button>
                            <?php else: ?>
                                <a href="connexion.php" class="btn-primary">
                                    <i class="fas fa-user"></i> Se connecter pour participer
                                </a>
                            <?php endif; ?>
                            
                            <button class="btn-share" onclick="partagerEvenement(<?php echo $event['idEvenement']; ?>)">
                                <i class="fas fa-share-alt"></i> Partager
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="event-content">
                <!-- Contenu principal -->
                <div class="main-content-section">
                    <!-- Description -->
                    <section class="description-section">
                        <h2><i class="fas fa-info-circle"></i> À propos de cet événement</h2>
                        <div class="description-content">
                            <p><?php echo nl2br(htmlspecialchars($event['description'])); ?></p>
                        </div>
                    </section>

                    <!-- Informations pratiques -->
                    <section class="practical-info">
                        <h2><i class="fas fa-clipboard-list"></i> Informations pratiques</h2>
                        <div class="info-grid">
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Date et heure</h4>
                                    <p><strong><?php echo formaterDateComplete($event['dateDebut'], $event['dateFin']); ?></strong></p>
                                    <?php if ($event['dateFin'] && $event['dateFin'] !== $event['dateDebut']): ?>
                                        <p class="duration">Événement sur plusieurs jours</p>
                                    <?php else: ?>
                                        <p class="duration">Événement d'une journée</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Lieu</h4>
                                    <p><strong><?php echo htmlspecialchars($event['lieu']); ?></strong></p>
                                    <button class="btn-map" onclick="ouvrirCarte('<?php echo urlencode($event['lieu']); ?>')">
                                        <i class="fas fa-directions"></i> Itinéraire
                                    </button>
                                </div>
                            </div>
                            
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Participants</h4>
                                    <p><strong><?php echo $event['nb_participants']; ?> personne<?php echo $event['nb_participants'] > 1 ? 's' : ''; ?></strong></p>
                                    <p class="participation-status">
                                        <?php echo $isParticipating ? 'Vous participez' : 'Vous ne participez pas encore'; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="info-card">
                                <div class="info-icon">
                                    <i class="fas fa-ticket-alt"></i>
                                </div>
                                <div class="info-content">
                                    <h4>Accès</h4>
                                    <p><strong>Gratuit</strong></p>
                                    <p class="access-info">Inscription obligatoire</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- Participants -->
                    <?php if ($result_participants->num_rows > 0): ?>
                    <section class="participants-section">
                        <h2><i class="fas fa-users"></i> Participants (<?php echo $event['nb_participants']; ?>)</h2>
                        <div class="participants-grid">
                            <?php while ($participant = $result_participants->fetch_assoc()): ?>
                                <div class="participant-card">
                                    <div class="participant-photo">
                                        <?php 
                                        $photo_src = !empty($participant['photo']) ? '../' . $participant['photo'] : 'images/profile-placeholder.jpg';
                                        ?>
                                        <img src="<?php echo $photo_src; ?>" alt="<?php echo htmlspecialchars($participant['prenom']); ?>">
                                    </div>
                                    <div class="participant-name">
                                        <?php echo htmlspecialchars($participant['prenom'] . ' ' . substr($participant['nom'], 0, 1) . '.'); ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                            
                            <?php if ($event['nb_participants'] > 12): ?>
                                <div class="participant-card more-participants">
                                    <div class="more-count">
                                        +<?php echo $event['nb_participants'] - 12; ?>
                                    </div>
                                    <div class="participant-name">autres</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Organisateur -->
                    <?php if ($event['artisan_nom']): ?>
                    <div class="organizer-card">
                        <h3><i class="fas fa-user-tie"></i> Organisé par</h3>
                        <div class="organizer-info">
                            <div class="organizer-photo">
                                <?php 
                                $organizer_photo = !empty($event['artisan_photo']) ? '../images/' . $event['artisan_photo'] : 'images/profile-placeholder.jpg';
                                ?>
                                <img src="<?php echo $organizer_photo; ?>" alt="<?php echo htmlspecialchars($event['artisan_prenom'] . ' ' . $event['artisan_nom']); ?>">
                            </div>
                            <div class="organizer-details">
                                <h4><?php echo htmlspecialchars($event['artisan_prenom'] . ' ' . $event['artisan_nom']); ?></h4>
                                <p class="organizer-specialty"><?php echo htmlspecialchars($event['specialite']); ?></p>
                                <?php if (!empty($event['portfolio'])): ?>
                                    <p class="organizer-bio"><?php echo htmlspecialchars(substr($event['portfolio'], 0, 100)) . '...'; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="organizer-actions">
                            <a href="profil-artisan.php?id=<?php echo $event['idArtisan']; ?>" class="btn-outline">
                                <i class="fas fa-user"></i> Voir le profil
                            </a>
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                                <a href="contact-artisan.php?id=<?php echo $event['idArtisan']; ?>" class="btn-primary">
                                    <i class="far fa-envelope"></i> Contacter
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Actions rapides -->
                    <div class="quick-actions">
                        <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
                        <div class="actions-list">
                            <button class="action-btn" onclick="ajouterAuCalendrier()">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Ajouter au calendrier</span>
                            </button>
                            
                            <button class="action-btn" onclick="partagerEvenement(<?php echo $event['idEvenement']; ?>)">
                                <i class="fas fa-share-alt"></i>
                                <span>Partager l'événement</span>
                            </button>
                            
                            <button class="action-btn" onclick="ouvrirCarte('<?php echo urlencode($event['lieu']); ?>')">
                                <i class="fas fa-map"></i>
                                <span>Voir sur la carte</span>
                            </button>
                            
                            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Client'): ?>
                                <button class="action-btn btn-report" onclick="signalerEvenement(<?php echo $event['idEvenement']; ?>)">
                                    <i class="fas fa-flag"></i>
                                    <span>Signaler un problème</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Événements similaires -->
                    <?php if ($result_similar->num_rows > 0): ?>
                    <div class="similar-events">
                        <h3><i class="fas fa-calendar-alt"></i> Événements similaires</h3>
                        <div class="similar-list">
                            <?php while ($similar = $result_similar->fetch_assoc()): ?>
                                <div class="similar-card" onclick="window.location.href='evenement-details.php?id=<?php echo $similar['idEvenement']; ?>'">
                                    <div class="similar-image">
                                        <img src="images/events/event-<?php echo $similar['idEvenement']; ?>.jpg" 
                                             alt="<?php echo htmlspecialchars($similar['nomEvenement']); ?>"
                                             onerror="this.src='images/event-placeholder.jpg'">
                                        <div class="similar-date">
                                            <?php echo date('d M', strtotime($similar['dateDebut'])); ?>
                                        </div>
                                    </div>
                                    <div class="similar-content">
                                        <h5><?php echo htmlspecialchars($similar['nomEvenement']); ?></h5>
                                        <p class="similar-meta">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($similar['lieu']); ?>
                                        </p>
                                        <p class="similar-participants">
                                            <?php echo $similar['nb_participants']; ?> participant<?php echo $similar['nb_participants'] > 1 ? 's' : ''; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <div class="similar-actions">
                            <a href="evenements.php" class="btn-outline btn-block">
                                <i class="fas fa-calendar-alt"></i> Voir tous les événements
                            </a>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // JavaScript pour les interactions de la page
        document.addEventListener('DOMContentLoaded', function() {
            initEventDetails();
        });

        function initEventDetails() {
            // Gestion du bouton de participation
            const participateBtn = document.querySelector('.btn-participate');
            if (participateBtn) {
                participateBtn.addEventListener('click', function() {
                    const eventId = this.dataset.event;
                    const isParticipating = this.classList.contains('participating');
                    
                    if (isParticipating) {
                        unparticipateEvent(eventId, this);
                    } else {
                        participateEvent(eventId, this);
                    }
                });
            }

            // Gestion du bouton favoris
            const favoriteBtn = document.querySelector('.btn-favorite');
            if (favoriteBtn) {
                favoriteBtn.addEventListener('click', function() {
                    const eventId = this.dataset.event;
                    toggleFavorite(eventId, this);
                });
            }
        }

        function participateEvent(eventId, button) {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription...';
            
            // Simulation de l'inscription
            setTimeout(() => {
                button.classList.add('participating');
                button.innerHTML = '<i class="fas fa-check"></i> Inscrit';
                button.disabled = false;
                
                // Mettre à jour le compteur
                updateParticipantCount(1);
                showNotification('Inscription confirmée !', 'success');
            }, 1500);
        }

        function unparticipateEvent(eventId, button) {
            if (!confirm('Êtes-vous sûr de vouloir vous désinscrire ?')) return;
            
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Désinscription...';
            
            setTimeout(() => {
                button.classList.remove('participating');
                button.innerHTML = '<i class="fas fa-plus"></i> Participer';
                button.disabled = false;
                
                updateParticipantCount(-1);
                showNotification('Désinscription confirmée', 'info');
            }, 1500);
        }

        function toggleFavorite(eventId, button) {
            const icon = button.querySelector('i');
            const isActive = button.classList.contains('active');
            
            if (isActive) {
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Retiré des favoris', 'info');
            } else {
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Ajouté aux favoris !', 'success');
            }
        }

        function updateParticipantCount(change) {
            const countElements = document.querySelectorAll('.meta-item strong, .info-content strong');
            countElements.forEach(element => {
                if (element.textContent.includes('participant')) {
                    const currentCount = parseInt(element.textContent);
                    const newCount = Math.max(0, currentCount + change);
                    element.textContent = newCount + ' participant' + (newCount > 1 ? 's' : '');
                }
            });
        }

        function partagerEvenement(eventId) {
            const url = window.location.href;
            
            if (navigator.share) {
                navigator.share({
                    title: document.title,
                    url: url
                });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    showNotification('Lien copié !', 'success');
                });
            }
        }

        function ouvrirCarte(lieu) {
            const url = `https://www.google.com/maps/search/${encodeURIComponent(lieu)}`;
            window.open(url, '_blank');
        }

        function ajouterAuCalendrier() {
            // Créer un événement ICS
            const title = <?php echo json_encode($event['nomEvenement']); ?>;
            const start = <?php echo json_encode(date('Ymd\THis', strtotime($event['dateDebut']))); ?>;
            const location = <?php echo json_encode($event['lieu']); ?>;
            const description = <?php echo json_encode($event['description']); ?>;
            
            const icsContent = `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Artisano//Event//FR
BEGIN:VEVENT
UID:${Date.now()}@artisano.com
DTSTAMP:${start}
DTSTART:${start}
SUMMARY:${title}
DESCRIPTION:${description}
LOCATION:${location}
END:VEVENT
END:VCALENDAR`;
            
            const blob = new Blob([icsContent], { type: 'text/calendar' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `evenement-${title.replace(/[^a-z0-9]/gi, '-').toLowerCase()}.ics`;
            a.click();
            
            showNotification('Événement ajouté au calendrier !', 'success');
        }

        function signalerEvenement(eventId) {
            const motif = prompt('Veuillez indiquer le motif du signalement:');
            if (motif && motif.trim()) {
                showNotification('Signalement envoyé. Merci pour votre vigilance.', 'info');
            }
        }

        function showNotification(message, type) {
            // Code de notification similaire aux autres pages
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            `;

            const styles = `
                .notification {
                    position: fixed; top: 20px; right: 20px; padding: 15px 20px;
                    border-radius: 10px; color: white; font-weight: 500; z-index: 10000;
                    display: flex; align-items: center; gap: 10px; transform: translateX(400px);
                    transition: transform 0.3s ease; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                }
                .notification.show { transform: translateX(0); }
                .notification-success { background: linear-gradient(135deg, #27ae60, #229954); }
                .notification-error { background: linear-gradient(135deg, #e74c3c, #c0392b); }
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
    });
    </script>
</body>
</html>
