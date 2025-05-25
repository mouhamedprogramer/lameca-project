/**
 * JavaScript pour la page des œuvres - VERSION CORRIGÉE
 * Gestion de l'ajout aux favoris et au panier
 */

class OeuvresManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
        this.checkWishlistStatus();
        this.initAnimations();
    }

    bindEvents() {
        // Gestion des boutons wishlist
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-wishlist, .add-to-wishlist *')) {
                const button = e.target.closest('.add-to-wishlist');
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleWishlist(button);
                }
            }

            // Gestion des boutons panier
            if (e.target.matches('.add-to-cart, .add-to-cart *')) {
                const button = e.target.closest('.add-to-cart');
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleAddToCart(button);
                }
            }
        });
    }

    async checkWishlistStatus() {
        // Vérifier quelles œuvres sont déjà dans la wishlist
        const wishlistButtons = document.querySelectorAll('.add-to-wishlist');
        
        for (const button of wishlistButtons) {
            const oeuvreId = button.dataset.id;
            if (oeuvreId) {
                try {
                    const response = await fetch(`actions/check-wishlist.php?oeuvreId=${oeuvreId}`);
                    const data = await response.json();
                    
                    if (data.success && data.is_in_wishlist) {
                        this.updateWishlistButton(button, true);
                    }
                } catch (error) {
                    console.log('Impossible de vérifier le statut de la wishlist pour l\'œuvre', oeuvreId);
                }
            }
        }
    }

    async handleWishlist(button) {
        const oeuvreId = button.dataset.id;
        const isInWishlist = button.classList.contains('in-wishlist');
        const action = isInWishlist ? 'remove' : 'add';

        // Debug
        console.log('Wishlist action:', { oeuvreId, action, isInWishlist });

        // Animation immédiate pour feedback rapide
        button.style.transform = 'scale(0.9)';
        setTimeout(() => {
            button.style.transform = 'scale(1)';
        }, 150);

        try {
            const response = await fetch('actions/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    idOeuvre: parseInt(oeuvreId),
                    action: action
                })
            });

            // Debug
            console.log('Response status:', response.status);
            
            const data = await response.json();
            console.log('Response data:', data);

            if (data.success) {
                this.updateWishlistButton(button, data.is_in_wishlist);
                this.showNotification(data.message, 'success');
                
                // Mettre à jour le badge wishlist dans la navbar
                this.updateWishlistBadge(action);
                
            } else {
                this.showNotification(data.message, 'error');
                
                // Rediriger vers la page de connexion si nécessaire
                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                }
            }
        } catch (error) {
            console.error('Erreur lors de la gestion de la wishlist:', error);
            this.showNotification('Une erreur est survenue. Veuillez réessayer.', 'error');
        }
    }

    updateWishlistButton(button, isInWishlist) {
        const icon = button.querySelector('i');
        
        if (isInWishlist) {
            button.classList.add('in-wishlist');
            icon.classList.remove('far');
            icon.classList.add('fas');
            icon.style.color = '#e74c3c';
            button.title = 'Retirer des favoris';
        } else {
            button.classList.remove('in-wishlist');
            icon.classList.remove('fas');
            icon.classList.add('far');
            icon.style.color = '';
            button.title = 'Ajouter aux favoris';
        }

        // Animation de cœur
        if (isInWishlist) {
            this.animateHeart(button);
        }
    }

    animateHeart(button) {
        // Créer un effet de particules de cœur
        const rect = button.getBoundingClientRect();
        const heart = document.createElement('div');
        heart.innerHTML = '❤️';
        heart.style.cssText = `
            position: fixed;
            left: ${rect.left + rect.width/2}px;
            top: ${rect.top + rect.height/2}px;
            font-size: 20px;
            pointer-events: none;
            z-index: 1000;
            animation: heartFloat 1s ease-out forwards;
        `;

        document.body.appendChild(heart);

        // Supprimer l'élément après l'animation
        setTimeout(() => {
            heart.remove();
        }, 1000);
    }

    async handleAddToCart(button) {
        const oeuvreId = button.dataset.id;
        
        try {
            button.disabled = true;
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
            
            // Simulation d'ajout au panier (remplacez par votre vraie API)
            await this.addToCartAPI(oeuvreId);
            
            button.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
            button.classList.add('success');
            
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalContent;
                button.classList.remove('success');
            }, 2000);
            
            this.showNotification('Œuvre ajoutée au panier !', 'success');
            this.updateCartBadge();
            
        } catch (error) {
            console.error('Erreur:', error);
            this.showNotification('Erreur lors de l\'ajout au panier', 'error');
            
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
        }
    }

    updateWishlistBadge(action) {
        // Chercher un badge wishlist dans la navbar
        const wishlistBadge = document.querySelector('.icon-link[href*="wishlist"] .badge, .badge-wishlist');
        if (wishlistBadge) {
            let currentCount = parseInt(wishlistBadge.textContent) || 0;
            
            if (action === 'add') {
                currentCount++;
            } else if (action === 'remove' && currentCount > 0) {
                currentCount--;
            }
            
            wishlistBadge.textContent = currentCount;
            
            if (currentCount === 0) {
                wishlistBadge.style.display = 'none';
            } else {
                wishlistBadge.style.display = 'flex';
                // Animation du badge
                wishlistBadge.style.transform = 'scale(1.3)';
                setTimeout(() => {
                    wishlistBadge.style.transform = 'scale(1)';
                }, 200);
            }
        }
    }

    updateCartBadge() {
        const cartBadge = document.getElementById('cart-count');
        if (cartBadge) {
            let currentCount = parseInt(cartBadge.textContent) || 0;
            cartBadge.textContent = currentCount + 1;
            
            // Animation du badge
            cartBadge.style.transform = 'scale(1.3)';
            setTimeout(() => {
                cartBadge.style.transform = 'scale(1)';
            }, 200);
        }
    }

    async addToCartAPI(oeuvreId) {
        // Simulation d'un appel API pour l'ajout au panier
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (Math.random() > 0.1) { // 90% de succès
                    resolve({ success: true });
                } else {
                    reject(new Error('Erreur réseau'));
                }
            }, 1000);
        });
    }

    initAnimations() {
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
    }

    showNotification(message, type = 'info') {
        // Créer une notification
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'error' ? 'exclamation-circle' : 'info-circle';
        
        notification.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;

        // Styles de la notification
        notification.style.cssText = `
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
            backdrop-filter: blur(10px);
            max-width: 350px;
            font-family: 'Poppins', sans-serif;
            background: ${type === 'success' ? 'linear-gradient(135deg, #27ae60, #229954)' : 
                        type === 'error' ? 'linear-gradient(135deg, #e74c3c, #c0392b)' : 
                        'linear-gradient(135deg, #3498db, #2980b9)'};
        `;

        document.body.appendChild(notification);
        
        // Animation d'apparition
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Suppression automatique
        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.transform = 'translateX(400px)';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }
        }, 4000);

        // Permettre la fermeture au clic
        notification.addEventListener('click', () => {
            notification.style.transform = 'translateX(400px)';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        });
    }
}

// CSS pour les animations
const oeuvresStyles = `
    @keyframes heartFloat {
        0% {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        50% {
            transform: translateY(-20px) scale(1.2);
            opacity: 0.8;
        }
        100% {
            transform: translateY(-40px) scale(0.8);
            opacity: 0;
        }
    }

    .add-to-wishlist {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .add-to-wishlist.in-wishlist {
        background: rgba(231, 76, 60, 0.1) !important;
        border-color: rgba(231, 76, 60, 0.3) !important;
    }

    .add-to-wishlist:hover {
        transform: scale(1.05);
    }

    .add-to-cart.success {
        background: linear-gradient(135deg, #27ae60, #229954) !important;
    }

    .wishlist-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid rgba(231, 76, 60, 0.3);
        color: #e74c3c;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(5px);
        z-index: 2;
    }

    .wishlist-btn:hover {
        background: rgba(255, 255, 255, 1);
        transform: scale(1.1);
        box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
    }

    .wishlist-btn.in-wishlist {
        background: rgba(231, 76, 60, 0.9) !important;
        color: white !important;
        border-color: #e74c3c !important;
    }

    .oeuvre-card:hover .wishlist-btn {
        opacity: 1;
        transform: translateY(0);
    }

    .btn-wishlist.in-wishlist {
        background: rgba(231, 76, 60, 0.1) !important;
        border-color: rgba(231, 76, 60, 0.3) !important;
        color: #e74c3c !important;
    }
`;

// Ajouter les styles s'ils n'existent pas déjà
if (!document.getElementById('oeuvres-styles')) {
    const styleElement = document.createElement('style');
    styleElement.id = 'oeuvres-styles';
    styleElement.textContent = oeuvresStyles;
    document.head.appendChild(styleElement);
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initialisation OeuvresManager...');
    window.oeuvresManager = new OeuvresManager();
});

// Export pour utilisation globale
window.OeuvresManager = OeuvresManager;