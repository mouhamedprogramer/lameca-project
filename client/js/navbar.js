// Dans votre script existant
window.updateBadgeCount = function(badgeClass, count) {
    const badge = document.querySelector(`.${badgeClass}`);
    if (badge) {
        badge.textContent = count;
        if (count > 0) {
            badge.style.display = 'flex';
            badge.classList.add('new', 'notify'); // Ajoute les animations new et notify
            setTimeout(() => {
                badge.classList.remove('new', 'notify'); // Retire après 3s
            }, 3000);
            if (count > 99) {
                badge.classList.add('high-count'); // Gère les nombres élevés
            } else {
                badge.classList.remove('high-count');
            }
            animateBadge(badge);
        } else {
            badge.style.display = 'none';
            badge.classList.remove('new', 'notify', 'high-count');
        }
    }
};