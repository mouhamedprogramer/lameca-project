// JavaScript pour la page des œuvres
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'ajout au panier
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function() {
            const oeuvreId = this.dataset.id;
            ajouterAuPanier(oeuvreId, this);
        });
    });

    // Gestion de l'ajout aux favoris
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function() {
            const oeuvreId = this.dataset.id;
            ajouterAuxFavoris(oeuvreId, this);
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

    const cards = document.querySelectorAll('.oeuvre-card');
    cards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(card);
    });

    // Filtrage en temps réel (optionnel)
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Ici on pourrait ajouter une recherche AJAX en temps réel
                // filtrerOeuvres(this.value);
            }, 500);
        });
    }
});

// Fonction pour ajouter au panier
function ajouterAuPanier(oeuvreId, button) {
    // Désactiver le bouton temporairement
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';

    fetch('actions/ajouter-panier.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idOeuvre: oeuvreId,
            action: 'ajouter'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Succès
            button.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
            button.style.background = '#27ae60';
            
            // Mettre à jour le compteur du panier
            updateCartCount();
            
            // Afficher une notification
            showNotification('Œuvre ajoutée au panier !', 'success');
            
            // Remettre le bouton normal après 2 secondes
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
                button.style.background = '';
            }, 2000);
        } else {
            // Erreur
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
            showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
        showNotification('Erreur de connexion', 'error');
    });
}

// Fonction pour ajouter aux favoris
function ajouterAuxFavoris(oeuvreId, button) {
    const icon = button.querySelector('i');
    const isActive = button.classList.contains('active');

    fetch('actions/ajouter-favoris.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idOeuvre: oeuvreId,
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
                showNotification('Retiré des favoris', 'info');
            } else {
                // Ajouter aux favoris
                button.classList.add('active');
                icon.classList.remove('far');
                icon.classList.add('fas');
                showNotification('Ajouté aux favoris !', 'success');
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

// Fonction pour mettre à jour le compteur du panier
function updateCartCount() {
    fetch('actions/get-cart-count.php')
        .then(response => response.json())
        .then(data => {
            const cartBadge = document.getElementById('cart-count');
            if (cartBadge && data.count !== undefined) {
                cartBadge.textContent = data.count;
                cartBadge.style.display = data.count > 0 ? 'inline' : 'none';
            }
        })
        .catch(error => console.error('Erreur lors de la mise à jour du compteur:', error));
}

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
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
                margin-left: 10px;
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

// Fonction pour filtrer les œuvres (recherche AJAX optionnelle)
function filtrerOeuvres(searchTerm) {
    const cards = document.querySelectorAll('.oeuvre-card');
    
    if (!searchTerm.trim()) {
        cards.forEach(card => {
            card.style.display = 'block';
        });
        return;
    }

    cards.forEach(card => {
        const title = card.querySelector('.oeuvre-title').textContent.toLowerCase();
        const artisan = card.querySelector('.oeuvre-artisan').textContent.toLowerCase();
        const specialite = card.querySelector('.oeuvre-specialite').textContent.toLowerCase();
        
        const searchLower = searchTerm.toLowerCase();
        
        if (title.includes(searchLower) || artisan.includes(searchLower) || specialite.includes(searchLower)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

// Gestion du lazy loading des images
function initLazyLoading() {
    const images = document.querySelectorAll('.oeuvre-image img');
    
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src || img.src;
                img.classList.add('loaded');
                observer.unobserve(img);
            }
        });
    });

    images.forEach(img => imageObserver.observe(img));
}