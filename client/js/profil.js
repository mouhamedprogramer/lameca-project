/* ===============================
   PROFIL - JAVASCRIPT MODERNE
=============================== */

document.addEventListener('DOMContentLoaded', function() {
    initializeTabs();
    initializeFormValidation();
    initializeAnimations();
    initializePhotoUpload();
    initializePreferences();
});

/* ===============================
   GESTION DES ONGLETS
=============================== */

function showTab(tabName) {
    // Masquer tous les contenus d'onglets
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => {
        content.classList.remove('active');
    });
    
    // Désactiver tous les boutons d'onglets
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activer l'onglet sélectionné
    const selectedTab = document.getElementById(tabName + '-tab');
    const selectedButton = document.querySelector(`[onclick="showTab('${tabName}')"]`);
    
    if (selectedTab && selectedButton) {
        selectedTab.classList.add('active');
        selectedButton.classList.add('active');
        
        // Animation d'entrée
        selectedTab.style.opacity = '0';
        selectedTab.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            selectedTab.style.transition = 'all 0.4s ease';
            selectedTab.style.opacity = '1';
            selectedTab.style.transform = 'translateY(0)';
        }, 50);
    }
    
    // Sauvegarder l'onglet actif dans le localStorage
    localStorage.setItem('activeProfileTab', tabName);
}

function initializeTabs() {
    // Restaurer l'onglet actif depuis le localStorage
    const savedTab = localStorage.getItem('activeProfileTab');
    if (savedTab) {
        showTab(savedTab);
    }
    
    // Ajouter les event listeners aux boutons d'onglets
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('onclick').match(/showTab\('(.+)'\)/)[1];
            showTab(tabName);
        });
    });
}

/* ===============================
   VALIDATION DES FORMULAIRES
=============================== */

function initializeFormValidation() {
    const forms = document.querySelectorAll('.profile-form');
    
    forms.forEach(form => {
        // Validation en temps réel
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', validateField);
            input.addEventListener('input', clearFieldError);
        });
        
        // Validation à la soumission
        form.addEventListener('submit', function(e) {
            if (!validateForm(this)) {
                e.preventDefault();
            } else {
                showLoadingState(this);
            }
        });
    });
    
    // Validation spéciale pour les mots de passe
    const newPasswordField = document.getElementById('new_password');
    const confirmPasswordField = document.getElementById('confirm_password');
    
    if (newPasswordField && confirmPasswordField) {
        confirmPasswordField.addEventListener('input', function() {
            if (newPasswordField.value !== this.value) {
                showFieldError(this, 'Les mots de passe ne correspondent pas');
            } else {
                clearFieldError({ target: this });
            }
        });
    }
}

function validateField(e) {
    const field = e.target;
    const value = field.value.trim();
    
    // Validation selon le type de champ
    switch(field.type) {
        case 'email':
            if (value && !isValidEmail(value)) {
                showFieldError(field, 'Adresse email invalide');
                return false;
            }
            break;
            
        case 'password':
            if (field.id === 'new_password' && value && value.length < 6) {
                showFieldError(field, 'Le mot de passe doit contenir au moins 6 caractères');
                return false;
            }
            break;
            
        case 'tel':
            if (value && !isValidPhone(value)) {
                showFieldError(field, 'Numéro de téléphone invalide');
                return false;
            }
            break;
    }
    
    // Validation des champs requis
    if (field.hasAttribute('required') && !value) {
        showFieldError(field, 'Ce champ est obligatoire');
        return false;
    }
    
    clearFieldError({ target: field });
    return true;
}

function validateForm(form) {
    const fields = form.querySelectorAll('input[required], select[required], textarea[required]');
    let isValid = true;
    
    fields.forEach(field => {
        if (!validateField({ target: field })) {
            isValid = false;
        }
    });
    
    return isValid;
}

function showFieldError(field, message) {
    clearFieldError({ target: field });
    
    field.classList.add('error');
    field.style.borderColor = '#ef4444';
    
    const errorElement = document.createElement('div');
    errorElement.className = 'field-error';
    errorElement.textContent = message;
    errorElement.style.cssText = `
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        animation: slideInDown 0.3s ease;
    `;
    
    field.parentNode.appendChild(errorElement);
}

function clearFieldError(e) {
    const field = e.target;
    field.classList.remove('error');
    field.style.borderColor = '';
    
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }
}

function showLoadingState(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    }
}

/* ===============================
   UTILITAIRES DE VALIDATION
=============================== */

function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidPhone(phone) {
    const phoneRegex = /^[\+]?[0-9\s\-\(\)]{10,}$/;
    return phoneRegex.test(phone);
}

/* ===============================
   ANIMATIONS ET EFFETS
=============================== */

function initializeAnimations() {
    // Animation des cartes statistiques
    const statCards = document.querySelectorAll('.stat-card');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, index) => {
            if (entry.isIntersecting) {
                setTimeout(() => {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }, index * 100);
            }
        });
    }, { threshold: 0.1 });
    
    statCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        card.style.transition = 'all 0.6s ease';
        observer.observe(card);
    });
    
    // Animation des chiffres
    animateCounters();
}

function animateCounters() {
    const counters = document.querySelectorAll('.stat-info h3');
    
    counters.forEach(counter => {
        const text = counter.textContent;
        const number = parseFloat(text.replace(/[^\d.-]/g, ''));
        
        if (!isNaN(number) && number > 0) {
            counter.textContent = '0';
            
            const increment = number / 50;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= number) {
                    counter.textContent = text;
                    clearInterval(timer);
                } else {
                    if (text.includes('€')) {
                        counter.textContent = Math.floor(current).toLocaleString() + '€';
                    } else if (text.includes('/5')) {
                        counter.textContent = current.toFixed(1) + '/5';
                    } else {
                        counter.textContent = Math.floor(current);
                    }
                }
            }, 30);
        }
    });
}

/* ===============================
   GESTION DE LA PHOTO DE PROFIL
=============================== */

function changePhoto() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = 'image/*';
    input.style.display = 'none';
    
    input.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 5 * 1024 * 1024) { // 5MB max
                showNotification('La taille de l\'image ne doit pas dépasser 5MB', 'error');
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.profile-avatar');
                const img = preview.querySelector('img');
                
                if (img) {
                    img.src = e.target.result;
                } else {
                    preview.innerHTML = `<img src="${e.target.result}" alt="Photo de profil">`;
                }
                
                // Animation de changement
                preview.style.transform = 'scale(0.8)';
                setTimeout(() => {
                    preview.style.transition = 'all 0.3s ease';
                    preview.style.transform = 'scale(1)';
                }, 100);
                
                // Ici vous pouvez ajouter le code pour uploader l'image vers le serveur
                uploadPhoto(file);
            };
            reader.readAsDataURL(file);
        }
    });
    
    document.body.appendChild(input);
    input.click();
    document.body.removeChild(input);
}

function uploadPhoto(file) {
    // Simulation d'upload (remplacez par votre logique d'upload)
    showNotification('Photo de profil mise à jour !', 'success');
    
    /* Exemple d'implémentation réelle :
    const formData = new FormData();
    formData.append('photo', file);
    
    fetch('upload_photo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Photo de profil mise à jour !', 'success');
        } else {
            showNotification('Erreur lors de l\'upload', 'error');
        }
    })
    .catch(error => {
        showNotification('Erreur lors de l\'upload', 'error');
    });
    */
}

function initializePhotoUpload() {
    // Drag & Drop pour la photo
    const avatar = document.querySelector('.profile-avatar');
    if (avatar) {
        avatar.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.style.opacity = '0.7';
        });
        
        avatar.addEventListener('dragleave', function(e) {
            e.preventDefault();
            this.style.opacity = '1';
        });
        
        avatar.addEventListener('drop', function(e) {
            e.preventDefault();
            this.style.opacity = '1';
            
            const files = e.dataTransfer.files;
            if (files.length > 0 && files[0].type.startsWith('image/')) {
                const event = { target: { files: files } };
                // Simuler le changement de fichier
                const input = document.createElement('input');
                input.type = 'file';
                input.files = files;
                input.dispatchEvent(new Event('change'));
            }
        });
    }
}

/* ===============================
   GESTION DES PRÉFÉRENCES
=============================== */

function initializePreferences() {
    const switches = document.querySelectorAll('.switch input[type="checkbox"]');
    
    switches.forEach(switchEl => {
        switchEl.addEventListener('change', function() {
            const isChecked = this.checked;
            const preferenceItem = this.closest('.preference-item');
            const preferenceName = preferenceItem.querySelector('h3').textContent;
            
            // Animation du switch
            const slider = this.nextElementSibling;
            slider.style.transform = 'scale(1.1)';
            setTimeout(() => {
                slider.style.transform = 'scale(1)';
            }, 200);
            
            // Sauvegarder la préférence
            savePreference(preferenceName, isChecked);
            
            // Notification
            const status = isChecked ? 'activée' : 'désactivée';
            showNotification(`Préférence "${preferenceName}" ${status}`, 'success');
        });
    });
}

function savePreference(name, value) {
    // Sauvegarder dans localStorage temporairement
    const preferences = JSON.parse(localStorage.getItem('userPreferences') || '{}');
    preferences[name] = value;
    localStorage.setItem('userPreferences', JSON.stringify(preferences));
    
    /* Exemple d'implémentation pour sauvegarder en base :
    fetch('save_preference.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            preference: name,
            value: value
        })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success) {
            console.error('Erreur lors de la sauvegarde de la préférence');
        }
    });
    */
}

/* ===============================
   SYSTÈME DE NOTIFICATIONS
=============================== */

function showNotification(message, type = 'info', duration = 4000) {
    // Créer l'élément de notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="notification-close" onclick="closeNotification(this)">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    // Styles de la notification
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-width: 300px;
        max-width: 500px;
        transform: translateX(100%);
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    `;
    
    // Couleurs selon le type
    const colors = {
        success: { bg: 'rgba(16, 185, 129, 0.9)', color: 'white' },
        error: { bg: 'rgba(239, 68, 68, 0.9)', color: 'white' },
        warning: { bg: 'rgba(245, 158, 11, 0.9)', color: 'white' },
        info: { bg: 'rgba(59, 130, 246, 0.9)', color: 'white' }
    };
    
    const color = colors[type] || colors.info;
    notification.style.background = color.bg;
    notification.style.color = color.color;
    
    // Ajouter au DOM
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Auto-suppression
    setTimeout(() => {
        closeNotification(notification.querySelector('.notification-close'));
    }, duration);
}

function getNotificationIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-triangle',
        warning: 'exclamation-circle',
        info: 'info-circle'
    };
    return icons[type] || icons.info;
}

function closeNotification(button) {
    const notification = button.closest('.notification');
    notification.style.transform = 'translateX(100%)';
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 300);
}

/* ===============================
   GESTION DES RACCOURCIS CLAVIER
=============================== */

document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + S pour sauvegarder
    if ((e.ctrlKey || e.metaKey) && e.key === 's') {
        e.preventDefault();
        const activeTab = document.querySelector('.tab-content.active');
        const form = activeTab?.querySelector('form');
        if (form) {
            form.dispatchEvent(new Event('submit'));
            showNotification('Sauvegarde en cours...', 'info', 2000);
        }
    }
    
    // Échap pour fermer les modales/notifications
    if (e.key === 'Escape') {
        const notifications = document.querySelectorAll('.notification');
        notifications.forEach(notification => {
            const closeBtn = notification.querySelector('.notification-close');
            if (closeBtn) closeNotification(closeBtn);
        });
    }
    
    // Navigation entre onglets avec les flèches
    if (e.key === 'ArrowLeft' || e.key === 'ArrowRight') {
        const activeTab = document.querySelector('.tab-btn.active');
        const tabs = Array.from(document.querySelectorAll('.tab-btn'));
        const currentIndex = tabs.indexOf(activeTab);
        
        if (currentIndex !== -1) {
            let newIndex;
            if (e.key === 'ArrowLeft') {
                newIndex = currentIndex > 0 ? currentIndex - 1 : tabs.length - 1;
            } else {
                newIndex = currentIndex < tabs.length - 1 ? currentIndex + 1 : 0;
            }
            
            tabs[newIndex].click();
        }
    }
});

/* ===============================
   GESTION DE L'ÉTAT HORS LIGNE
=============================== */

window.addEventListener('online', function() {
    showNotification('Connexion rétablie', 'success');
});

window.addEventListener('offline', function() {
    showNotification('Connexion perdue - Les modifications seront sauvegardées localement', 'warning');
});

/* ===============================
   AUTO-SAUVEGARDE
=============================== */

let autoSaveTimer;
function setupAutoSave() {
    const inputs = document.querySelectorAll('.profile-form input, .profile-form select, .profile-form textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                saveFormDataLocally();
            }, 2000); // Sauvegarde après 2 secondes d'inactivité
        });
    });
}

function saveFormDataLocally() {
    const formData = {};
    const inputs = document.querySelectorAll('.profile-form input, .profile-form select, .profile-form textarea');
    
    inputs.forEach(input => {
        if (input.type !== 'password') { // Ne pas sauvegarder les mots de passe
            formData[input.name || input.id] = input.value;
        }
    });
    
    localStorage.setItem('profileFormData', JSON.stringify(formData));
}

function restoreFormDataFromLocal() {
    const savedData = localStorage.getItem('profileFormData');
    if (savedData) {
        const formData = JSON.parse(savedData);
        
        Object.keys(formData).forEach(key => {
            const input = document.querySelector(`[name="${key}"], #${key}`);
            if (input && input.type !== 'password') {
                input.value = formData[key];
            }
        });
    }
}

/* ===============================
   AMÉLIORATION DE L'ACCESSIBILITÉ
=============================== */

function enhanceAccessibility() {
    // Ajouter des attributs ARIA
    const tabs = document.querySelectorAll('.tab-btn');
    const tabPanels = document.querySelectorAll('.tab-content');
    
    tabs.forEach((tab, index) => {
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-selected', tab.classList.contains('active'));
        tab.setAttribute('tabindex', tab.classList.contains('active') ? '0' : '-1');
        tab.id = `tab-${index}`;
    });
    
    tabPanels.forEach((panel, index) => {
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', `tab-${index}`);
        panel.setAttribute('tabindex', '0');
    });
    
    // Gestion du focus pour les onglets
    tabs.forEach(tab => {
        tab.addEventListener('focus', function() {
            this.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
}

/* ===============================
   GESTION DES ERREURS GLOBALES
=============================== */

window.addEventListener('error', function(e) {
    console.error('Erreur JavaScript:', e.error);
    showNotification('Une erreur s\'est produite. Veuillez rafraîchir la page.', 'error');
});

window.addEventListener('unhandledrejection', function(e) {
    console.error('Promesse rejetée:', e.reason);
    showNotification('Une erreur réseau s\'est produite.', 'error');
});

/* ===============================
   INITIALISATION FINALE
=============================== */

// Initialiser toutes les fonctionnalités au chargement
document.addEventListener('DOMContentLoaded', function() {
    setupAutoSave();
    restoreFormDataFromLocal();
    enhanceAccessibility();
    
    // Afficher un message de bienvenue
    setTimeout(() => {
        showNotification('Bienvenue sur votre profil !', 'info', 3000);
    }, 1000);
});

/* ===============================
   UTILITAIRES EXPORTÉS
=============================== */

// Fonctions globales utilisables depuis le HTML
window.showTab = showTab;
window.changePhoto = changePhoto;
window.closeNotification = closeNotification;