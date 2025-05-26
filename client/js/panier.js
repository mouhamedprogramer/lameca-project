// js/panier.js - Gestion complète du panier (adapté à votre API)

document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons "Ajouter au panier"
    const addToCartButtons = document.querySelectorAll('.add-to-cart');
    
    addToCartButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const oeuvreId = this.getAttribute('data-id');
            ajouterAuPanier(oeuvreId, this);
        });
    });
    
    // Gestion des quantités
    const qtyBtns = document.querySelectorAll('.qty-btn');
    const qtyInputs = document.querySelectorAll('.qty-input');
    
    qtyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const commandeId = this.getAttribute('data-id');
            const isPlus = this.classList.contains('plus');
            const input = document.querySelector(`input[data-id="${commandeId}"]`);
            
            let newQty = parseInt(input.value);
            if (isPlus) {
                newQty = Math.min(newQty + 1, 10);
            } else {
                newQty = Math.max(newQty - 1, 1);
            }
            
            input.value = newQty;
            mettreAJourQuantite(commandeId, newQty);
        });
    });
    
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            const commandeId = this.getAttribute('data-id');
            let newQty = Math.max(1, Math.min(parseInt(this.value) || 1, 10));
            this.value = newQty;
            mettreAJourQuantite(commandeId, newQty);
        });
    });
    
    // Gestion des suppressions
    const removeButtons = document.querySelectorAll('.btn-remove');
    removeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const commandeId = this.getAttribute('data-id');
            supprimerDuPanier(commandeId);
        });
    });
    
    // Gestion du checkout
    const checkoutBtn = document.querySelector('.checkout-btn');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            procederAuPaiement();
        });
    }
    
    // Gestion du code promo
    const applyPromoBtn = document.getElementById('apply-promo');
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', function() {
            const promoCode = document.getElementById('promo-code').value;
            appliquerCodePromo(promoCode);
        });
    }
});

// Fonction pour ajouter une œuvre au panier (adaptée à votre API JSON)
function ajouterAuPanier(oeuvreId, button) {
    // Animation du bouton
    const originalText = button.innerHTML;
    const originalClass = button.className;
    
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
    button.disabled = true;
    
    // Requête AJAX avec JSON (comme votre API l'attend)
    fetch('actions/ajouter-panier.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idOeuvre: parseInt(oeuvreId)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Succès
            button.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
            button.className = 'btn-cart success';
            
            // Mettre à jour le compteur du panier dans le header
            if (window.updateBadgeCount) {
                window.updateBadgeCount('badge-cart', data.cart_count);
            }
            
            // Afficher une notification
            showNotification('Œuvre ajoutée au panier avec succès !', 'success');
            
            // Remettre le bouton normal après 2 secondes
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = originalClass;
                button.disabled = false;
            }, 2000);
            
        } else {
            // Erreur
            button.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erreur';
            button.className = 'btn-cart error';
            
            showNotification(data.message || 'Erreur lors de l\'ajout au panier', 'error');
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.className = originalClass;
                button.disabled = false;
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        button.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Erreur';
        button.className = 'btn-cart error';
        
        showNotification('Erreur de connexion', 'error');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.className = originalClass;
            button.disabled = false;
        }, 2000);
    });
}

// Fonction pour mettre à jour la quantité (à adapter selon votre API)
function mettreAJourQuantite(commandeId, quantite) {
    fetch('actions/modifier-quantite-panier.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idCommande: parseInt(commandeId),
            quantite: parseInt(quantite)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Mettre à jour les prix affichés
            const cartItem = document.querySelector(`[data-id="${commandeId}"]`);
            const totalPriceElement = cartItem.querySelector('.total-price');
            const unitPrice = parseFloat(totalPriceElement.getAttribute('data-unit-price'));
            
            totalPriceElement.textContent = formaterPrix(unitPrice * quantite);
            
            // Mettre à jour le total général
            mettreAJourTotal();
            
            showNotification('Quantité mise à jour', 'success');
        } else {
            showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
            // Recharger la page en cas d'erreur
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// Fonction pour supprimer un article du panier
function supprimerDuPanier(commandeId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet article du panier ?')) {
        return;
    }
    
    fetch('actions/supprimer-panier.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idCommande: parseInt(commandeId)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Supprimer l'élément de la page
            const cartItem = document.querySelector(`[data-id="${commandeId}"]`);
            cartItem.style.opacity = '0';
            cartItem.style.transform = 'translateX(-100%)';
            
            setTimeout(() => {
                cartItem.remove();
                
                // Vérifier s'il reste des articles
                const remainingItems = document.querySelectorAll('.cart-item');
                if (remainingItems.length === 0) {
                    location.reload(); // Recharger pour afficher le panier vide
                } else {
                    mettreAJourTotal();
                }
            }, 300);
            
            // Mettre à jour le compteur du panier
            if (window.updateBadgeCount) {
                window.updateBadgeCount('badge-cart', data.cart_count);
            }
            
            showNotification('Article supprimé du panier', 'success');
        } else {
            showNotification(data.message || 'Erreur lors de la suppression', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
    });
}

// Fonction pour mettre à jour le total
function mettreAJourTotal() {
    let total = 0;
    const totalPrices = document.querySelectorAll('.total-price');
    
    totalPrices.forEach(element => {
        const priceText = element.textContent.replace(/[^\d,]/g, '').replace(',', '.');
        const price = parseFloat(priceText) || 0;
        total += price;
    });
    
    document.getElementById('subtotal').textContent = formaterPrix(total);
    document.getElementById('total').textContent = formaterPrix(total);
}

// Fonction pour formater les prix
function formaterPrix(prix) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(prix);
}

// Fonction pour procéder au paiement
function procederAuPaiement() {
    // Rediriger vers la page de paiement
    window.location.href = 'checkout.php';
}

// Fonction pour appliquer un code promo
function appliquerCodePromo(code) {
    if (!code.trim()) {
        showNotification('Veuillez entrer un code promo', 'warning');
        return;
    }
    
    fetch('actions/appliquer-promo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            code: code
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Code promo appliqué avec succès !', 'success');
            // Recharger la page pour afficher les nouveaux prix
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Code promo invalide', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'application du code promo', 'error');
    });
}

// Fonction pour afficher les notifications
function showNotification(message, type = 'info') {
    // Supprimer les notifications existantes
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    // Créer la nouvelle notification
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas ${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Styles inline pour la notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${getNotificationColor(type)};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        max-width: 400px;
        transform: translateX(100%);
        transition: transform 0.3s ease;
        font-weight: 500;
        font-family: 'Poppins', sans-serif;
    `;
    
    // Styles pour le contenu
    const notificationContent = notification.querySelector('.notification-content');
    notificationContent.style.cssText = `
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
    `;
    
    // Styles pour le bouton de fermeture
    const closeButton = notification.querySelector('.notification-close');
    closeButton.style.cssText = `
        background: none;
        border: none;
        color: rgba(255, 255, 255, 0.8);
        cursor: pointer;
        padding: 5px;
        margin-left: 10px;
        border-radius: 4px;
        transition: background-color 0.2s ease;
    `;
    
    closeButton.addEventListener('mouseenter', function() {
        this.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
    });
    
    closeButton.addEventListener('mouseleave', function() {
        this.style.backgroundColor = 'transparent';
    });
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Suppression automatique après 5 secondes
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}

function getNotificationIcon(type) {
    switch(type) {
        case 'success': return 'fa-check-circle';
        case 'error': return 'fa-exclamation-circle';
        case 'warning': return 'fa-exclamation-triangle';
        default: return 'fa-info-circle';
    }
}

function getNotificationColor(type) {
    switch(type) {
        case 'success': return '#10b981';
        case 'error': return '#ef4444';
        case 'warning': return '#f59e0b';
        default: return '#3b82f6';
    }
}

// Fonction utilitaire pour déboguer
function debugPanier() {
    console.log('Debug: Boutons ajouter au panier trouvés:', document.querySelectorAll('.add-to-cart').length);
    console.log('Debug: Session utilisateur:', {
        connecte: typeof window.userConnected !== 'undefined' ? window.userConnected : 'Non défini'
    });
}