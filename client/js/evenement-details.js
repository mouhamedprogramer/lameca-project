// JavaScript pour la page de détails d'événement
document.addEventListener('DOMContentLoaded', function() {
    initEventDetails();
    initScrollAnimations();
    initParallaxEffect();
    initCountdown();
});

// Initialisation principale
function initEventDetails() {
    setupParticipationButton();
    setupFavoriteButton();
    setupShareButton();
    setupQuickActions();
    setupSimilarEvents();
    setupImageLazyLoading();
}

// Configuration du bouton de participation
function setupParticipationButton() {
    const participateBtn = document.querySelector('.btn-participate');
    
    if (participateBtn) {
        participateBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const eventId = this.dataset.event;
            const isParticipating = this.classList.contains('participating');
            
            if (isParticipating) {
                unparticipateEvent(eventId, this);
            } else {
                participateEvent(eventId, this);
            }
        });
    }
}

// Fonction pour participer à un événement
function participateEvent(eventId, button) {
    // Désactiver le bouton et afficher le chargement
    button.disabled = true;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inscription...';
    
    // Ajouter l'effet de chargement
    button.classList.add('loading');
    
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
        button.classList.remove('loading');
        
        if (data.success) {
            // Succès de l'inscription
            button.classList.add('participating');
            button.innerHTML = '<i class="fas fa-check"></i> Inscrit';
            button.style.background = 'rgba(39, 174, 96, 0.9)';
            
            // Mettre à jour le compteur de participants
            updateParticipantCount(1);
            
            // Animation de succès
            button.style.animation = 'bounce 0.6s ease';
            setTimeout(() => {
                button.style.animation = '';
            }, 600);
            
            showNotification('🎉 Inscription confirmée ! Vous recevrez bientôt un email de confirmation.', 'success');
            
            // Remettre le bouton après 3 secondes
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-times"></i> Se désinscrire';
                button.style.background = '';
            }, 3000);
            
            // Ajouter automatiquement au calendrier si demandé
            if (confirm('Voulez-vous ajouter cet événement à votre calendrier ?')) {
                ajouterAuCalendrier();
            }
        } else {
            // Erreur lors de l'inscription
            button.disabled = false;
            button.innerHTML = originalContent;
            showNotification(data.message || 'Erreur lors de l\'inscription. Veuillez réessayer.', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur lors de l\'inscription:', error);
        button.classList.remove('loading');
        button.disabled = false;
        button.innerHTML = originalContent;
        showNotification('Erreur de connexion. Vérifiez votre connexion internet.', 'error');
    });
}

// Fonction pour se désinscrire d'un événement
function unparticipateEvent(eventId, button) {
    // Demander confirmation
    if (!confirm('Êtes-vous sûr de vouloir vous désinscrire de cet événement ?')) {
        return;
    }
    
    button.disabled = true;
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Désinscription...';
    button.classList.add('loading');
    
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
        button.classList.remove('loading');
        
        if (data.success) {
            // Succès de la désinscription
            button.classList.remove('participating');
            button.innerHTML = '<i class="fas fa-plus"></i> Participer';
            button.style.background = '';
            
            // Mettre à jour le compteur
            updateParticipantCount(-1);
            
            showNotification('Désinscription confirmée', 'info');
            button.disabled = false;
        } else {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-times"></i> Se désinscrire';
            showNotification(data.message || 'Erreur lors de la désinscription', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur lors de la désinscription:', error);
        button.classList.remove('loading');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-times"></i> Se désinscrire';
        showNotification('Erreur de connexion', 'error');
    });
}

// Configuration du bouton favoris
function setupFavoriteButton() {
    const favoriteBtn = document.querySelector('.btn-favorite');
    
    if (favoriteBtn) {
        favoriteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const eventId = this.dataset.event;
            toggleFavoriteEvent(eventId, this);
        });
    }
}

// Fonction pour toggle les favoris
function toggleFavoriteEvent(eventId, button) {
    const icon = button.querySelector('i');
    const isActive = button.classList.contains('active');
    
    // Animation immédiate pour le feedback utilisateur
    button.style.transform = 'scale(0.95)';
    setTimeout(() => {
        button.style.transform = '';
    }, 150);
    
    fetch('actions/favoris-evenement.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idEvenement: eventId,
            action: isActive ? 'retirer' : 'ajouter'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (isActive) {
                // Retirer des favoris
                button.classList.remove('active');
                icon.classList.remove('fas');
                icon.classList.add('far');
                button.style.background = '';
                showNotification('Retiré des favoris', 'info');
            } else {
                // Ajouter aux favoris
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                button.style.background = 'rgba(231, 76, 60, 0.9)';
                
                // Animation de cœur
                button.style.animation = 'heartBeat 0.8s ease';
                setTimeout(() => {
                    button.style.animation = '';
                }, 800);
                
                showNotification('❤️ Ajouté aux favoris !', 'success');
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

// Configuration du bouton de partage
function setupShareButton() {
    const shareBtn = document.querySelector('.btn-share');
    
    if (shareBtn) {
        shareBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const eventId = this.dataset.event || getEventIdFromUrl();
            partagerEvenement(eventId);
        });
    }
}

// Fonction de partage d'événement
function partagerEvenement(eventId) {
    const url = window.location.href;
    const title = document.querySelector('.event-title').textContent;
    const description = document.querySelector('.description-content p').textContent.substring(0, 100) + '...';
    
    // Vérifier si l'API Web Share est supportée
    if (navigator.share) {
        navigator.share({
            title: title + ' - Artisano',
            text: description,
            url: url
        }).then(() => {
            showNotification('Événement partagé avec succès !', 'success');
        }).catch((error) => {
            console.log('Erreur lors du partage:', error);
            fallbackShare(url, title);
        });
    } else {
        fallbackShare(url, title);
    }
}

// Fonction de partage fallback
function fallbackShare(url, title) {
    // Essayer de copier dans le presse-papiers
    if (navigator.clipboard) {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('🔗 Lien copié dans le presse-papiers !', 'success');
        }).catch(() => {
            showShareModal(url, title);
        });
    } else {
        showShareModal(url, title);
    }
}

// Afficher un modal de partage
function showShareModal(url, title) {
    const modal = document.createElement('div');
    modal.className = 'share-modal';
    modal.innerHTML = `
        <div class="share-modal-content">
            <div class="share-modal-header">
                <h3>Partager cet événement</h3>
                <button class="share-modal-close">&times;</button>
            </div>
            <div class="share-modal-body">
                <div class="share-options">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" class="share-btn facebook">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" target="_blank" class="share-btn twitter">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="https://wa.me/?text=${encodeURIComponent(title + ' ' + url)}" target="_blank" class="share-btn whatsapp">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}" class="share-btn email">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                </div>
                <div class="share-link">
                    <input type="text" value="${url}" readonly class="share-url">
                    <button class="copy-link-btn">Copier</button>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter les styles du modal
    addShareModalStyles();
    
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('show'), 10);
    
    // Gestion de la fermeture
    const closeBtn = modal.querySelector('.share-modal-close');
    const copyBtn = modal.querySelector('.copy-link-btn');
    
    closeBtn.addEventListener('click', () => {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    });
    
    copyBtn.addEventListener('click', () => {
        const input = modal.querySelector('.share-url');
        input.select();
        document.execCommand('copy');
        copyBtn.textContent = 'Copié !';
        setTimeout(() => copyBtn.textContent = 'Copier', 2000);
    });
    
    // Fermer en cliquant à l'extérieur
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('show');
            setTimeout(() => modal.remove(), 300);
        }
    });
}

// Ajouter les styles du modal de partage
function addShareModalStyles() {
    if (document.getElementById('share-modal-styles')) return;
    
    const styles = document.createElement('style');
    styles.id = 'share-modal-styles';
    styles.textContent = `
        .share-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .share-modal.show {
            opacity: 1;
        }
        .share-modal-content {
            background: white;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow: hidden;
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }
        .share-modal.show .share-modal-content {
            transform: scale(1);
        }
        .share-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .share-modal-header h3 {
            margin: 0;
            color: #333;
        }
        .share-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        .share-modal-body {
            padding: 20px;
        }
        .share-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .share-btn {
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            color: white;
            text-align: center;
            font-weight: 500;
            transition: transform 0.3s ease;
        }
        .share-btn:hover {
            transform: translateY(-2px);
        }
        .share-btn.facebook { background: #3b5998; }
        .share-btn.twitter { background: #1da1f2; }
        .share-btn.whatsapp { background: #25d366; }
        .share-btn.email { background: #666; }
        .share-link {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .share-url {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        .copy-link-btn {
            padding: 10px 15px;
            background: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    `;
    document.head.appendChild(styles);
}

// Configuration des actions rapides
function setupQuickActions() {
    // Bouton calendrier
    const calendarBtn = document.querySelector('[onclick*="ajouterAuCalendrier"]');
    if (calendarBtn) {
        calendarBtn.removeAttribute('onclick');
        calendarBtn.addEventListener('click', ajouterAuCalendrier);
    }
    
    // Bouton carte
    const mapBtns = document.querySelectorAll('[onclick*="ouvrirCarte"]');
    mapBtns.forEach(btn => {
        const lieu = btn.getAttribute('onclick').match(/'([^']+)'/)[1];
        btn.removeAttribute('onclick');
        btn.addEventListener('click', () => ouvrirCarte(decodeURIComponent(lieu)));
    });
    
    // Bouton signalement
    const reportBtn = document.querySelector('[onclick*="signalerEvenement"]');
    if (reportBtn) {
        const eventId = reportBtn.getAttribute('onclick').match(/\d+/)[0];
        reportBtn.removeAttribute('onclick');
        reportBtn.addEventListener('click', () => signalerEvenement(eventId));
    }
}

// Fonction pour ajouter au calendrier
function ajouterAuCalendrier() {
    // Récupérer les informations de l'événement depuis la page
    const title = document.querySelector('.event-title').textContent;
    const description = document.querySelector('.description-content p').textContent;
    const location = document.querySelector('.meta-item:nth-child(2) strong').textContent;
    
    // Créer le contenu ICS
    const now = new Date();
    const startDate = getEventStartDate(); // À implémenter selon vos données
    
    const icsContent = `BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Artisano//Event//FR
BEGIN:VEVENT
UID:${Date.now()}@artisano.com
DTSTAMP:${formatDateForICS(now)}
DTSTART:${formatDateForICS(startDate)}
SUMMARY:${title}
DESCRIPTION:${description.replace(/\n/g, '\\n')}
LOCATION:${location}
URL:${window.location.href}
END:VEVENT
END:VCALENDAR`;
    
    // Créer et télécharger le fichier
    const blob = new Blob([icsContent], { type: 'text/calendar;charset=utf-8' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `evenement-${title.replace(/[^a-z0-9]/gi, '-').toLowerCase()}.ics`;
    link.click();
    
    URL.revokeObjectURL(url);
    showNotification('📅 Événement ajouté au calendrier !', 'success');
}

// Fonction pour ouvrir la carte
function ouvrirCarte(lieu) {
    const url = `https://www.google.com/maps/search/${encodeURIComponent(lieu)}`;
    const mapWindow = window.open(url, '_blank');
    
    if (!mapWindow) {
        // Fallback si le popup est bloqué
        showNotification('Redirection vers Google Maps...', 'info');
        setTimeout(() => {
            window.location.href = url;
        }, 1000);
    } else {
        showNotification('🗺️ Carte ouverte dans un nouvel onglet', 'success');
    }
}

// Fonction pour signaler un événement
function signalerEvenement(eventId) {
    const reasons = [
        'Contenu inapproprié',
        'Informations incorrectes',
        'Spam ou publicité',
        'Événement annulé',
        'Autre (préciser)'
    ];
    
    let reasonsHtml = reasons.map((reason, index) => 
        `<label><input type="radio" name="reason" value="${reason}"> ${reason}</label>`
    ).join('<br>');
    
    const modal = document.createElement('div');
    modal.className = 'report-modal';
    modal.innerHTML = `
        <div class="report-modal-content">
            <h3>Signaler cet événement</h3>
            <div class="report-form">
                <p>Pourquoi signalez-vous cet événement ?</p>
                ${reasonsHtml}
                <textarea placeholder="Commentaire (optionnel)" id="report-comment"></textarea>
                <div class="report-actions">
                    <button class="btn-cancel">Annuler</button>
                    <button class="btn-report">Signaler</button>
                </div>
            </div>
        </div>
    `;
    
    // Ajouter les styles
    addReportModalStyles();
    
    document.body.appendChild(modal);
    setTimeout(() => modal.classList.add('show'), 10);
    
    // Gestion des événements
    modal.querySelector('.btn-cancel').addEventListener('click', () => {
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    });
    
    modal.querySelector('.btn-report').addEventListener('click', () => {
        const selectedReason = modal.querySelector('input[name="reason"]:checked');
        const comment = modal.querySelector('#report-comment').value;
        
        if (!selectedReason) {
            alert('Veuillez sélectionner un motif');
            return;
        }
        
        // Envoyer le signalement
        sendReport(eventId, selectedReason.value, comment);
        modal.classList.remove('show');
        setTimeout(() => modal.remove(), 300);
    });
}

// Envoyer le signalement
function sendReport(eventId, reason, comment) {
    fetch('actions/signaler-evenement.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idEvenement: eventId,
            motif: reason,
            commentaire: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('🚨 Signalement envoyé. Merci pour votre vigilance.', 'success');
        } else {
            showNotification('Erreur lors de l\'envoi du signalement', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// Ajouter les styles du modal de signalement
function addReportModalStyles() {
    if (document.getElementById('report-modal-styles')) return;
    
    const styles = document.createElement('style');
    styles.id = 'report-modal-styles';
    styles.textContent = `
        .report-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .report-modal.show {
            opacity: 1;
        }
        .report-modal-content {
            background: white;
            border-radius: 15px;
            max-width: 400px;
            width: 90%;
            padding: 30px;
            transform: scale(0.8);
            transition: transform 0.3s ease;
        }
        .report-modal.show .report-modal-content {
            transform: scale(1);
        }
        .report-form label {
            display: block;
            margin: 10px 0;
            cursor: pointer;
        }
        .report-form textarea {
            width: 100%;
            margin: 15px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            resize: vertical;
        }
        .report-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
        }
        .btn-cancel, .btn-report {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        .btn-cancel {
            background: #f0f0f0;
            color: #666;
        }
        .btn-report {
            background: #e74c3c;
            color: white;
        }
    `;
    document.head.appendChild(styles);
}

// Configuration des événements similaires
function setupSimilarEvents() {
    const similarCards = document.querySelectorAll('.similar-card');
    
    similarCards.forEach(card => {
        card.addEventListener('click', function() {
            const eventId = this.getAttribute('onclick')?.match(/\d+/)?.[0];
            if (eventId) {
                window.location.href = `evenement-details.php?id=${eventId}`;
            }
        });
    });
}

// Mettre à jour le nombre de participants
function updateParticipantCount(change) {
    const countElements = document.querySelectorAll('.meta-item strong, .info-content strong');
    
    countElements.forEach(element => {
        const text = element.textContent;
        if (text.includes('participant')) {
            const currentCount = parseInt(text.match(/\d+/)?.[0] || 0);
            const newCount = Math.max(0, currentCount + change);
            element.textContent = newCount + ' participant' + (newCount > 1 ? 's' : '');
        }
    });
    
    // Mettre à jour le titre de la section participants si elle existe
    const participantsSection = document.querySelector('.participants-section h2');
    if (participantsSection) {
        const text = participantsSection.textContent;
        const currentCount = parseInt(text.match(/\(\d+\)/)?.[0]?.replace(/[()]/g, '') || 0);
        const newCount = Math.max(0, currentCount + change);
        participantsSection.innerHTML = participantsSection.innerHTML.replace(/\(\d+\)/, `(${newCount})`);
    }
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

    const animatedElements = document.querySelectorAll('.main-content-section > *, .sidebar > *');
    animatedElements.forEach((element, index) => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(30px)';
        element.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
        observer.observe(element);
    });
}

// Effet parallaxe pour le hero
function initParallaxEffect() {
    const heroImage = document.querySelector('.hero-image img');
    
    if (heroImage) {
        window.addEventListener('scroll', () => {
            const scrollTop = window.pageYOffset;
            const rate = scrollTop * -0.5;
            heroImage.style.transform = `translateY(${rate}px)`;
        });
    }
}

// Initialiser le compte à rebours
function initCountdown() {
    const countdownElement = document.querySelector('.countdown');
    
    if (countdownElement) {
        updateCountdown();
        setInterval(updateCountdown, 60000); // Mettre à jour chaque minute
    }
}

// Mettre à jour le compte à rebours
function updateCountdown() {
    const eventDate = getEventStartDate();
    if (!eventDate) return;
    
    const now = new Date();
    const difference = eventDate.getTime() - now.getTime();
    
    if (difference <= 0) {
        document.querySelector('.countdown').textContent = 'Événement commencé';
        return;
    }
    
    const days = Math.floor(difference / (1000 * 60 * 60 * 24));
    const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
    
    let countdownText = '';
    if (days > 0) {
        countdownText = `Dans ${days} jour${days > 1 ? 's' : ''}`;
    } else if (hours > 0) {
        countdownText = `Dans ${hours} heure${hours > 1 ? 's' : ''}`;
    } else {
        countdownText = `Dans ${minutes} minute${minutes > 1 ? 's' : ''}`;
    }
    
    document.querySelector('.countdown').textContent = countdownText;
}

// Lazy loading des images
function setupImageLazyLoading() {
    const images = document.querySelectorAll('img[data-src]');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}

// Fonctions utilitaires
function getEventIdFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

function getEventStartDate() {
    // Cette fonction devrait extraire la date de début de l'événement depuis la page
    // Pour l'exemple, on retourne une date fictive
    const dateText = document.querySelector('.meta-item strong')?.textContent;
    if (!dateText) return null;
    
    // Parser la date en français (à adapter selon votre format)
    try {
        // Exemple: "15 Décembre 2024"
        const months = {
            'Janvier': 0, 'Février': 1, 'Mars': 2, 'Avril': 3, 'Mai': 4, 'Juin': 5,
            'Juillet': 6, 'Août': 7, 'Septembre': 8, 'Octobre': 9, 'Novembre': 10, 'Décembre': 11
        };
        
        const parts = dateText.split(' ');
        if (parts.length >= 3) {
            const day = parseInt(parts[0]);
            const month = months[parts[1]];
            const year = parseInt(parts[2]);
            
            if (!isNaN(day) && month !== undefined && !isNaN(year)) {
                return new Date(year, month, day);
            }
        }
    } catch (error) {
        console.error('Erreur lors du parsing de la date:', error);
    }
    
    return null;
}

function formatDateForICS(date) {
    return date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
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
                border-radius: 12px;
                color: white;
                font-weight: 500;
                z-index: 10000;
                display: flex;
                align-items: center;
                gap: 10px;
                transform: translateX(400px);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 8px 32px rgba(0,0,0,0.2);
                min-width: 320px;
                max-width: 450px;
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255,255,255,0.1);
            }
            .notification.show {
                transform: translateX(0);
            }
            .notification-success {
                background: linear-gradient(135deg, rgba(39, 174, 96, 0.9), rgba(34, 153, 84, 0.9));
            }
            .notification-error {
                background: linear-gradient(135deg, rgba(231, 76, 60, 0.9), rgba(192, 57, 43, 0.9));
            }
            .notification-info {
                background: linear-gradient(135deg, rgba(52, 152, 219, 0.9), rgba(41, 128, 185, 0.9));
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
                transition: opacity 0.3s ease, transform 0.3s ease;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
            }
            .notification-close:hover {
                opacity: 1;
                transform: scale(1.1);
                background: rgba(255,255,255,0.1);
            }
            @keyframes bounce {
                0%, 20%, 60%, 100% { transform: translateY(0); }
                40% { transform: translateY(-10px); }
                80% { transform: translateY(-5px); }
            }
            @keyframes heartBeat {
                0%, 100% { transform: scale(1); }
                14% { transform: scale(1.3); }
                28% { transform: scale(1); }
                42% { transform: scale(1.3); }
                70% { transform: scale(1); }
            }
            .loading {
                position: relative;
                overflow: hidden;
            }
            .loading::after {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                animation: shimmer 1.5s infinite;
            }
            @keyframes shimmer {
                0% { left: -100%; }
                100% { left: 100%; }
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
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    });

    // Auto-fermeture après 5 secondes
    setTimeout(() => {
        if (notification.parentNode) {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);

    // Effet de vibration pour les erreurs sur mobile
    if (type === 'error' && navigator.vibrate) {
        navigator.vibrate([100, 50, 100]);
    }
}

// Fonction utilitaire pour les icônes de notification
function getNotificationIcon(type) {
    switch (type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'info': return 'info-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'bell';
    }
}

// Gestion des raccourcis clavier
function initKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Échap pour fermer les modaux
        if (e.key === 'Escape') {
            const modals = document.querySelectorAll('.share-modal, .report-modal');
            modals.forEach(modal => {
                modal.classList.remove('show');
                setTimeout(() => modal.remove(), 300);
            });
            
            // Fermer les notifications
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notif => {
                notif.classList.remove('show');
                setTimeout(() => notif.remove(), 300);
            });
        }
        
        // P pour participer (si connecté)
        if (e.key === 'p' || e.key === 'P') {
            const participateBtn = document.querySelector('.btn-participate');
            if (participateBtn && !e.ctrlKey && !e.altKey && !e.metaKey) {
                e.preventDefault();
                participateBtn.click();
            }
        }
        
        // F pour favoris (si connecté)
        if (e.key === 'f' || e.key === 'F') {
            const favoriteBtn = document.querySelector('.btn-favorite');
            if (favoriteBtn && !e.ctrlKey && !e.altKey && !e.metaKey) {
                e.preventDefault();
                favoriteBtn.click();
            }
        }
        
        // S pour partager
        if (e.key === 's' || e.key === 'S') {
            const shareBtn = document.querySelector('.btn-share');
            if (shareBtn && !e.ctrlKey && !e.altKey && !e.metaKey) {
                e.preventDefault();
                shareBtn.click();
            }
        }
    });
}

// Gestion des interactions tactiles sur mobile
function initTouchGestures() {
    let touchStartY = 0;
    let touchEndY = 0;

    document.addEventListener('touchstart', e => {
        touchStartY = e.changedTouches[0].screenY;
    });

    document.addEventListener('touchend', e => {
        touchEndY = e.changedTouches[0].screenY;
        handleGesture();
    });

    function handleGesture() {
        const swipeThreshold = 50;
        const diff = touchStartY - touchEndY;

        // Swipe vers le haut pour scroller vers les actions
        if (diff > swipeThreshold) {
            const heroActions = document.querySelector('.hero-actions');
            if (heroActions && window.scrollY < 100) {
                heroActions.scrollIntoView({ behavior: 'smooth' });
            }
        }
    }
}

// Optimisation des performances
function initPerformanceOptimizations() {
    // Lazy loading des images de fond
    const heroImage = document.querySelector('.hero-image img');
    if (heroImage) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    observer.unobserve(entry.target);
                }
            });
        });
        
        heroImage.style.opacity = '0';
        heroImage.style.transition = 'opacity 0.5s ease';
        observer.observe(heroImage);
    }

    // Debounce pour le scroll
    let ticking = false;
    function updateScrollEffects() {
        initParallaxEffect();
        ticking = false;
    }

    window.addEventListener('scroll', () => {
        if (!ticking) {
            requestAnimationFrame(updateScrollEffects);
            ticking = true;
        }
    });
}

// Gestion des erreurs JavaScript
function initErrorHandling() {
    window.addEventListener('error', function(e) {
        console.error('Erreur JavaScript:', e.error);
        // En production, vous pourriez envoyer ces erreurs à un service de monitoring
    });

    window.addEventListener('unhandledrejection', function(e) {
        console.error('Promise rejetée:', e.reason);
        // Empêcher l'affichage de l'erreur dans la console
        e.preventDefault();
    });
}

// Analytics et suivi des événements
function initAnalytics() {
    // Suivi des clics sur les boutons
    document.addEventListener('click', function(e) {
        const button = e.target.closest('button, a');
        if (button) {
            const action = button.className.includes('participate') ? 'participate' :
                          button.className.includes('favorite') ? 'favorite' :
                          button.className.includes('share') ? 'share' : null;
            
            if (action) {
                // Ici vous pourriez envoyer des données à votre service d'analytics
                console.log(`Action: ${action}, Event ID: ${getEventIdFromUrl()}`);
            }
        }
    });
}

// Initialisation complète au chargement de la page
window.addEventListener('load', function() {
    initKeyboardShortcuts();
    initTouchGestures();
    initPerformanceOptimizations();
    initErrorHandling();
    initAnalytics();
    
    // Précharger les ressources importantes
    preloadCriticalResources();
});

// Préchargement des ressources
function preloadCriticalResources() {
    // Précharger les images des événements similaires
    const similarImages = document.querySelectorAll('.similar-image img');
    similarImages.forEach(img => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.as = 'image';
        link.href = img.src;
        document.head.appendChild(link);
    });
}

// Fonction de débogage pour le développement
function debugEventDetails() {
    console.log('=== Debug Détails Événement ===');
    console.log('ID Événement:', getEventIdFromUrl());
    console.log('Bouton participation:', document.querySelector('.btn-participate'));
    console.log('Bouton favoris:', document.querySelector('.btn-favorite'));
    console.log('Nombre de participants:', document.querySelector('.meta-item strong')?.textContent);
    console.log('Date de l\'événement:', getEventStartDate());
}

// Exposer les fonctions de débogage en mode développement
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    window.debugEventDetails = debugEventDetails;
    window.partagerEvenement = partagerEvenement;
    window.ajouterAuCalendrier = ajouterAuCalendrier;
}