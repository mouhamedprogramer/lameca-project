// JavaScript pour la page du panier
document.addEventListener('DOMContentLoaded', function() {
    // Gestion des boutons de quantité
    const qtyButtons = document.querySelectorAll('.qty-btn');
    const qtyInputs = document.querySelectorAll('.qty-input');
    const removeButtons = document.querySelectorAll('.btn-remove');
    const checkoutBtn = document.querySelector('.checkout-btn');
    const promoBtn = document.getElementById('apply-promo');

    // Événements pour les boutons de quantité
    qtyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const commandeId = this.dataset.id;
            const isPlus = this.classList.contains('plus');
            const input = document.querySelector(`.qty-input[data-id="${commandeId}"]`);
            let currentQty = parseInt(input.value);

            if (isPlus) {
                if (currentQty < 10) {
                    currentQty++;
                    updateQuantity(commandeId, currentQty, input);
                }
            } else {
                if (currentQty > 1) {
                    currentQty--;
                    updateQuantity(commandeId, currentQty, input);
                }
            }
        });
    });

    // Événements pour les inputs de quantité
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            const commandeId = this.dataset.id;
            let qty = parseInt(this.value);
            
            if (qty < 1) qty = 1;
            if (qty > 10) qty = 10;
            
            this.value = qty;
            updateQuantity(commandeId, qty, this);
        });
    });

    // Événements pour les boutons de suppression
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const commandeId = this.dataset.id;
            removeFromCart(commandeId);
        });
    });

    // Événement pour le bouton de checkout
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function() {
            window.location.href = 'checkout.php';
        });
    }

    // Événement pour le code promo
    if (promoBtn) {
        promoBtn.addEventListener('click', function() {
            const promoCode = document.getElementById('promo-code').value;
            applyPromoCode(promoCode);
        });
    }
});

// Fonction pour mettre à jour la quantité
function updateQuantity(commandeId, newQty, inputElement) {
    const cartItem = inputElement.closest('.cart-item');
    cartItem.classList.add('updating');

    fetch('actions/update-cart-quantity.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idCommande: commandeId,
            nouvelle_quantite: newQty
        })
    })
    .then(response => response.json())
    .then(data => {
        cartItem.classList.remove('updating');
        
        if (data.success) {
            // Mettre à jour le prix total de l'article
            const totalPriceElement = cartItem.querySelector('.total-price');
            const unitPrice = parseFloat(totalPriceElement.dataset.unitPrice);
            const newTotalPrice = unitPrice * newQty;
            
            totalPriceElement.textContent = formatPrice(newTotalPrice);
            
            // Mettre à jour les totaux
            updateCartTotals();
            
            // Mettre à jour le compteur du panier dans le header
            updateCartCount();
            
        } else {
            showNotification(data.message || 'Erreur lors de la mise à jour', 'error');
            // Remettre la valeur précédente
            inputElement.value = inputElement.defaultValue;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        cartItem.classList.remove('updating');
        showNotification('Erreur de connexion', 'error');
        inputElement.value = inputElement.defaultValue;
    });
}

// Fonction pour supprimer un article du panier
function removeFromCart(commandeId) {
    if (!confirm('Êtes-vous sûr de vouloir supprimer cet article de votre panier ?')) {
        return;
    }

    const cartItem = document.querySelector(`.cart-item[data-id="${commandeId}"]`);
    cartItem.classList.add('updating');

    fetch('actions/remove-from-cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idCommande: commandeId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animation de suppression
            cartItem.style.transform = 'translateX(-100%)';
            cartItem.style.opacity = '0';
            
            setTimeout(() => {
                cartItem.remove();
                
                // Vérifier s'il reste des articles
                const remainingItems = document.querySelectorAll('.cart-item');
                if (remainingItems.length === 0) {
                    location.reload(); // Recharger pour afficher le panier vide
                } else {
                    updateCartTotals();
                    updateCartCount();
                }
            }, 300);
            
            showNotification('Article supprimé du panier', 'info');
        } else {
            cartItem.classList.remove('updating');
            showNotification(data.message || 'Erreur lors de la suppression', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        cartItem.classList.remove('updating');
        showNotification('Erreur de connexion', 'error');
    });
}

// Fonction pour appliquer un code promo
function applyPromoCode(code) {
    if (!code.trim()) {
        showNotification('Veuillez entrer un code promo', 'error');
        return;
    }

    const promoBtn = document.getElementById('apply-promo');
    const originalText = promoBtn.textContent;
    promoBtn.textContent = 'Vérification...';
    promoBtn.disabled = true;

    fetch('actions/apply-promo.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            code_promo: code
        })
    })
    .then(response => response.json())
    .then(data => {
        promoBtn.textContent = originalText;
        promoBtn.disabled = false;

        if (data.success) {
            showNotification('Code promo appliqué !', 'success');
            updateCartTotals();
        } else {
            showNotification(data.message || 'Code promo invalide', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        promoBtn.textContent = originalText;
        promoBtn.disabled = false;
        showNotification('Erreur de connexion', 'error');
    });
}

// Fonction pour mettre à jour les totaux
function updateCartTotals() {
    let subtotal = 0;
    
    document.querySelectorAll('.total-price').forEach(element => {
        const price = parseFloat(element.textContent.replace(/[^\d,]/g, '').replace(',', '.'));
        if (!isNaN(price)) {
            subtotal += price;
        }
    });

    const subtotalElement = document.getElementById('subtotal');
    const totalElement = document.getElementById('total');

    if (subtotalElement) {
        subtotalElement.textContent = formatPrice(subtotal);
    }
    
    if (totalElement) {
        totalElement.textContent = formatPrice(subtotal); // Sans frais de livraison pour l'instant
    }
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

// Fonction utilitaire pour formater les prix
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR'
    }).format(price);
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
                min-width: 300px;
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

// Sauvegarde automatique du panier (optionnel)
function autoSaveCart() {
    // Cette fonction pourrait être utilisée pour sauvegarder le panier automatiquement
    // Utile si on veut implémenter une sauvegarde côté client
}

// Gestion de la navigation retour
window.addEventListener('popstate', function(event) {
    // Rafraîchir le compteur du panier si l'utilisateur revient sur la page
    updateCartCount();
});