// JavaScript pour la page de checkout
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du DOM
    const paymentMethods = document.querySelectorAll('.payment-method');
    const cardPayment = document.getElementById('card-payment');
    const paypalPayment = document.getElementById('paypal-payment');
    const cardNumber = document.getElementById('card-number');
    const expiry = document.getElementById('expiry');
    const cvv = document.getElementById('cvv');
    const placeOrderBtn = document.getElementById('place-order');
    const checkoutForm = document.getElementById('checkout-form');

    // Gestion des méthodes de paiement
    paymentMethods.forEach(method => {
        method.addEventListener('click', function() {
            // Retirer la classe active de toutes les méthodes
            paymentMethods.forEach(m => m.classList.remove('active'));
            // Ajouter la classe active à la méthode sélectionnée
            this.classList.add('active');

            const selectedMethod = this.dataset.method;
            
            if (selectedMethod === 'card') {
                cardPayment.style.display = 'block';
                paypalPayment.style.display = 'none';
            } else if (selectedMethod === 'paypal') {
                cardPayment.style.display = 'none';
                paypalPayment.style.display = 'block';
            }
        });
    });

    // Formatage du numéro de carte
    if (cardNumber) {
        cardNumber.addEventListener('input', function() {
            let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
            
            if (formattedValue.length > 19) {
                formattedValue = formattedValue.substring(0, 19);
            }
            
            this.value = formattedValue;
            
            // Détection du type de carte
            detectCardType(value);
        });
    }

    // Formatage de la date d'expiration
    if (expiry) {
        expiry.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            
            this.value = value;
        });
    }

    // Validation du CVV
    if (cvv) {
        cvv.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '');
        });
    }

    // Gestion du bouton de commande
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function() {
            processOrder();
        });
    }

    // Validation en temps réel
    const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            validateField(this);
        });
    });
});

// Fonction pour détecter le type de carte
function detectCardType(number) {
    const cardTypeIcon = document.querySelector('.card-type-icon');
    if (!cardTypeIcon) return;

    // Supprimer les espaces
    number = number.replace(/\s/g, '');

    let cardType = '';
    if (/^4/.test(number)) {
        cardType = 'fab fa-cc-visa';
    } else if (/^5[1-5]/.test(number) || /^2[2-7]/.test(number)) {
        cardType = 'fab fa-cc-mastercard';
    } else if (/^3[47]/.test(number)) {
        cardType = 'fab fa-cc-amex';
    }

    cardTypeIcon.className = `card-type-icon ${cardType}`;
}

// Fonction de validation d'un champ
function validateField(field) {
    const value = field.value.trim();
    let isValid = true;
    let errorMessage = '';

    // Validation générale des champs requis
    if (field.hasAttribute('required') && !value) {
        isValid = false;
        errorMessage = 'Ce champ est obligatoire';
    }

    // Validations spécifiques
    switch (field.type) {
        case 'email':
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (value && !emailRegex.test(value)) {
                isValid = false;
                errorMessage = 'Format d\'email invalide';
            }
            break;
        
        case 'tel':
            const phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
            if (value && !phoneRegex.test(value.replace(/\s/g, ''))) {
                isValid = false;
                errorMessage = 'Format de téléphone invalide';
            }
            break;
    }

    // Validations spécifiques pour les champs de carte
    if (field.id === 'card-number') {
        const cardNumber = value.replace(/\s/g, '');
        if (cardNumber && (cardNumber.length < 13 || cardNumber.length > 19)) {
            isValid = false;
            errorMessage = 'Numéro de carte invalide';
        }
    } else if (field.id === 'expiry') {
        if (value && !/^\d{2}\/\d{2}$/.test(value)) {
            isValid = false;
            errorMessage = 'Format MM/AA requis';
        } else if (value) {
            const [month, year] = value.split('/');
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear() % 100;
            const currentMonth = currentDate.getMonth() + 1;
            
            if (parseInt(month) < 1 || parseInt(month) > 12) {
                isValid = false;
                errorMessage = 'Mois invalide';
            } else if (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                isValid = false;
                errorMessage = 'Carte expirée';
            }
        }
    } else if (field.id === 'cvv') {
        if (value && (value.length < 3 || value.length > 4)) {
            isValid = false;
            errorMessage = 'CVV invalide (3-4 chiffres)';
        }
    }

    // Affichage de l'erreur
    showFieldError(field, isValid, errorMessage);
    return isValid;
}

// Fonction pour afficher les erreurs de validation
function showFieldError(field, isValid, errorMessage) {
    // Supprimer l'ancien message d'erreur
    const existingError = field.parentNode.querySelector('.field-error');
    if (existingError) {
        existingError.remove();
    }

    // Supprimer les classes d'erreur
    field.classList.remove('error', 'valid');

    if (!isValid && errorMessage) {
        // Ajouter la classe d'erreur
        field.classList.add('error');
        
        // Créer le message d'erreur
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = errorMessage;
        
        // Ajouter les styles d'erreur
        errorDiv.style.color = '#e74c3c';
        errorDiv.style.fontSize = '0.85rem';
        errorDiv.style.marginTop = '5px';
        
        field.parentNode.appendChild(errorDiv);
    } else if (isValid && field.value.trim()) {
        field.classList.add('valid');
    }
}

// Fonction principale pour traiter la commande
function processOrder() {
    const placeOrderBtn = document.getElementById('place-order');
    
    // Valider tous les champs
    if (!validateAllFields()) {
        showNotification('Veuillez corriger les erreurs dans le formulaire', 'error');
        return;
    }

    // Désactiver le bouton et afficher le chargement
    placeOrderBtn.disabled = true;
    placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement...';
    
    const checkoutContent = document.querySelector('.checkout-content');
    checkoutContent.classList.add('processing');

    // Récupérer les données du formulaire
    const formData = getFormData();

    // Simuler le traitement du paiement
    setTimeout(() => {
        processPayment(formData);
    }, 2000);
}

// Fonction pour valider tous les champs
function validateAllFields() {
    const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
    const activePaymentMethod = document.querySelector('.payment-method.active').dataset.method;
    
    let allValid = true;

    // Valider les champs de livraison
    requiredFields.forEach(field => {
        if (!validateField(field)) {
            allValid = false;
        }
    });

    // Valider les champs de paiement selon la méthode sélectionnée
    if (activePaymentMethod === 'card') {
        const cardFields = ['card-number', 'expiry', 'cvv', 'card-holder'];
        cardFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field && !validateField(field)) {
                allValid = false;
            }
        });
    }

    return allValid;
}

// Fonction pour récupérer les données du formulaire
function getFormData() {
    const activePaymentMethod = document.querySelector('.payment-method.active').dataset.method;
    
    const data = {
        // Informations de livraison
        livraison: {
            nom: document.getElementById('nom').value,
            prenom: document.getElementById('prenom').value,
            email: document.getElementById('email').value,
            telephone: document.getElementById('telephone').value,
            adresse: document.getElementById('adresse').value,
            ville: document.getElementById('ville').value,
            code_postal: document.getElementById('code_postal').value,
            pays: document.getElementById('pays').value
        },
        // Méthode de paiement
        paiement: {
            methode: activePaymentMethod
        }
    };

    // Ajouter les données de carte si nécessaire
    if (activePaymentMethod === 'card') {
        data.paiement.carte = {
            numero: document.getElementById('card-number').value.replace(/\s/g, ''),
            expiration: document.getElementById('expiry').value,
            cvv: document.getElementById('cvv').value,
            titulaire: document.getElementById('card-holder').value
        };
    }

    return data;
}

// Fonction pour traiter le paiement
function processPayment(formData) {
    fetch('actions/process-payment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        const checkoutContent = document.querySelector('.checkout-content');
        checkoutContent.classList.remove('processing');
        
        if (data.success) {
            // Paiement réussi
            showNotification('Commande finalisée avec succès !', 'success');
            
            // Redirection vers la page de confirmation
            setTimeout(() => {
                window.location.href = `commande-confirmee.php?id=${data.orderId}`;
            }, 2000);
        } else {
            // Erreur de paiement
            const placeOrderBtn = document.getElementById('place-order');
            placeOrderBtn.disabled = false;
            placeOrderBtn.innerHTML = '<i class="fas fa-lock"></i> Finaliser la commande';
            
            showNotification(data.message || 'Erreur lors du traitement du paiement', 'error');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        
        const checkoutContent = document.querySelector('.checkout-content');
        checkoutContent.classList.remove('processing');
        
        const placeOrderBtn = document.getElementById('place-order');
        placeOrderBtn.disabled = false;
        placeOrderBtn.innerHTML = '<i class="fas fa-lock"></i> Finaliser la commande';
        
        showNotification('Erreur de connexion', 'error');
    });
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
            .field-error {
                color: #e74c3c;
                font-size: 0.85rem;
                margin-top: 5px;
            }
            input.error, select.error, textarea.error {
                border-color: #e74c3c !important;
                background: #fdf2f2 !important;
            }
            input.valid, select.valid, textarea.valid {
                border-color: #27ae60 !important;
                background: #f2fff2 !important;
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

// Validation en temps réel du formulaire
function setupRealTimeValidation() {
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('input', function() {
            // Supprimer les classes d'erreur lors de la saisie
            this.classList.remove('error');
            const errorMsg = this.parentNode.querySelector('.field-error');
            if (errorMsg) {
                errorMsg.remove();
            }
        });
    });
}

// Initialiser la validation en temps réel
setupRealTimeValidation();

// Gestion de la navigation retour
window.addEventListener('beforeunload', function(event) {
    // Avertir l'utilisateur s'il tente de quitter la page
    if (document.querySelector('.processing')) {
        event.preventDefault();
        event.returnValue = 'Votre commande est en cours de traitement. Êtes-vous sûr de vouloir quitter ?';
    }
});