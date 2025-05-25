/**
 * JavaScript pour la gestion de la liste de souhaits (wishlist)
 */

class WishlistManager {
    constructor() {
        this.currentView = 'grid';
        this.currentSort = 'recent';
        this.selectedItems = new Set();
        this.init();
    }

    init() {
        this.bindEvents();
        this.initializeView();
    }

    bindEvents() {
        // Gestion des vues
        document.addEventListener('click', (e) => {
            // Boutons de vue
            if (e.target.matches('.view-btn, .view-btn *')) {
                const button = e.target.closest('.view-btn');
                if (button) {
                    this.handleViewChange(button);
                }
            }

            // Retirer des favoris
            if (e.target.matches('.btn-remove-wishlist, .btn-remove-wishlist *')) {
                const button = e.target.closest('.btn-remove-wishlist');
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleRemoveFromWishlist(button);
                }
            }

            // Ajouter au panier
            if (e.target.matches('.btn-add-cart, .btn-add-cart *')) {
                const button = e.target.closest('.btn-add-cart');
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleAddToCart(button);
                }
            }

            // Partager
            if (e.target.matches('.btn-share, .btn-share *')) {
                const button = e.target.closest('.btn-share');
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleShare(button);
                }
            }

            // Ajouter aux favoris (recommandations)
            if (e.target.matches('.btn-add-wishlist, .btn-add-wishlist *')) {
                const button = e.target.closest('.btn-add-wishlist');
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleAddToWishlist(button);
                }
            }

            // Vue rapide
            if (e.target.matches('.btn-quick-view, .btn-quick-view *')) {
                const button = e.target.closest('.btn-quick-view');
                if (button) {
                    e.preventDefault();
                    e.stopPropagation();
                    this.handleQuickView(button);
                }
            }
        });

        // Gestion du tri
        const sortSelect = document.getElementById('sortBy');
        if (sortSelect) {
            sortSelect.addEventListener('change', (e) => {
                this.handleSortChange(e.target.value);
            });
        }

        // Gestion des checkboxes
        document.addEventListener('change', (e) => {
            if (e.target.matches('.oeuvre-checkbox')) {
                this.handleCheckboxChange(e.target);
            }
        });

        // Sélectionner tout
        const selectAllBtn = document.getElementById('selectAll');
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => {
                this.handleSelectAll();
            });
        }

        // Actions groupées
        const addSelectedBtn = document.getElementById('addSelectedToCart');
        const removeSelectedBtn = document.getElementById('removeSelected');
        
        if (addSelectedBtn) {
            addSelectedBtn.addEventListener('click', () => {
                this.handleAddSelectedToCart();
            });
        }
        
        if (removeSelectedBtn) {
            removeSelectedBtn.addEventListener('click', () => {
                this.handleRemoveSelected();
            });
        }
    }

    initializeView() {
        // Activer la vue par défaut
        const defaultViewBtn = document.querySelector('.view-btn.active');
        if (defaultViewBtn) {
            this.currentView = defaultViewBtn.dataset.view;
        }
        
        this.applyView();
    }

    handleViewChange(button) {
        // Retirer la classe active des autres boutons
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Activer le bouton cliqué
        button.classList.add('active');
        
        // Changer la vue
        this.currentView = button.dataset.view;
        this.applyView();
    }

    applyView() {
        const grid = document.getElementById('wishlistGrid');
        if (!grid) return;

        if (this.currentView === 'list') {
            grid.classList.add('list-view');
        } else {
            grid.classList.remove('list-view');
        }

        // Animation pour le changement de vue
        this.animateViewChange();
    }

    animateViewChange() {
        const items = document.querySelectorAll('.wishlist-item');
        items.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 50);
        });
    }

    handleSortChange(sortValue) {
        this.currentSort = sortValue;
        this.sortItems();
    }

    sortItems() {
        const grid = document.getElementById('wishlistGrid');
        const items = Array.from(grid.children);
        
        items.sort((a, b) => {
            switch (this.currentSort) {
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

        // Réorganiser les éléments avec animation
        items.forEach((item, index) => {
            item.style.order = index;
            item.style.animation = `fadeInUp 0.4s ease-out ${index * 0.05}s both`;
        });

        // Ajouter les éléments triés au DOM
        items.forEach(item => grid.appendChild(item));
    }

    handleCheckboxChange(checkbox) {
        const itemId = checkbox.value;
        
        if (checkbox.checked) {
            this.selectedItems.add(itemId);
        } else {
            this.selectedItems.delete(itemId);
        }
        
        this.updateBulkActions();
    }

    handleSelectAll() {
        const checkboxes = document.querySelectorAll('.oeuvre-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
            
            if (cb.checked) {
                this.selectedItems.add(cb.value);
            } else {
                this.selectedItems.delete(cb.value);
            }
        });
        
        this.updateBulkActions();
    }

    updateBulkActions() {
        const selectedCount = this.selectedItems.size;
        const selectAllBtn = document.getElementById('selectAll');
        const addSelectedBtn = document.getElementById('addSelectedToCart');
        const removeSelectedBtn = document.getElementById('removeSelected');
        
        if (selectedCount > 0) {
            if (addSelectedBtn) {
                addSelectedBtn.style.display = 'flex';
                addSelectedBtn.innerHTML = `<i class="fas fa-shopping-cart"></i> Ajouter au panier (${selectedCount})`;
            }
            
            if (removeSelectedBtn) {
                removeSelectedBtn.style.display = 'flex';
                removeSelectedBtn.innerHTML = `<i class="fas fa-trash"></i> Retirer (${selectedCount})`;
            }
            
            if (selectAllBtn) {
                selectAllBtn.innerHTML = '<i class="far fa-square"></i> Désélectionner tout';
            }
        } else {
            if (addSelectedBtn) addSelectedBtn.style.display = 'none';
            if (removeSelectedBtn) removeSelectedBtn.style.display = 'none';
            
            if (selectAllBtn) {
                selectAllBtn.innerHTML = '<i class="far fa-check-square"></i> Tout sélectionner';
            }
        }
    }

    async handleRemoveFromWishlist(button) {
        const itemId = button.dataset.id;
        const item = button.closest('.wishlist-item');
        
        if (!confirm('Êtes-vous sûr de vouloir retirer cette œuvre de vos favoris ?')) {
            return;
        }

        try {
            // Animation de suppression
            item.style.transform = 'scale(0.8)';
            item.style.opacity = '0.5';
            
            // Simulation de l'API call
            await this.removeFromWishlistAPI(itemId);
            
            // Supprimer l'élément avec animation
            setTimeout(() => {
                item.style.height = item.offsetHeight + 'px';
                item.style.overflow = 'hidden';
                
                setTimeout(() => {
                    item.style.height = '0';
                    item.style.margin = '0';
                    item.style.padding = '0';
                    
                    setTimeout(() => {
                        item.remove();
                        this.updateStats();
                        this.checkEmptyState();
                    }, 300);
                }, 100);
            }, 300);
            
            this.showNotification('Œuvre retirée de vos favoris', 'success');
            
        } catch (error) {
            console.error('Erreur:', error);
            this.showNotification('Erreur lors de la suppression', 'error');
            
            // Restaurer l'apparence
            item.style.transform = '';
            item.style.opacity = '';
        }
    }

    async handleAddToCart(button) {
        const itemId = button.dataset.id;
        
        try {
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Ajout...';
            
            await this.addToCartAPI(itemId);
            
            button.innerHTML = '<i class="fas fa-check"></i> Ajouté !';
            button.classList.add('success');
            
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-shopping-cart"></i> Ajouter au panier';
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

    handleShare(button) {
        const itemId = button.dataset.id;
        const item = button.closest('.wishlist-item');
        const title = item.querySelector('.item-title a').textContent;
        const url = `${window.location.origin}/oeuvre-details.php?id=${itemId}`;
        
        if (navigator.share) {
            navigator.share({
                title: `${title} - Artisano`,
                text: `Découvrez cette magnifique œuvre d'art sur Artisano`,
                url: url
            }).then(() => {
                this.showNotification('Œuvre partagée !', 'success');
            }).catch((error) => {
                if (error.name !== 'AbortError') {
                    this.fallbackShare(url, title);
                }
            });
        } else {
            this.fallbackShare(url, title);
        }
    }

    fallbackShare(url, title) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(url).then(() => {
                this.showNotification('Lien copié dans le presse-papier !', 'success');
            }).catch(() => {
                this.showShareModal(url, title);
            });
        } else {
            this.showShareModal(url, title);
        }
    }

    showShareModal(url, title) {
        const modal = document.createElement('div');
        modal.className = 'share-modal-overlay';
        modal.innerHTML = `
            <div class="share-modal">
                <div class="share-modal-header">
                    <h3><i class="fas fa-share-alt"></i> Partager cette œuvre</h3>
                    <button class="btn-close">&times;</button>
                </div>
                <div class="share-modal-content">
                    <div class="share-url-container">
                        <input type="text" value="${url}" readonly class="share-url-input">
                        <button class="btn-copy">Copier</button>
                    </div>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}" target="_blank" class="share-btn facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}" target="_blank" class="share-btn twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}" target="_blank" class="share-btn linkedin">
                            <i class="fab fa-linkedin-in"></i> LinkedIn
                        </a>
                        <a href="mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}" class="share-btn email">
                            <i class="fas fa-envelope"></i> Email
                        </a>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        
        // Event listeners
        modal.querySelector('.btn-close').addEventListener('click', () => modal.remove());
        modal.querySelector('.btn-copy').addEventListener('click', (e) => {
            const input = modal.querySelector('.share-url-input');
            input.select();
            document.execCommand('copy');
            e.target.textContent = 'Copié !';
            setTimeout(() => e.target.textContent = 'Copier', 2000);
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }

    async handleAddToWishlist(button) {
        const itemId = button.dataset.id;
        
        try {
            button.disabled = true;
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            await this.addToWishlistAPI(itemId);
            
            button.innerHTML = '<i class="fas fa-heart"></i>';
            button.classList.add('added');
            
            setTimeout(() => {
                button.disabled = false;
                button.innerHTML = originalContent;
                button.classList.remove('added');
            }, 2000);
            
            this.showNotification('Œuvre ajoutée à vos favoris !', 'success');
            
        } catch (error) {
            console.error('Erreur:', error);
            this.showNotification('Erreur lors de l\'ajout aux favoris', 'error');
            
            button.disabled = false;
            button.innerHTML = '<i class="far fa-heart"></i>';
        }
    }

    handleQuickView(button) {
        const itemId = button.dataset.id;
        const item = button.closest('.wishlist-item');
        
        // Créer une modal de vue rapide
        this.showQuickViewModal(item, itemId);
    }

    showQuickViewModal(item, itemId) {
        const title = item.querySelector('.item-title a').textContent;
        const image = item.querySelector('.item-image img').src;
        const price = item.querySelector('.price-tag').textContent;
        const artisan = item.querySelector('.artisan-info span').textContent;
        const description = item.querySelector('.item-description').textContent;
        
        const modal = document.createElement('div');
        modal.className = 'quick-view-modal-overlay';
        modal.innerHTML = `
            <div class="quick-view-modal">
                <div class="quick-view-header">
                    <h3>Aperçu rapide</h3>
                    <button class="btn-close">&times;</button>
                </div>
                <div class="quick-view-content">
                    <div class="quick-view-image">
                        <img src="${image}" alt="${title}">
                    </div>
                    <div class="quick-view-info">
                        <h4>${title}</h4>
                        <p class="quick-view-artisan">Par ${artisan}</p>
                        <p class="quick-view-price">${price}</p>
                        <p class="quick-view-description">${description}</p>
                        <div class="quick-view-actions">
                            <button class="btn-primary btn-add-cart" data-id="${itemId}">
                                <i class="fas fa-shopping-cart"></i> Ajouter au panier
                            </button>
                            <a href="oeuvre-details.php?id=${itemId}" class="btn-outline">
                                <i class="fas fa-eye"></i> Voir les détails
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        
        modal.querySelector('.btn-close').addEventListener('click', () => modal.remove());
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
        
        // Gérer l'ajout au panier depuis la modal
        const cartBtn = modal.querySelector('.btn-add-cart');
        if (cartBtn) {
            cartBtn.addEventListener('click', () => {
                this.handleAddToCart(cartBtn);
            });
        }
    }

    async handleAddSelectedToCart() {
        if (this.selectedItems.size === 0) return;
        
        const selectedArray = Array.from(this.selectedItems);
        
        try {
            const promises = selectedArray.map(id => this.addToCartAPI(id));
            await Promise.all(promises);
            
            this.showNotification(`${selectedArray.length} œuvre(s) ajoutée(s) au panier !`, 'success');
            this.updateCartBadge();
            
            // Désélectionner tous les éléments
            document.querySelectorAll('.oeuvre-checkbox:checked').forEach(cb => {
                cb.checked = false;
            });
            this.selectedItems.clear();
            this.updateBulkActions();
            
        } catch (error) {
            console.error('Erreur:', error);
            this.showNotification('Erreur lors de l\'ajout au panier', 'error');
        }
    }

    async handleRemoveSelected() {
        if (this.selectedItems.size === 0) return;
        
        const selectedArray = Array.from(this.selectedItems);
        
        if (!confirm(`Êtes-vous sûr de vouloir retirer ${selectedArray.length} œuvre(s) de vos favoris ?`)) {
            return;
        }
        
        try {
            const promises = selectedArray.map(async (id) => {
                const item = document.querySelector(`[data-id="${id}"]`);
                if (item) {
                    item.style.opacity = '0.5';
                    await this.removeFromWishlistAPI(id);
                    return item;
                }
            });
            
            const items = await Promise.all(promises);
            
            // Supprimer les éléments avec animation
            items.forEach((item, index) => {
                if (item) {
                    setTimeout(() => {
                        item.style.height = '0';
                        item.style.margin = '0';
                        item.style.padding = '0';
                        setTimeout(() => item.remove(), 300);
                    }, index * 100);
                }
            });
            
            this.selectedItems.clear();
            this.updateBulkActions();
            this.updateStats();
            
            setTimeout(() => {
                this.checkEmptyState();
            }, (items.length * 100) + 500);
            
            this.showNotification(`${selectedArray.length} œuvre(s) retirée(s) de vos favoris`, 'success');
            
        } catch (error) {
            console.error('Erreur:', error);
            this.showNotification('Erreur lors de la suppression', 'error');
        }
    }

    updateStats() {
        const items = document.querySelectorAll('.wishlist-item');
        const statNumbers = document.querySelectorAll('.stat-number');
        
        if (statNumbers.length >= 1) {
            statNumbers[0].textContent = items.length;
        }
        
        // Recalculer la valeur totale et le prix moyen
        let totalValue = 0;
        items.forEach(item => {
            totalValue += parseFloat(item.dataset.price) || 0;
        });
        
        if (statNumbers.length >= 2) {
            statNumbers[1].textContent = new Intl.NumberFormat('fr-FR').format(totalValue) + '€';
        }
        
        if (statNumbers.length >= 3 && items.length > 0) {
            const avgPrice = totalValue / items.length;
            statNumbers[2].textContent = new Intl.NumberFormat('fr-FR').format(Math.round(avgPrice)) + '€';
        }
    }

    checkEmptyState() {
        const items = document.querySelectorAll('.wishlist-item');
        if (items.length === 0) {
            // Rediriger vers la page rechargée pour afficher l'état vide
            window.location.reload();
        }
    }

    updateCartBadge() {
        // Mettre à jour le badge du panier dans la navbar
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

    // API Simulation methods
    async addToCartAPI(itemId) {
        // Simulation d'un appel API
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

    async removeFromWishlistAPI(itemId) {
        // Simulation d'un appel API
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                if (Math.random() > 0.1) { // 90% de succès
                    resolve({ success: true });
                } else {
                    reject(new Error('Erreur réseau'));
                }
            }, 800);
        });
    }

    async addToWishlistAPI(itemId) {
        // Simulation d'un appel API
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

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        
        const icon = type === 'success' ? 'check-circle' : 
                    type === 'error' ? 'exclamation-circle' : 'info-circle';
        
        notification.innerHTML = `
            <i class="fas fa-${icon}"></i>
            <span>${message}</span>
        `;

        // Styles inline pour la notification
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; padding: 15px 20px;
            border-radius: 10px; color: white; font-weight: 500; z-index: 10000;
            display: flex; align-items: center; gap: 10px; transform: translateX(400px);
            transition: transform 0.3s ease; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px); max-width: 300px;
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

// Fonction d'initialisation globale
function initWishlistPage() {
    window.wishlistManager = new WishlistManager();
}

// CSS supplémentaires pour les modales (ajouté dynamiquement)
const modalStyles = `
    .share-modal-overlay,
    .quick-view-modal-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5); display: flex; align-items: center;
        justify-content: center; z-index: 10000; animation: fadeIn 0.3s ease;
    }

    .share-modal,
    .quick-view-modal {
        background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        max-width: 500px; width: 90%; animation: slideUp 0.3s ease;
    }

    .quick-view-modal {
        max-width: 800px;
    }

    .share-modal-header,
    .quick-view-header {
        padding: 1.5rem; border-bottom: 1px solid #eee; display: flex;
        align-items: center; justify-content: space-between;
    }

    .share-modal-content,
    .quick-view-content {
        padding: 1.5rem;
    }

    .quick-view-content {
        display: flex; gap: 2rem;
    }

    .quick-view-image {
        width: 300px; height: 250px; border-radius: 15px; overflow: hidden;
        flex-shrink: 0;
    }

    .quick-view-image img {
        width: 100%; height: 100%; object-fit: cover;
    }

    .quick-view-info {
        flex: 1;
    }

    .quick-view-info h4 {
        font-size: 1.5rem; font-weight: 600; margin-bottom: 0.5rem;
        color: #2c3e50;
    }

    .quick-view-artisan {
        color: #6c757d; margin-bottom: 1rem;
    }

    .quick-view-price {
        font-size: 1.3rem; font-weight: 700; color: #e74c3c;
        margin-bottom: 1rem;
    }

    .quick-view-description {
        color: #6c757d; line-height: 1.6; margin-bottom: 1.5rem;
    }

    .quick-view-actions {
        display: flex; gap: 1rem;
    }

    .share-url-container {
        display: flex; gap: 0.5rem; margin-bottom: 1rem;
    }

    .share-url-input {
        flex: 1; padding: 0.75rem; border: 1px solid #ddd; border-radius: 8px;
        font-size: 0.9rem;
    }

    .btn-copy {
        padding: 0.75rem 1rem; background: #e74c3c; color: white; border: none;
        border-radius: 8px; cursor: pointer; transition: background 0.3s ease;
    }

    .btn-copy:hover {
        background: #c0392b;
    }

    .share-buttons {
        display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem;
    }

    .share-btn {
        display: flex; align-items: center; justify-content: center; gap: 0.5rem;
        padding: 0.75rem; text-decoration: none; color: white; border-radius: 8px;
        font-weight: 500; transition: transform 0.3s ease;
    }

    .share-btn:hover {
        transform: translateY(-2px);
    }

    .share-btn.facebook { background: #3b5998; }
    .share-btn.twitter { background: #1da1f2; }
    .share-btn.linkedin { background: #0077b5; }
    .share-btn.email { background: #34495e; }

    .btn-close {
        background: none; border: none; font-size: 1.2rem; cursor: pointer;
        color: #95a5a6; transition: color 0.3s ease; padding: 0.5rem;
        border-radius: 50%;
    }

    .btn-close:hover {
        color: #7f8c8d; background: #f8f9fa;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 768px) {
        .quick-view-content {
            flex-direction: column;
        }
        
        .quick-view-image {
            width: 100%; height: 200px;
        }
        
        .quick-view-actions {
            flex-direction: column;
        }
    }
`;

// Ajouter les styles s'ils n'existent pas déjà
if (!document.getElementById('wishlist-modal-styles')) {
    const styleElement = document.createElement('style');
    styleElement.id = 'wishlist-modal-styles';
    styleElement.textContent = modalStyles;
    document.head.appendChild(styleElement);
}