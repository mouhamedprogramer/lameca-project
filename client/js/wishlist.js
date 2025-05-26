// js/wishlist.js - Gestion de la liste de souhaits (adapté à votre API)

document.addEventListener('DOMContentLoaded', function() {
    initWishlistFunctionality();
});

function initWishlistFunctionality() {
    // Gestion des boutons wishlist dans toutes les pages
    const wishlistButtons = document.querySelectorAll('.add-to-wishlist, .btn-add-wishlist, .wishlist-btn');
    
    wishlistButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const oeuvreId = this.getAttribute('data-id');
            const isCurrentlyInWishlist = this.classList.contains('active') || this.classList.contains('in-wishlist');
            
            toggleWishlist(oeuvreId, this, !isCurrentlyInWishlist);
        });
    });
    
    // Gestion des boutons de suppression dans la page wishlist
    const removeButtons = document.querySelectorAll('.btn-remove-wishlist');
    removeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const oeuvreId = this.getAttribute('data-id');
            removeFromWishlist(oeuvreId, this);
        });
    });
}

// Fonction principale pour ajouter/retirer des favoris
function toggleWishlist(oeuvreId, button, shouldAdd = true) {
    if (!oeuvreId) {
        showNotification('Erreur: ID œuvre manquant', 'error');
        return;
    }

    // Animation du bouton
    const originalContent = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    // Déterminer l'action
    const action = shouldAdd ? 'add' : 'remove';

    // Requête AJAX avec le format JSON attendu par votre API
    fetch('actions/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idOeuvre: parseInt(oeuvreId),
            action: action
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Succès - mettre à jour l'interface
            updateWishlistButton(button, data.action === 'add');
            
            // Mettre à jour le compteur dans le header si disponible
            if (window.updateBadgeCount) {
                // Récupérer le nouveau nombre de favoris
                updateWishlistCount();
            }
            
            const message = data.action === 'add' ? 
                'Œuvre ajoutée à vos favoris !' : 
                'Œuvre retirée de vos favoris';
            
            showNotification(message, 'success');
            
        } else {
            // Erreur du serveur
            showNotification(data.message || 'Erreur lors de la modification des favoris', 'error');
            button.innerHTML = originalContent;
        }
    })
    .catch(error => {
        console.error('Erreur wishlist:', error);
        showNotification('Erreur de connexion. Veuillez réessayer.', 'error');
        button.innerHTML = originalContent;
    })
    .finally(() => {
        button.disabled = false;
    });
}

// Fonction spécifique pour retirer des favoris (page wishlist)
function removeFromWishlist(oeuvreId, button) {
    if (!confirm('Êtes-vous sûr de vouloir retirer cette œuvre de vos favoris ?')) {
        return;
    }

    const wishlistItem = button.closest('.wishlist-item');
    
    // Animation du bouton
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('actions/wishlist.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            idOeuvre: parseInt(oeuvreId),
            action: 'remove'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Animation de suppression
            if (wishlistItem) {
                wishlistItem.style.transform = 'scale(0.8)';
                wishlistItem.style.opacity = '0';
                
                setTimeout(() => {
                    wishlistItem.remove();
                    updateWishlistStats();
                    
                    // Vérifier s'il reste des items
                    const remainingItems = document.querySelectorAll('.wishlist-item');
                    if (remainingItems.length === 0) {
                        location.reload(); // Recharger pour afficher l'état vide
                    }
                }, 300);
            }
            
            showNotification('Œuvre retirée de vos favoris', 'success');
            updateWishlistCount();
            
        } else {
            showNotification(data.message || 'Erreur lors de la suppression', 'error');
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-heart"></i>';
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur de connexion', 'error');
        button.disabled = false;
        button.innerHTML = '<i class="fas fa-heart"></i>';
    });
}

// Mettre à jour l'apparence du bouton wishlist
function updateWishlistButton(button, isInWishlist) {
    if (isInWishlist) {
        // L'œuvre est maintenant dans les favoris
        button.classList.add('active', 'in-wishlist');
        button.innerHTML = '<i class="fas fa-heart"></i><span>Dans les favoris</span>';
        button.title = 'Retirer des favoris';
        
        // Animation de cœur
        const heart = button.querySelector('i');
        if (heart) {
            heart.style.animation = 'heartBeat 0.6s ease';
            setTimeout(() => {
                heart.style.animation = '';
            }, 600);
        }
        
    } else {
        // L'œuvre n'est plus dans les favoris
        button.classList.remove('active', 'in-wishlist');
        button.innerHTML = '<i class="far fa-heart"></i><span>Favoris</span>';
        button.title = 'Ajouter aux favoris';
    }
}

// Mettre à jour le compteur de wishlist dans le header
function updateWishlistCount() {
    fetch('actions/get-wishlist-count.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && window.updateBadgeCount) {
                window.updateBadgeCount('badge-wishlist', data.count);
            }
        })
        .catch(error => {
            console.error('Erreur lors de la récupération du compteur:', error);
        });
}

// Mettre à jour les statistiques sur la page wishlist
function updateWishlistStats() {
    const remainingItems = document.querySelectorAll('.wishlist-item');
    const total = remainingItems.length;
    
    // Calculer la valeur totale
    let valeurTotale = 0;
    remainingItems.forEach(item => {
        const price = parseFloat(item.dataset.price) || 0;
        valeurTotale += price;
    });
    
    const prixMoyen = total > 0 ? valeurTotale / total : 0;
    
    // Mettre à jour l'affichage des stats
    const statNumber = document.querySelector('.stat-item:first-child .stat-number');
    const statLabel = document.querySelector('.stat-item:first-child .stat-label');
    const valeurTotaleEl = document.querySelector('.stat-item:nth-child(2) .stat-number');
    const prixMoyenEl = document.querySelector('.stat-item:nth-child(3) .stat-number');
    
    if (statNumber) {
        statNumber.textContent = total;
        statLabel.textContent = `Œuvre${total > 1 ? 's' : ''} favorite${total > 1 ? 's' : ''}`;
    }
    
    if (valeurTotaleEl) {
        valeurTotaleEl.textContent = Math.round(valeurTotale).toLocaleString('fr-FR') + '€';
    }
    
    if (prixMoyenEl) {
        prixMoyenEl.textContent = Math.round(prixMoyen).toLocaleString('fr-FR') + '€';
    }
}

// Fonction pour la page wishlist - gestion des vues et filtres
function initWishlistPage() {
    // Gestion des vues (grille/liste)
    const viewButtons = document.querySelectorAll('.view-btn');
    const wishlistGrid = document.getElementById('wishlistGrid');
    
    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const view = this.dataset.view;
            wishlistGrid.className = view === 'list' ? 'wishlist-list' : 'wishlist-grid';
        });
    });

    // Gestion du tri
    const sortSelect = document.getElementById('sortBy');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            sortWishlistItems(this.value);
        });
    }

    // Gestion de la sélection multiple
    initBulkActions();
    
    // Gestion des boutons d'ajout au panier
    const addToCartButtons = document.querySelectorAll('.btn-add-cart');
    addToCartButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const oeuvreId = this.getAttribute('data-id');
            addToCartFromWishlist(oeuvreId, this);
        });
    });

    // Gestion des boutons de partage
    const shareButtons = document.querySelectorAll('.btn-share');
    shareButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const oeuvreId = this.getAttribute('data-id');
            shareOeuvre(oeuvreId);
        });
    });
}

// Trier les éléments de la wishlist
function sortWishlistItems(sortBy) {
    const container = document.getElementById('wishlistGrid');
    const items = Array.from(container.querySelectorAll('.wishlist-item'));
    
    items.sort((a, b) => {
        switch(sortBy) {
            case 'price-asc':
                return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            case 'price-desc':
                return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            case 'rating':
                return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating);
            case 'name':
                return a.dataset.name.localeCompare(b.dataset.name);
            case 'recent':
            default:
                return new Date(b.dataset.date) - new Date(a.dataset.date);
        }
    });
    
    // Réorganiser les éléments
    items.forEach(item => container.appendChild(item));
}

// Gestion des actions en lot
function initBulkActions() {
    const selectAllBtn = document.getElementById('selectAll');
    const addSelectedBtn = document.getElementById('addSelectedToCart');
    const removeSelectedBtn = document.getElementById('removeSelected');
    const checkboxes = document.querySelectorAll('.oeuvre-checkbox');
    
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const allChecked = Array.from(checkboxes).every(cb => cb.checked);
            checkboxes.forEach(cb => cb.checked = !allChecked);
            updateBulkActionButtons();
        });
    }
    
    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActionButtons);
    });
    
    if (addSelectedBtn) {
        addSelectedBtn.addEventListener('click', function() {
            const selected = getSelectedItems();
            if (selected.length > 0) {
                addMultipleToCart(selected);
            }
        });
    }
    
    if (removeSelectedBtn) {
        removeSelectedBtn.addEventListener('click', function() {
            const selected = getSelectedItems();
            if (selected.length > 0) {
                removeMultipleFromWishlist(selected);
            }
        });
    }
}

function updateBulkActionButtons() {
    const checkboxes = document.querySelectorAll('.oeuvre-checkbox');
    const checkedCount = Array.from(checkboxes).filter(cb => cb.checked).length;
    const addSelectedBtn = document.getElementById('addSelectedToCart');
    const removeSelectedBtn = document.getElementById('removeSelected');
    
    if (addSelectedBtn && removeSelectedBtn) {
        if (checkedCount > 0) {
            addSelectedBtn.style.display = 'block';
            removeSelectedBtn.style.display = 'block';
            addSelectedBtn.innerHTML = `<i class="fas fa-shopping-cart"></i> Ajouter (${checkedCount})`;
            removeSelectedBtn.innerHTML = `<i class="fas fa-trash"></i> Retirer (${checkedCount})`;
        } else {
            addSelectedBtn.style.display = 'none';
            removeSelectedBtn.style.display = 'none';
        }
    }
}

function getSelectedItems() {
    const checkboxes = document.querySelectorAll('.oeuvre-checkbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

// Ajouter au panier depuis la wishlist
function addToCartFromWishlist(oeuvreId, button) {
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
    button.disabled = true;
    
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
            button.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
            button.classList.add('success');
            
            showNotification('Œuvre ajoutée au panier !', 'success');
            
            if (window.updateBadgeCount) {
                window.updateBadgeCount('badge-cart', data.cart_count);
            }
            
            setTimeout(() => {
                button.innerHTML = originalText;
                button.classList.remove('success');
                button.disabled = false;
            }, 2000);
        } else {
            showNotification(data.message, 'error');
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'ajout au panier', 'error');
        button.innerHTML = originalText;
        button.disabled = false;
    });
}

// Partager une œuvre
function shareOeuvre(oeuvreId) {
    const url = `${window.location.origin}/oeuvre-details.php?id=${oeuvreId}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Œuvre d\'art - Artisano',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Lien copié dans le presse-papiers !', 'success');
        }).catch(() => {
            showNotification('Impossible de copier le lien', 'error');
        });
    }
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
    
    // Styles inline
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
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Suppression automatique
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

// Animation CSS pour le cœur
const style = document.createElement('style');
style.textContent = `
    @keyframes heartBeat {
        0%, 100% { transform: scale(1); }
        25% { transform: scale(1.3); }
        50% { transform: scale(1.1); }
        75% { transform: scale(1.2); }
    }
`;
document.head.appendChild(style);