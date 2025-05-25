<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté en tant que client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    header('Location: connexion.php');
    exit;
}

$idClient = $_SESSION['idUtilisateur'];

// D'abord, vérifier si la table favoris_evenements existe
$favorisTableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'favoris_evenements'");
if ($result->num_rows > 0) {
    $favorisTableExists = true;
}

// Construire la requête en fonction de l'existence de la table favoris
if ($favorisTableExists) {
    $sql = "SELECT e.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom,
            (SELECT COUNT(*) FROM Clientevenement ce2 WHERE ce2.idEvenement = e.idEvenement) as nb_participants,
            (SELECT COUNT(*) FROM favoris_evenements f WHERE f.idEvenement = e.idEvenement AND f.idClient = ?) as is_favorite
            FROM Clientevenement ce
            JOIN Evenement e ON ce.idEvenement = e.idEvenement
            LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
            LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
            WHERE ce.idClient = ?
            ORDER BY e.dateDebut ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idClient, $idClient);
} else {
    $sql = "SELECT e.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom,
            (SELECT COUNT(*) FROM Clientevenement ce2 WHERE ce2.idEvenement = e.idEvenement) as nb_participants,
            0 as is_favorite
            FROM Clientevenement ce
            JOIN Evenement e ON ce.idEvenement = e.idEvenement
            LEFT JOIN Artisan a ON e.idArtisan = a.idArtisan
            LEFT JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
            WHERE ce.idClient = ?
            ORDER BY e.dateDebut ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idClient);
}

$stmt->execute();
$result = $stmt->get_result();

$participations = [];
$stats = [
    'total' => 0,
    'a_venir' => 0,
    'en_cours' => 0,
    'termines' => 0
];

while ($row = $result->fetch_assoc()) {
    $participations[] = $row;
    $stats['total']++;
    
    $statut = getStatutEvenement($row['dateDebut'], $row['dateFin']);
    $stats[$statut === 'a-venir' ? 'a_venir' : ($statut === 'en-cours' ? 'en_cours' : 'termines')]++;
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

// Fonction pour obtenir une date d'inscription simulée
function getDateInscriptionSimulee($dateEvenement) {
    // Simuler une date d'inscription entre 1 et 30 jours avant l'événement
    $dateEvent = strtotime($dateEvenement);
    $joursAvant = rand(1, 30);
    $dateInscription = $dateEvent - ($joursAvant * 24 * 60 * 60);
    return date('Y-m-d', $dateInscription);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes événements - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/navbar-enhanced.css">
    <link rel="stylesheet" href="css/mes-evenements.css">
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
                        <span>Mes événements</span>
                    </div>
                    <h1><i class="fas fa-calendar-check"></i> Mes événements</h1>
                    <p>Gérez vos participations aux événements artistiques</p>
                    
                    <div class="stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['total'] ?></span>
                            <span class="stat-label">Total</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['a_venir'] ?></span>
                            <span class="stat-label">À venir</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['en_cours'] ?></span>
                            <span class="stat-label">En cours</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $stats['termines'] ?></span>
                            <span class="stat-label">Terminés</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="container">
            <?php if (empty($participations)): ?>
                <!-- État vide -->
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                    <h3>Aucune participation</h3>
                    <p>Vous ne participez à aucun événement pour le moment.<br>Découvrez notre sélection d'événements artistiques et inscrivez-vous !</p>
                    <a href="evenements.php" class="btn-primary">
                        <i class="fas fa-calendar-alt"></i> Découvrir les événements
                    </a>
                </div>

            <?php else: ?>
                <!-- Filtres et options -->
                <div class="page-controls">
                    <div class="view-options">
                        <button class="view-btn active" data-view="timeline">
                            <i class="fas fa-stream"></i> Timeline
                        </button>
                        <button class="view-btn" data-view="grid">
                            <i class="fas fa-th-large"></i> Grille
                        </button>
                        <button class="view-btn" data-view="calendar">
                            <i class="fas fa-calendar"></i> Calendrier
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
                            <option value="">Toutes les périodes</option>
                            <option value="cette-semaine">Cette semaine</option>
                            <option value="ce-mois">Ce mois</option>
                            <option value="prochains-3-mois">3 prochains mois</option>
                            <option value="cette-annee">Cette année</option>
                        </select>
                    </div>
                    
                    <div class="action-buttons">
                        <button class="btn-outline" onclick="exporterCalendrier()">
                            <i class="fas fa-download"></i> Exporter
                        </button>
                        <a href="evenements.php" class="btn-primary">
                            <i class="fas fa-plus"></i> Nouvel événement
                        </a>
                    </div>
                </div>

                <!-- Timeline View (par défaut) -->
                <div class="events-timeline" id="eventsTimeline">
                    <?php
                    $currentMonth = '';
                    foreach ($participations as $event):
                        $eventMonth = date('Y-m', strtotime($event['dateDebut']));
                        if ($eventMonth !== $currentMonth):
                            $currentMonth = $eventMonth;
                            $monthYear = date('F Y', strtotime($event['dateDebut']));
                    ?>
                        <div class="timeline-month">
                            <h3><?= $monthYear ?></h3>
                        </div>
                    <?php endif; ?>
                        
                        <div class="timeline-item" 
                             data-status="<?= getStatutEvenement($event['dateDebut'], $event['dateFin']) ?>"
                             data-date="<?= $event['dateDebut'] ?>"
                             data-event-id="<?= $event['idEvenement'] ?>">
                            
                            <div class="timeline-date">
                                <span class="day"><?= date('d', strtotime($event['dateDebut'])) ?></span>
                                <span class="month"><?= date('M', strtotime($event['dateDebut'])) ?></span>
                                <span class="year"><?= date('Y', strtotime($event['dateDebut'])) ?></span>
                            </div>
                            
                            <div class="timeline-content">
                                <div class="event-card">
                                    <div class="card-header">
                                        <div class="event-badges">
                                            <span class="badge status-badge status-<?= getStatutEvenement($event['dateDebut'], $event['dateFin']) ?>">
                                                <?php 
                                                $statut = getStatutEvenement($event['dateDebut'], $event['dateFin']);
                                                echo $statut === 'a-venir' ? 'À venir' : ($statut === 'en-cours' ? 'En cours' : 'Terminé');
                                                ?>
                                            </span>
                                            
                                            <?php if ($event['is_favorite'] > 0): ?>
                                                <span class="badge favorite-badge">
                                                    <i class="fas fa-heart"></i> Favori
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($temps = tempsAvantEvenement($event['dateDebut'])): ?>
                                                <span class="badge countdown-badge">
                                                    <i class="fas fa-clock"></i> Dans <?= $temps ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="card-actions-header">
                                            <?php if (getStatutEvenement($event['dateDebut'], $event['dateFin']) === 'a-venir'): ?>
                                                <button class="btn-unsubscribe" data-event="<?= $event['idEvenement'] ?>" title="Se désinscrire">
                                                    <i class="fas fa-user-minus"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="card-body">
                                        <div class="event-image">
                                            <img src="images/events/event-<?= $event['idEvenement'] ?>.jpg" 
                                                 alt="<?= htmlspecialchars($event['nomEvenement']) ?>"
                                                 onerror="this.src='images/event-placeholder.jpg'">
                                        </div>
                                        
                                        <div class="event-details">
                                            <h3 class="event-title">
                                                <a href="evenement-details.php?id=<?= $event['idEvenement'] ?>">
                                                    <?= htmlspecialchars($event['nomEvenement']) ?>
                                                </a>
                                            </h3>
                                            
                                            <div class="event-meta">
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
                                                <?= htmlspecialchars(substr($event['description'], 0, 150)) ?>...
                                            </p>
                                            
                                            <div class="participation-info">
                                                <i class="fas fa-calendar-check"></i>
                                                <span>Inscrit le <?= date('d/m/Y', strtotime(getDateInscriptionSimulee($event['dateDebut']))) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card-footer">
                                        <div class="card-actions">
                                            <a href="evenement-details.php?id=<?= $event['idEvenement'] ?>" class="btn-primary">
                                                <i class="fas fa-eye"></i> Voir les détails
                                            </a>
                                            
                                            <button class="btn-outline" onclick="ajouterAuCalendrier(<?= $event['idEvenement'] ?>)">
                                                <i class="fas fa-calendar-plus"></i> Calendrier
                                            </button>
                                            
                                            <button class="btn-outline" onclick="partagerEvenement(<?= $event['idEvenement'] ?>)">
                                                <i class="fas fa-share-alt"></i> Partager
                                            </button>
                                            
                                            <?php if ($favorisTableExists): ?>
                                                <?php if ($event['is_favorite'] == 0): ?>
                                                    <button class="btn-favorite" data-event="<?= $event['idEvenement'] ?>">
                                                        <i class="far fa-heart"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn-favorite active" data-event="<?= $event['idEvenement'] ?>">
                                                        <i class="fas fa-heart"></i>
                                                    </button>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Grid View (masquée par défaut) -->
                <div class="events-grid" id="eventsGrid" style="display: none;">
                    <!-- Le contenu sera généré en JavaScript -->
                </div>

                <!-- Calendar View (masquée par défaut) -->
                <div class="events-calendar" id="eventsCalendar" style="display: none;">
                    <div class="calendar-header">
                        <button class="btn-nav" id="prevMonth"><i class="fas fa-chevron-left"></i></button>
                        <h3 id="currentMonth"></h3>
                        <button class="btn-nav" id="nextMonth"><i class="fas fa-chevron-right"></i></button>
                    </div>
                    <div class="calendar-grid" id="calendarGrid">
                        <!-- Le calendrier sera généré en JavaScript -->
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/evenement-interactions.js"></script>
    <script>
        const eventsData = <?= json_encode($participations) ?>;
        const favorisTableExists = <?= $favorisTableExists ? 'true' : 'false' ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            initMyEventsPage();
        });

        function initMyEventsPage() {
            // Gestion des vues
            const viewButtons = document.querySelectorAll('.view-btn');
            const timeline = document.getElementById('eventsTimeline');
            const grid = document.getElementById('eventsGrid');
            const calendar = document.getElementById('eventsCalendar');
            
            viewButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    viewButtons.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const view = this.dataset.view;
                    
                    // Masquer toutes les vues
                    timeline.style.display = 'none';
                    grid.style.display = 'none';
                    calendar.style.display = 'none';
                    
                    // Afficher la vue sélectionnée
                    switch(view) {
                        case 'timeline':
                            timeline.style.display = 'block';
                            break;
                        case 'grid':
                            grid.style.display = 'grid';
                            generateGridView();
                            break;
                        case 'calendar':
                            calendar.style.display = 'block';
                            generateCalendarView();
                            break;
                    }
                });
            });

            // Gestion des filtres
            const statusFilter = document.getElementById('statusFilter');
            const dateFilter = document.getElementById('dateFilter');
            
            function applyFilters() {
                const items = document.querySelectorAll('.timeline-item');
                const statusValue = statusFilter.value;
                const dateValue = dateFilter.value;
                
                items.forEach(item => {
                    let show = true;
                    
                    if (statusValue && item.dataset.status !== statusValue) {
                        show = false;
                    }
                    
                    if (dateValue && !matchDateFilter(item.dataset.date, dateValue)) {
                        show = false;
                    }
                    
                    item.style.display = show ? 'flex' : 'none';
                });
            }
            
            statusFilter.addEventListener('change', applyFilters);
            dateFilter.addEventListener('change', applyFilters);

            // Gestion des désinscriptions
            const unsubscribeBtns = document.querySelectorAll('.btn-unsubscribe');
            unsubscribeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const eventId = this.dataset.event;
                    unsubscribeFromEvent(eventId, this.closest('.timeline-item'));
                });
            });

            // Gestion des favoris (seulement si la table existe)
            if (favorisTableExists) {
                const favoriteBtns = document.querySelectorAll('.btn-favorite');
                favoriteBtns.forEach(btn => {
                    btn.addEventListener('click', function() {
                        const eventId = this.dataset.event;
                        const isActive = this.classList.contains('active');
                        toggleFavorite(eventId, this, !isActive);
                    });
                });
            }
        }
        
        async function toggleFavorite(eventId, button, addToFavorites) {
            const action = addToFavorites ? 'ajouter' : 'retirer';
            
            try {
                const response = await fetch('actions/favoris-evenement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idEvenement: parseInt(eventId),
                        action: action
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    const icon = button.querySelector('i');
                    
                    if (addToFavorites) {
                        button.classList.add('active');
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                    } else {
                        button.classList.remove('active');
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                    }
                    
                    showNotification(data.message, 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la gestion des favoris', 'error');
            }
        }
        
        function generateGridView() {
            const grid = document.getElementById('eventsGrid');
            grid.innerHTML = '';
            
            eventsData.forEach(event => {
                const card = createEventGridCard(event);
                grid.appendChild(card);
            });
        }
        
        function createEventGridCard(event) {
            const div = document.createElement('div');
            div.className = 'event-grid-card';
            div.dataset.status = getEventStatus(event.dateDebut, event.dateFin);
            div.dataset.date = event.dateDebut;
            div.dataset.eventId = event.idEvenement;
            
            div.innerHTML = `
                <div class="grid-card-image">
                    <img src="images/events/event-${event.idEvenement}.jpg" 
                         alt="${event.nomEvenement}"
                         onerror="this.src='images/event-placeholder.jpg'">
                    <div class="grid-card-date">
                        <span class="day">${new Date(event.dateDebut).getDate()}</span>
                        <span class="month">${new Date(event.dateDebut).toLocaleDateString('fr-FR', {month: 'short'})}</span>
                    </div>
                </div>
                <div class="grid-card-content">
                    <h4><a href="evenement-details.php?id=${event.idEvenement}">${event.nomEvenement}</a></h4>
                    <p class="grid-card-location"><i class="fas fa-map-marker-alt"></i> ${event.lieu}</p>
                    <p class="grid-card-participants"><i class="fas fa-users"></i> ${event.nb_participants} participant${event.nb_participants > 1 ? 's' : ''}</p>
                </div>
                <div class="grid-card-actions">
                    <a href="evenement-details.php?id=${event.idEvenement}" class="btn-primary btn-sm">Voir</a>
                    ${favorisTableExists ? `<button class="btn-favorite btn-sm ${event.is_favorite > 0 ? 'active' : ''}" data-event="${event.idEvenement}">
                        <i class="fa${event.is_favorite > 0 ? 's' : 'r'} fa-heart"></i>
                    </button>` : ''}
                </div>
            `;
            
            return div;
        }
        
        function generateCalendarView() {
            initCalendar();
        }
        
        function initCalendar() {
            const currentDate = new Date();
            let currentMonth = currentDate.getMonth();
            let currentYear = currentDate.getFullYear();
            
            const monthNames = [
                'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
                'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
            ];
            
            function updateCalendar() {
                const currentMonthElement = document.getElementById('currentMonth');
                currentMonthElement.textContent = `${monthNames[currentMonth]} ${currentYear}`;
                
                const calendarGrid = document.getElementById('calendarGrid');
                calendarGrid.innerHTML = '';
                
                const firstDay = new Date(currentYear, currentMonth, 1).getDay();
                const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
                
                const dayNames = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                dayNames.forEach(day => {
                    const dayHeader = document.createElement('div');
                    dayHeader.className = 'calendar-day-header';
                    dayHeader.textContent = day;
                    calendarGrid.appendChild(dayHeader);
                });
                
                for (let i = 0; i < firstDay; i++) {
                    const emptyCell = document.createElement('div');
                    emptyCell.className = 'calendar-day empty';
                    calendarGrid.appendChild(emptyCell);
                }
                
                for (let day = 1; day <= daysInMonth; day++) {
                    const dayCell = document.createElement('div');
                    dayCell.className = 'calendar-day';
                    
                    const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                    const eventsForDay = eventsData.filter(event => 
                        event.dateDebut.startsWith(dateStr)
                    );
                    
                    dayCell.innerHTML = `
                        <span class="day-number">${day}</span>
                        ${eventsForDay.length > 0 ? `<div class="day-events">${eventsForDay.length} événement(s)</div>` : ''}
                    `;
                    
                    if (eventsForDay.length > 0) {
                        dayCell.classList.add('has-events');
                        dayCell.addEventListener('click', () => {
                            showDayEvents(dateStr, eventsForDay);
                        });
                    }
                    
                    calendarGrid.appendChild(dayCell);
                }
            }
            
            document.getElementById('prevMonth').addEventListener('click', () => {
                currentMonth--;
                if (currentMonth < 0) {
                    currentMonth = 11;
                    currentYear--;
                }
                updateCalendar();
            });
            
            document.getElementById('nextMonth').addEventListener('click', () => {
                currentMonth++;
                if (currentMonth > 11) {
                    currentMonth = 0;
                    currentYear++;
                }
                updateCalendar();
            });
            
            updateCalendar();
        }
        
        function getEventStatus(dateDebut, dateFin) {
            const now = new Date();
            const start = new Date(dateDebut);
            const end = dateFin ? new Date(dateFin) : start;
            
            if (now < start) return 'a-venir';
            if (now >= start && now <= end) return 'en-cours';
            return 'termine';
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
                    
                case 'cette-annee':
                    return eventDateTime.getFullYear() === today.getFullYear();
                    
                default:
                    return true;
            }
        }
        
        async function unsubscribeFromEvent(eventId, itemElement) {
            if (!confirm('Êtes-vous sûr de vouloir vous désinscrire de cet événement ?')) {
                return;
            }
            
            try {
                const response = await fetch('actions/participer-evenement.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        idEvenement: parseInt(eventId),
                        action: 'desinscrire'
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    itemElement.style.opacity = '0.5';
                    itemElement.style.transform = 'translateX(-20px)';
                    
                    setTimeout(() => {
                        itemElement.remove();
                        updateStats();
                    }, 300);
                    
                    showNotification('Désinscription confirmée', 'success');
                } else {
                    showNotification(data.message, 'error');
                }
            } catch (error) {
                showNotification('Erreur lors de la désinscription', 'error');
            }
        }
        
        function updateStats() {
            const items = document.querySelectorAll('.timeline-item:not([style*="display: none"])');
            const stats = { total: 0, a_venir: 0, en_cours: 0, termines: 0 };
            
            items.forEach(item => {
                stats.total++;
                const status = item.dataset.status;
                if (status === 'a-venir') stats.a_venir++;
                else if (status === 'en-cours') stats.en_cours++;
                else stats.termines++;
            });
            
            const statNumbers = document.querySelectorAll('.stat-number');
            statNumbers[0].textContent = stats.total;
            statNumbers[1].textContent = stats.a_venir;
            statNumbers[2].textContent = stats.en_cours;
            statNumbers[3].textContent = stats.termines;
        }
        
        function exporterCalendrier() {
            const events = eventsData.map(event => {
                const start = new Date(event.dateDebut).toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
                const end = event.dateFin ? new Date(event.dateFin).toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z' : start;
                
                return `BEGIN:VEVENT
UID:${event.idEvenement}@artisano.com
DTSTAMP:${start}
DTSTART:${start}
DTEND:${end}
SUMMARY:${event.nomEvenement}
DESCRIPTION:${event.description ? event.description.substring(0, 200) : 'Événement Artisano'}
LOCATION:${event.lieu}
END:VEVENT`;
            }).join('\n');
            
            const icsContent = `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Artisano//Mes Événements//FR
${events}
END:VCALENDAR`;
            
            const blob = new Blob([icsContent], { type: 'text/calendar' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'mes-evenements-artisano.ics';
            a.click();
            URL.revokeObjectURL(url);
            
            showNotification('Calendrier exporté !', 'success');
        }
        
        function ajouterAuCalendrier(eventId) {
            const event = eventsData.find(e => e.idEvenement == eventId);
            if (!event) return;
            
            const start = new Date(event.dateDebut).toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
            const end = event.dateFin ? new Date(event.dateFin).toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z' : start;
            
            const icsContent = `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Artisano//Event//FR
BEGIN:VEVENT
UID:${event.idEvenement}@artisano.com
DTSTAMP:${start}
DTSTART:${start}
DTEND:${end}
SUMMARY:${event.nomEvenement}
DESCRIPTION:${event.description || 'Événement Artisano'}
LOCATION:${event.lieu}
END:VEVENT
END:VCALENDAR`;
            
            const blob = new Blob([icsContent], { type: 'text/calendar' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `evenement-${event.nomEvenement.replace(/[^a-z0-9]/gi, '-').toLowerCase()}.ics`;
            a.click();
            URL.revokeObjectURL(url);
            
            showNotification('Événement ajouté au calendrier !', 'success');
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
        
        function showDayEvents(date, events) {
            const modal = document.createElement('div');
            modal.className = 'day-events-modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Événements du ${new Date(date).toLocaleDateString('fr-FR')}</h3>
                        <button class="btn-close">&times;</button>
                    </div>
                    <div class="modal-body">
                        ${events.map(event => `
                            <div class="event-summary">
                                <h4><a href="evenement-details.php?id=${event.idEvenement}">${event.nomEvenement}</a></h4>
                                <p><i class="fas fa-map-marker-alt"></i> ${event.lieu}</p>
                                <p><i class="fas fa-users"></i> ${event.nb_participants} participants</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            modal.querySelector('.btn-close').addEventListener('click', () => {
                modal.remove();
            });
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) modal.remove();
            });
        }
        
        function showNotification(message, type) {
            if (window.eventInteractions) {
                window.eventInteractions.showNotification(message, type);
            } else {
                // Fallback simple
                const notification = document.createElement('div');
                notification.style.cssText = `
                    position: fixed; top: 20px; right: 20px; padding: 15px 20px;
                    background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
                    color: white; border-radius: 8px; z-index: 10000;
                    font-weight: 500; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                `;
                notification.textContent = message;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 3000);
            }
        }
    </script>

    <!-- CSS supplémentaire pour la vue grille -->
    <style>
        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .event-grid-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .event-grid-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .grid-card-image {
            position: relative;
            height: 150px;
            overflow: hidden;
        }
        
        .grid-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .grid-card-date {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.9);
            padding: 0.5rem;
            border-radius: 8px;
            text-align: center;
            backdrop-filter: blur(5px);
        }
        
        .grid-card-date .day {
            display: block;
            font-size: 1.2rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .grid-card-date .month {
            display: block;
            font-size: 0.7rem;
            color: #7f8c8d;
            text-transform: uppercase;
        }
        
        .grid-card-content {
            padding: 1.5rem;
        }
        
        .grid-card-content h4 {
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        
        .grid-card-content h4 a {
            color: #2c3e50;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .grid-card-content h4 a:hover {
            color: #f39c12;
        }
        
        .grid-card-location,
        .grid-card-participants {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .grid-card-location i,
        .grid-card-participants i {
            color: #f39c12;
            width: 16px;
        }
        
        .grid-card-actions {
            padding: 1rem 1.5rem;
            border-top: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.85rem;
            border-radius: 8px;
        }
        
        .btn-favorite.btn-sm {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</body>
</html>