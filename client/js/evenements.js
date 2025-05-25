// JavaScript pour la page des événements
document.addEventListener('DOMContentLoaded', function() {
    initEventHandlers();
    initViewToggle();
    initParticipationButtons();
    initFavoriteButtons();
    initNewsletterForm();
    initScrollAnimations();
    animateHeroStats();
});

// Initialisation des gestionnaires d'événements
function initEventHandlers() {
    // Gestion du clic sur les cartes d'événements
    const eventCards = document.querySelectorAll('.event-card, .featured-card');
    eventCards.forEach(card => {
        card.addEventListener('click', function(e) {
            // Éviter le clic si on clique sur un bouton
            if (e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            const eventId = this.dataset.id || this.querySelector('[data-event]')?.dataset.event;
            if (eventId) {
                window.location.href = `evenement-details.php?id=${eventId}`;
            }
        });
    });
}

// Gestion du toggle vue grille/liste
function initViewToggle() {
    const viewButtons = document.querySelectorAll('.view-btn');
    const eventsContainer = document.getElementById('events-container');
    
    viewButtons.forEach(button => {
        button.addEventListener('click', function() {
            const view = this.dataset.view;
            
            // Mettre à jour les boutons actifs
            viewButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            
            // Changer la vue
            if (view === 'list') {
                eventsContainer.classList.add('list-view');
            } else {
                eventsContainer.classList.remove('list-view');
            }
            
            // Animation de transition
            eventsContainer.style.opacity = '0.5';
            setTimeout(() => {
                eventsContainer.style.opacity = '1';
            }, 300);
        });
    });
}

// Gestion des boutons de participation
function initParticipationButtons() {
    const participateButtons = document.querySelectorAll('.btn-participate');
    
    participateButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const eventId = this.dataset.event;
            const isParticipating = this.classList.contains('participating');
            
            if (isParticipating) {
                unparticipateEvent(eventId, this);
            } else {
                participateEvent(eventId, this);
            }
        });
    });
}

// Fonction pour participer à un événement
function participateEvent(eventId, button) {
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription...';
    
    fetch('actions/participer-evenement.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idEvenement: eventId,
            action: 'participer'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.add('participating');
            button.innerHTML = '<i class="fas fa-check"></i> Inscrit';
            button.style.background = '#27ae60';
            
            // Mettre à jour le nombre de participants
            updateParticipantCount(eventId, 1);
            
            showNotification('Inscription confirmée !', 'success');
            
            // Remettre le bouton après 2 secondes
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-times"></i> Se désinscrire';
                button.style.background = '';
            }, 2000);
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-plus"></i> Participer';
            showNotification(data.message || 'Erreur lors de l\'inscription', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-plus"></i> Participer';
        showNotification('Erreur de connexion', 'error');
    });
}

// Fonction pour se désinscrire d'un événement
function unparticipateEvent(eventId, button) {
    if (!confirm('Êtes-vous sûr de vouloir vous désinscrire de cet événement ?')) {
        return;
    }
    
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Désinscription...';
    
    fetch('actions/participer-evenement.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idEvenement: eventId,
            action: 'desinscrire'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            button.classList.remove('participating');
            button.innerHTML = '<i class="fas fa-plus"></i> Participer';
            button.style.background = '';
            
            // Mettre à jour le nombre de participants
            updateParticipantCount(eventId, -1);
            
            showNotification('Désinscription confirmée', 'info');
            button.disabled = false;
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-times"></i> Se désinscrire';
            showNotification(data.message || 'Erreur lors de la désinscription', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-times"></i> Se désinscrire';
        showNotification('Erreur de connexion', 'error');
    });
}

// Gestion des boutons favoris
function initFavoriteButtons() {
    const favoriteButtons = document.querySelectorAll('.btn-favorite');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.stopPropagation();
            
            const eventId = this.dataset.event;
            const isFavorite = this.classList.contains('active');
            
            toggleFavoriteEvent(eventId, this, isFavorite);
        });
    });
}

// Fonction pour toggle les favoris
function toggleFavoriteEvent(eventId, button, isFavorite) {
    const icon = button.querySelector('i');
    
    fetch('actions/favoris-evenement.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idEvenement: eventId,
            action: isFavorite ? 'retirer' : 'ajouter'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isFavorite) {
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                showNotification('Retiré des favoris', 'info');
            } else {
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Ajouté aux favoris !', 'success');
                
                // Animation de cœur
                button.style.animation = 'heartBeat 0.6s ease';
                setTimeout(() => {
                    button.style.animation = '';
                }, 600);
            }
        } else {
            showNotification(data.message || 'Erreur lors de la modification des favoris', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// Gestion du formulaire newsletter
function initNewsletterForm() {
    const newsletterForm = document.querySelector('.newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('.newsletter-input');
            const submitBtn = this.querySelector('.btn-newsletter');
            const email = emailInput.value.trim();
            
            if (!email || !isValidEmail(email)) {
                showNotification('Veuillez entrer une adresse email valide', 'error');
                return;
            }
            
            // Désactiver le formulaire
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription...';
            
            // Simuler l'inscription à la newsletter
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-bell"></i> S\'abonner';
                emailInput.value = '';
                showNotification('Inscription à la newsletter confirmée !', 'success');
            }, 1500);
        });
    }
}

// Fonction de partage d'événement
function partagerEvenement(eventId) {
    const url = `${window.location.origin}/evenement-details.php?id=${eventId}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Événement Artistique - Artisano',
            text: 'Découvrez cet événement artistique exceptionnel !',
            url: url
        });
    } else {
        // Fallback pour les navigateurs qui ne supportent pas l'API Web Share
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Lien copié dans le presse-papiers !', 'success');
        }).catch(() => {
            // Fallback ultime
            prompt('Copiez ce lien:', url);
        });
    }
}

// Mettre à jour le nombre de participants
function updateParticipantCount(eventId, change) {
    const participantElements = document.querySelectorAll(`[data-event="${eventId}"]`)
        .forEach(element => {
            const card = element.closest('.event-card, .featured-card');
            const participantSpan = card.querySelector('.meta-row:last-child span, .meta-item:last-child span');
            
            if (participantSpan) {
                const currentText = participantSpan.textContent;
                const currentCount = parseInt(currentText.match(/\d+/)[0]);
                const newCount = Math.max(0, currentCount + change);
                participantSpan.textContent = `${newCount} participant${newCount > 1 ? 's' : ''}`;
            }
        });
}

// Animations au scroll
function initScrollAnimations() {
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

    const animatedElements = document.querySelectorAll('.event-card, .featured-card, .filters-section, .newsletter-section');
    animatedElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(element);
    });
}

// Animation des statistiques du hero
function animateHeroStats() {
    const statNumbers = document.querySelectorAll('.stat-number');
    
    const animateCounter = (element) => {
        const target = parseInt(element.textContent);
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target;
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current);
            }
        }, 40);
    };
    
    // Démarrer l'animation après un délai
    setTimeout(() => {
        statNumbers.forEach(animateCounter);
    }, 800);
}

// Fonction utilitaire pour valider email
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());

    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${getNotificationIcon(type)}"></i>
        <span>${message}</span>
        <button class="notification-close">&times;</button>
    `;

    // Ajouter les styles CSS si pas déjà présents
    if (!document.getElementById('notification-styles')) {
        const styles = document.createElement('style');
        styles.id = 'notification-styles';
        styles.textContent = `
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
                min-width: 300px;
                max-width: 400px;
            }
            .notification.show {
                transform: translateX(0);
            }
            .notification-success {
                background: linear-gradient(135deg, #27ae60, #229954);
            }
            .notification-error {
                background: linear-gradient(135deg, #e74c3c, #c0392b);
            }
            .notification-info {
                background: linear-gradient(135deg, #3498db, #2980b9);
            }
            .notification-close {
                background: none;
                border: none;
                color: white;
                font-size: 18px;
                cursor: pointer;
                padding: 0;
                margin-left: auto;
                opacity: 0.8;
                transition: opacity 0.3s ease;
            }
            .notification-close:hover {
                opacity: 1;
            }
            @keyframes heartBeat {
                0%, 100% { transform: scale(1); }
                25% { transform: scale(1.2); }
                50% { transform: scale(1.1); }
                75% { transform: scale(1.15); }
            }
        `;
        document.head.appendChild(styles);
    }

    // Ajouter au DOM
    document.body.appendChild(notification);

    // Afficher avec animation
    setTimeout(() => notification.classList.add('show'), 100);

    // Gestion de la fermeture
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    });

    // Auto-fermeture après 5 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }
    }, 5000);
}

// Fonction utilitaire pour les icônes de notification
function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'info': return 'info-circle';
        default: return 'bell';
    }
}

// Gestion des filtres en temps réel (optionnel)
function initRealTimeFilters() {
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                filterEventsRealTime(this.value);
            }, 500);
        });
    }
}

// Filtrage en temps réel des événements
function filterEventsRealTime(searchTerm) {
    const eventCards = document.querySelectorAll('.event-card');
    
    if (!searchTerm.trim()) {
        eventCards.forEach(card => {
            card.style.display = 'block';
        });
        return;
    }

    eventCards.forEach(card => {
        const title = card.querySelector('.event-title').textContent.toLowerCase();
        const description = card.querySelector('.event-description').textContent.toLowerCase();
        const location = card.querySelector('.meta-row:nth-child(2) span').textContent.toLowerCase();
        
        const searchLower = searchTerm.toLowerCase();
        
        if (title.includes(searchLower) || description.includes(searchLower) || location.includes(searchLower)) {
            card.style.display = 'block';
            card.style.animation = 'fadeInUp 0.3s ease';
        } else {
            card.style.display = 'none';
        }
    });
}

// Gestion du lazy loading des images
function initLazyLoading() {
    const images = document.querySelectorAll('.event-image img, .featured-image img');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                
                // Ajouter un effet de loading
                img.style.opacity = '0';
                img.style.transition = 'opacity 0.3s ease';
                
                // Charger l'image
                const tempImg = new Image();
                tempImg.onload = () => {
                    img.style.opacity = '1';
                };
                tempImg.onerror = () => {
                    img.src = 'images/event-placeholder.jpg';
                    img.style.opacity = '1';
                };
                tempImg.src = img.src;
                
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Fonction pour gérer les états de l'interface
function updateUIStates() {
    // Mettre à jour l'état des boutons de participation
    const participateButtons = document.querySelectorAll('.btn-participate');
    participateButtons.forEach(button => {
        if (button.classList.contains('participating')) {
            button.innerHTML = '<i class="fas fa-times"></i> Se désinscrire';
        }
    });
    
    // Mettre à jour l'état des favoris
    const favoriteButtons = document.querySelectorAll('.btn-favorite');
    favoriteButtons.forEach(button => {
        if (button.classList.contains('active')) {
            const icon = button.querySelector('i');
            icon.classList.remove('far');
            icon.classList.add('fas');
        }
    });
}

// Gestion des raccourcis clavier
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K pour focus sur la recherche
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape pour fermer les notifications
        if (e.key === 'Escape') {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notif => {
                notif.classList.remove('show');
                setTimeout(() => notif.remove(), 300);
            });
        }
    });
}

// Gestion de la géolocalisation pour les événements proches
function initGeolocation() {
    if ('geolocation' in navigator) {
        const geoBtn = document.getElementById('nearby-events-btn');
        if (geoBtn) {
            geoBtn.addEventListener('click', function() {
                this.disabled = true;
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation...';
                
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const { latitude, longitude } = position.coords;
                        findNearbyEvents(latitude, longitude);
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-map-marker-alt"></i> Événements proches';
                    },
                    (error) => {
                        console.error('Erreur de géolocalisation:', error);
                        this.disabled = false;
                        this.innerHTML = '<i class="fas fa-map-marker-alt"></i> Événements proches';
                        showNotification('Impossible de récupérer votre position', 'error');
                    }
                );
            });
        }
    }
}

// Fonction pour trouver les événements proches
function findNearbyEvents(latitude, longitude) {
    // Cette fonction pourrait être implémentée pour filtrer les événements par proximité
    showNotification('Recherche d\'événements proches...', 'info');
    
    // Simulation de la recherche
    setTimeout(() => {
        showNotification('3 événements trouvés à proximité !', 'success');
    }, 1500);
}

// Initialisation des fonctionnalités avancées
function initAdvancedFeatures() {
    initRealTimeFilters();
    initLazyLoading();
    initKeyboardShortcuts();
    initGeolocation();
    updateUIStates();
}

// Appeler les fonctionnalités avancées après le chargement complet
window.addEventListener('load', function() {
    initAdvancedFeatures();
});

// Gestion de la navigation retour
window.addEventListener('popstate', function(event) {
    // Rafraîchir l'état de l'interface si l'utilisateur revient sur la page
    setTimeout(() => {
        updateUIStates();
    }, 100);
});

// Fonction utilitaire pour déboguer
function debugEventSystem() {
    console.log('=== Debug Système Événements ===');
    console.log('Nombre d\'événements:', document.querySelectorAll('.event-card').length);
    console.log('Nombre d\'événements à la une:', document.querySelectorAll('.featured-card').length);
    console.log('Vue active:', document.querySelector('.view-btn.active')?.dataset.view || 'grid');
    console.log('Boutons de participation:', document.querySelectorAll('.btn-participate').length);
    console.log('Boutons favoris:', document.querySelectorAll('.btn-favorite').length);
}

// Exposer la fonction de debug en mode développement
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.debugEventSystem = debugEventSystem;
}