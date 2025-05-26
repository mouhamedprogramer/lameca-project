<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté en tant que client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    header('Location: connexion.php');
    exit;
}

$idClient = $_SESSION['idUtilisateur'];

// Vérifier si la table favoris_evenements existe
$tableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'favoris_evenements'");
if ($result->num_rows > 0) {
    $tableExists = true;
}

$favoris = [];
if ($tableExists) {
    // Récupérer les événements favoris avec leurs détails
    $sql = "SELECT e.*, f.date_ajout, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom,
            (SELECT COUNT(*) FROM Clientevenement ce WHERE ce.idEvenement = e.idEvenement) as nb_participants,
            (SELECT COUNT(*) FROM Clientevenement ce WHERE ce.idEvenement = e.idEvenement AND ce.idClient = ?) as is_participating
            FROM favoris_evenements f
            JOIN Evenement e ON f.idEvenement = e.idEvenement
            LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
            LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
            WHERE f.idClient = ?
            ORDER BY f.date_ajout DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idClient, $idClient);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $favoris[] = $row;
    }
}

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
    <title>Mes événements favoris - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/navbar-enhanced.css">
    <link rel="stylesheet" href="css/favoris-evenements.css">
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
                        <span>Mes événements favoris</span>
                    </div>
                    <h1><i class="fas fa-calendar-heart"></i> Mes événements favoris</h1>
                    <p>Retrouvez tous les événements que vous avez ajoutés à vos favoris</p>
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= count($favoris) ?></span>
                            <span class="stat-label">Événement<?= count($favoris) > 1 ? 's' : '' ?> favori<?= count($favoris) > 1 ? 's' : '' ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <?php if (!$tableExists): ?>
                <!-- Message si la table n'existe pas -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3>Fonctionnalité en cours d'initialisation</h3>
                    <p>Le système de favoris d'événements est en cours de mise en place.</p>
                    <a href="evenements.php" class="btn-primary">
                        <i class="fas fa-calendar-alt"></i> Découvrir les événements
                    </a>
                </div>

            <?php elseif (empty($favoris)): ?>
                <!-- État vide -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Aucun événement favori</h3>
                    <p>Vous n'avez pas encore ajouté d'événements à vos favoris.<br>Explorez notre catalogue et ajoutez vos événements préférés !</p>
                    <a href="evenements.php" class="btn-primary">
                        <i class="fas fa-calendar-alt"></i> Découvrir les événements
                    </a>
                </div>

            <?php else: ?>
                <!-- Filtres et options -->
                <div class="page-controls">
                    <div class="view-options">
                        <button class="view-btn active" data-view="grid">
                            <i class="fas fa-th-large"></i> Grille
                        </button>
                        <button class="view-btn" data-view="list">
                            <i class="fas fa-list"></i> Liste
                        </button>
                    </div>
                    
                    <div class="filter-options">
                        <select class="filter-select" id="statusFilter">
                            <option value="">Tous les statuts</option>
                            <option value="a-venir">À venir</option>
                            <option value="en-cours">En cours</option>
                            <option value="termine">Terminé</option>
                        </select>
                        
                        <select class="filter-select" id="dateFilter">
                            <option value="">Toutes les dates</option>
                            <option value="cette-semaine">Cette semaine</option>
                            <option value="ce-mois">Ce mois</option>
                            <option value="prochains-3-mois">3 prochains mois</option>
                        </select>
                    </div>
                    
                    <div class="bulk-actions">
                        <button class="btn-outline" id="selectAll">
                            <i class="far fa-check-square"></i> Tout sélectionner
                        </button>
                        <button class="btn-danger" id="removeSelected" style="display: none;">
                            <i class="fas fa-trash"></i> Retirer la sélection
                        </button>
                    </div>
                </div>

                <!-- Liste des événements favoris -->
                <div class="favorites-grid" id="favoritesGrid">
                    <?php foreach ($favoris as $event): ?>
                        <div class="favorite-card" 
                             data-status="<?= getStatutEvenement($event['dateDebut'], $event['dateFin']) ?>"
                             data-date="<?= $event['dateDebut'] ?>"
                             data-event-id="<?= $event['idEvenement'] ?>">
                            
                            <div class="card-header">
                                <div class="card-checkbox">
                                    <input type="checkbox" class="event-checkbox" value="<?= $event['idEvenement'] ?>">
                                </div>
                                <div class="card-badges">
                                    <span class="badge status-badge status-<?= getStatutEvenement($event['dateDebut'], $event['dateFin']) ?>">
                                        <?php 
                                        $statut = getStatutEvenement($event['dateDebut'], $event['dateFin']);
                                        echo $statut === 'a-venir' ? 'À venir' : ($statut === 'en-cours' ? 'En cours' : 'Terminé');
                                        ?>
                                    </span>
                                    <?php if ($event['is_participating'] > 0): ?>
                                        <span class="badge participating-badge">
                                            <i class="fas fa-check"></i> Inscrit
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <button class="btn-remove-favorite" data-event="<?= $event['idEvenement'] ?>" title="Retirer des favoris">
                                    <i class="fas fa-heart"></i>
                                </button>
                            </div>
                            
                            <div class="card-image">
                                <img src="../images/event.jpg" 
                                     alt="<?= htmlspecialchars($event['nomEvenement']) ?>"
                                     onerror="this.src='images/event-placeholder.jpg'">
                                <div class="card-date">
                                    <span class="day"><?= date('d', strtotime($event['dateDebut'])) ?></span>
                                    <span class="month"><?= date('M', strtotime($event['dateDebut'])) ?></span>
                                </div>
                            </div>
                            
                            <div class="card-content">
                                <h3 class="event-title">
                                    <a href="evenement-details.php?id=<?= $event['idEvenement'] ?>">
                                        <?= htmlspecialchars($event['nomEvenement']) ?>
                                    </a>
                                </h3>
                                
                                <div class="event-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= formaterDate($event['dateDebut']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?= htmlspecialchars($event['lieu']) ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span><?= $event['nb_participants'] ?> participant<?= $event['nb_participants'] > 1 ? 's' : '' ?></span>
                                    </div>
                                    <?php if ($event['artisan_nom']): ?>
                                    <div class="meta-item">
                                        <i class="fas fa-user-tie"></i>
                                        <span><?= htmlspecialchars($event['artisan_prenom'] . ' ' . $event['artisan_nom']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="event-description">
                                    <?= htmlspecialchars(substr($event['description'], 0, 120)) ?>...
                                </p>
                                
                                <div class="favorite-info">
                                    <span class="favorite-date">
                                        <i class="fas fa-heart"></i>
                                        Ajouté le <?= date('d/m/Y', strtotime($event['date_ajout'])) ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="card-actions">
                                <a href="evenement-details.php?id=<?= $event['idEvenement'] ?>" class="btn-primary">
                                    <i class="fas fa-eye"></i> Voir les détails
                                </a>
                                
                                <?php if (getStatutEvenement($event['dateDebut'], $event['dateFin']) === 'a-venir'): ?>
                                    <?php if ($event['is_participating'] > 0): ?>
                                        <button class="btn-outline btn-participate participating" data-event="<?= $event['idEvenement'] ?>">
                                            <i class="fas fa-check"></i> Inscrit
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-outline btn-participate" data-event="<?= $event['idEvenement'] ?>">
                                            <i class="fas fa-plus"></i> Participer
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <button class="btn-share" onclick="partagerEvenement(<?= $event['idEvenement'] ?>)">
                                    <i class="fas fa-share-alt"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/evenement-interactions.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initFavoritesPage();
        });

        function initFavoritesPage() {
            // Gestion des vues (grille/liste)
            const viewButtons = document.querySelectorAll('.view-btn');
            const favoritesGrid = document.getElementById('favoritesGrid');
            
            viewButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    viewButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const view = this.dataset.view;
                    favoritesGrid.className = view === 'list' ? 'favorites-list' : 'favorites-grid';
                });
            });

            // Gestion des filtres
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            
            function applyFilters() {
                const cards = document.querySelectorAll('.favorite-card');
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;
                
                cards.forEach(card => {
                    let show = true;
                    
                    // Filtre par statut
                    if (statusValue && card.dataset.status !== statusValue) {
                        show = false;
                    }
                    
                    // Filtre par date
                    if (dateValue && !matchDateFilter(card.dataset.date, dateValue)) {
                        show = false;
                    }
                    
                    card.style.display = show ? 'block' : 'none';
                });
            }
            
            statusFilter.addEventListener('change', applyFilters);
            dateFilter.addEventListener('change', applyFilters);

            // Gestion de la sélection multiple
            const selectAllBtn = document.getElementById('selectAll');
            const removeSelectedBtn = document.getElementById('removeSelected');
            const checkboxes = document.querySelectorAll('.event-checkbox');
            
            selectAllBtn.addEventListener('click', function() {
                const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                checkboxes.forEach(cb => cb.checked = !allChecked);
                updateBulkActions();
            });
            
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateBulkActions);
            });
            
            removeSelectedBtn.addEventListener('click', function() {
                const selected = Array.from(checkboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
                
                if (selected.length > 0) {
                    removeMultipleFavorites(selected);
                }
            });

            // Gestion des boutons retirer des favoris
            const removeBtns = document.querySelectorAll('.btn-remove-favorite');
            removeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const eventId = this.dataset.event;
                    removeFavorite(eventId, this.closest('.favorite-card'));
                });
            });
        }
        
        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.event-checkbox');
            const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
            const removeBtn = document.getElementById('removeSelected');
            const selectAllBtn = document.getElementById('selectAll');
            
            if (checkedCount > 0) {
                removeBtn.style.display = 'block';
                removeBtn.textContent = `Retirer (${checkedCount})`;
                selectAllBtn.innerHTML = '<i class="far fa-square"></i> Désélectionner tout';
            } else {
                removeBtn.style.display = 'none';
                selectAllBtn.innerHTML = '<i class="far fa-check-square"></i> Tout sélectionner';
            }
        }
        
        function matchDateFilter(eventDate, filter) {
            const today = new Date();
            const eventDateTime = new Date(eventDate);
            
            switch(filter) {
                case 'cette-semaine':
                    const weekEnd = new Date(today);
                    weekEnd.setDate(today.getDate() + 7);
                    return eventDateTime >= today && eventDateTime <= weekEnd;
                    
                case 'ce-mois':
                    return eventDateTime.getMonth() === today.getMonth() && 
                           eventDateTime.getFullYear() === today.getFullYear();
                           
                case 'prochains-3-mois':
                    const threeMonthsLater = new Date(today);
                    threeMonthsLater.setMonth(today.getMonth() + 3);
                    return eventDateTime >= today && eventDateTime <= threeMonthsLater;
                    
                default:
                    return true;
            }
        }
        
        async function removeFavorite(eventId, cardElement) {
            if (!confirm('Êtes-vous sûr de vouloir retirer cet événement de vos favoris ?')) {
                return;
            }
            
            try {
                const response = await fetch('actions/favoris-evenement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idEvenement: parseInt(eventId),
                        action: 'retirer'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Animation de suppression
                    cardElement.style.transform = 'scale(0.8)';
                    cardElement.style.opacity = '0';
                    
                    setTimeout(() => {
                        cardElement.remove();
                        updateFavoritesCount();
                    }, 300);
                    
                    showNotification('Événement retiré des favoris', 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression', 'error');
            }
        }
        
        async function removeMultipleFavorites(eventIds) {
            if (!confirm(`Êtes-vous sûr de vouloir retirer ${eventIds.length} événement(s) de vos favoris ?`)) {
                return;
            }
            
            const promises = eventIds.map(id => 
                fetch('actions/favoris-evenement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idEvenement: parseInt(id),
                        action: 'retirer'
                    })
                })
            );
            
            try {
                const responses = await Promise.all(promises);
                const results = await Promise.all(responses.map(r => r.json()));
                
                const successCount = results.filter(r => r.success).length;
                
                if (successCount > 0) {
                    // Supprimer les cartes
                    eventIds.forEach(id => {
                        const card = document.querySelector(`[data-event-id="${id}"]`);
                        if (card) card.remove();
                    });
                    
                    updateFavoritesCount();
                    showNotification(`${successCount} événement(s) retiré(s) des favoris`, 'success');
                }
                
                const errorCount = results.length - successCount;
                if (errorCount > 0) {
                    showNotification(`Erreur lors de la suppression de ${errorCount} événement(s)`, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la suppression multiple', 'error');
            }
        }
        
        function updateFavoritesCount() {
            const remainingCards = document.querySelectorAll('.favorite-card').length;
            const statNumber = document.querySelector('.stat-number');
            const statLabel = document.querySelector('.stat-label');
            
            if (statNumber) {
                statNumber.textContent = remainingCards;
                statLabel.textContent = `Événement${remainingCards > 1 ? 's' : ''} favori${remainingCards > 1 ? 's' : ''}`;
            }
            
            // Afficher l'état vide si plus de favoris
            if (remainingCards === 0) {
                location.reload();
            }
        }
        
        function partagerEvenement(eventId) {
            const url = `${window.location.origin}/evenement-details.php?id=${eventId}`;
            
            if (navigator.share) {
                navigator.share({
                    title: 'Événement Artisano',
                    url: url
                });
            } else {
                navigator.clipboard.writeText(url).then(() => {
                    showNotification('Lien copié !', 'success');
                });
            }
        }
        
        function showNotification(message, type) {
            if (window.eventInteractions) {
                window.eventInteractions.showNotification(message, type);
            } else {
                alert(message);
            }
        }
    </script>
</body>
</html>