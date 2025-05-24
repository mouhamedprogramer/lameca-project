<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    header('Location: connexion.php');
    exit;
}

$idClient = $_SESSION['idUtilisateur'];

// Récupérer les articles du panier
$sql = "SELECT c.*, o.titre, o.prix, o.description, 
        (SELECT p.url FROM Photooeuvre p WHERE p.idOeuvre = o.idOeuvre ORDER BY p.idPhoto ASC LIMIT 1) as photo,
        a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom
        FROM Commande c
        JOIN Oeuvre o ON c.idOeuvre = o.idOeuvre
        JOIN Artisan a ON o.idArtisan = a.idArtisan
        JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
        WHERE c.idClient = ? AND c.statut = 'En attente'
        ORDER BY c.dateCommande DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idClient);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
    $total += $row['prix'] * $row['nombreArticles'];
}

// Rediriger si le panier est vide
if (empty($articles)) {
    header('Location: panier.php');
    exit;
}

// Récupérer les informations du client
$sql_client = "SELECT * FROM Utilisateur WHERE idUtilisateur = ?";
$stmt_client = $conn->prepare($sql_client);
$stmt_client->bind_param("i", $idClient);
$stmt_client->execute();
$client = $stmt_client->get_result()->fetch_assoc();

function formaterPrix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finaliser ma commande - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="checkout-header">
                <h1><i class="fas fa-credit-card"></i> Finaliser ma commande</h1>
                <div class="checkout-steps">
                    <div class="step active">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Panier</span>
                    </div>
                    <div class="step active">
                        <i class="fas fa-truck"></i>
                        <span>Livraison</span>
                    </div>
                    <div class="step active">
                        <i class="fas fa-credit-card"></i>
                        <span>Paiement</span>
                    </div>
                </div>
            </div>

            <div class="checkout-content">
                <!-- Informations de livraison -->
                <div class="checkout-section">
                    <div class="section-card">
                        <h2><i class="fas fa-truck"></i> Informations de livraison</h2>
                        
                        <form id="checkout-form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="nom">Nom *</label>
                                    <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($client['nom']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="prenom">Prénom *</label>
                                    <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($client['prenom']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="email">Email *</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="telephone">Téléphone *</label>
                                <input type="tel" id="telephone" name="telephone" value="<?php echo htmlspecialchars($client['telephone']); ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="adresse">Adresse de livraison *</label>
                                <textarea id="adresse" name="adresse" rows="3" required><?php echo htmlspecialchars($client['adresse']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="ville">Ville *</label>
                                    <input type="text" id="ville" name="ville" value="<?php echo htmlspecialchars($client['ville']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="code_postal">Code postal *</label>
                                    <input type="text" id="code_postal" name="code_postal" value="<?php echo htmlspecialchars($client['code_postal']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="pays">Pays *</label>
                                <select id="pays" name="pays" required>
                                    <option value="France" <?php echo $client['pays'] === 'France' ? 'selected' : ''; ?>>France</option>
                                    <option value="Belgique" <?php echo $client['pays'] === 'Belgique' ? 'selected' : ''; ?>>Belgique</option>
                                    <option value="Suisse" <?php echo $client['pays'] === 'Suisse' ? 'selected' : ''; ?>>Suisse</option>
                                    <option value="Luxembourg" <?php echo $client['pays'] === 'Luxembourg' ? 'selected' : ''; ?>>Luxembourg</option>
                                    <option value="Monaco" <?php echo $client['pays'] === 'Monaco' ? 'selected' : ''; ?>>Monaco</option>
                                </select>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Informations de paiement -->
                <div class="checkout-section">
                    <div class="section-card">
                        <h2><i class="fas fa-credit-card"></i> Informations de paiement</h2>
                        
                        <div class="payment-methods">
                            <div class="payment-method active" data-method="card">
                                <i class="fas fa-credit-card"></i>
                                <span>Carte bancaire</span>
                                <div class="card-icons">
                                    <i class="fab fa-cc-visa"></i>
                                    <i class="fab fa-cc-mastercard"></i>
                                    <i class="fab fa-cc-amex"></i>
                                </div>
                            </div>
                            <div class="payment-method" data-method="paypal">
                                <i class="fab fa-paypal"></i>
                                <span>PayPal</span>
                            </div>
                        </div>

                        <div id="card-payment" class="payment-form">
                            <div class="form-group card-number-group">
                                <label for="card-number">Numéro de carte *</label>
                                <div class="input-with-icon">
                                    <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                                    <div class="card-type-icon"></div>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="expiry">Date d'expiration *</label>
                                    <input type="text" id="expiry" placeholder="MM/AA" maxlength="5" required>
                                </div>
                                <div class="form-group">
                                    <label for="cvv">CVV *</label>
                                    <input type="text" id="cvv" placeholder="123" maxlength="4" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="card-holder">Nom du titulaire *</label>
                                <input type="text" id="card-holder" placeholder="Nom sur la carte" required>
                            </div>
                        </div>

                        <div id="paypal-payment" class="payment-form" style="display: none;">
                            <div class="paypal-info">
                                <p>Vous serez redirigé vers PayPal pour finaliser votre paiement en toute sécurité.</p>
                                <div class="paypal-logo">
                                    <i class="fab fa-paypal"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Conditions générales -->
                        <div class="terms-conditions">
                            <label class="checkbox-container">
                                <input type="checkbox" id="accept-terms" required>
                                <span class="checkmark"></span>
                                J'accepte les <a href="conditions-generales.php" target="_blank">conditions générales</a> et la <a href="politique-confidentialite.php" target="_blank">politique de confidentialité</a>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Résumé de commande -->
                <div class="checkout-summary">
                    <div class="summary-card">
                        <h3>Résumé de votre commande</h3>
                        
                        <div class="order-items">
                            <?php foreach ($articles as $article): ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <?php 
                                        $image_src = !empty($article['photo']) ? '../' . $article['photo'] : 'images/oeuvre-placeholder.jpg';
                                        ?>
                                        <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($article['titre']); ?>">
                                    </div>
                                    <div class="item-info">
                                        <h4><?php echo htmlspecialchars($article['titre']); ?></h4>
                                        <p>Par <?php echo htmlspecialchars($article['artisan_prenom'] . ' ' . $article['artisan_nom']); ?></p>
                                        <div class="item-quantity">Quantité: <?php echo $article['nombreArticles']; ?></div>
                                    </div>
                                    <div class="item-price">
                                        <?php echo formaterPrix($article['prix'] * $article['nombreArticles']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-totals">
                            <div class="summary-line">
                                <span>Sous-total:</span>
                                <span id="subtotal"><?php echo formaterPrix($total); ?></span>
                            </div>
                            <div class="summary-line">
                                <span>Frais de livraison:</span>
                                <span class="free-shipping">Gratuit</span>
                            </div>
                            <div class="summary-line taxes">
                                <span>TVA (20%):</span>
                                <span id="taxes"><?php echo formaterPrix($total * 0.2); ?></span>
                            </div>
                            <div class="summary-line total-line">
                                <span>Total TTC:</span>
                                <span id="total"><?php echo formaterPrix($total); ?></span>
                            </div>
                        </div>
                        
                        <div class="checkout-actions">
                            <a href="panier.php" class="btn-outline">
                                <i class="fas fa-arrow-left"></i> Retour au panier
                            </a>
                            <button type="button" id="place-order" class="btn-primary checkout-btn" disabled>
                                <i class="fas fa-lock"></i> Finaliser la commande
                            </button>
                        </div>
                        
                        <div class="security-info">
                            <i class="fas fa-shield-alt"></i>
                            <span>Paiement 100% sécurisé SSL</span>
                        </div>

                        <div class="payment-security">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Configuration globale
        const MONTANT_TOTAL = <?php echo $total; ?>;
        const NB_ARTICLES = <?php echo count($articles); ?>;

        // JavaScript pour la page de checkout
        document.addEventListener('DOMContentLoaded', function() {
            initCheckout();
        });

        function initCheckout() {
            setupPaymentMethods();
            setupCardFormatting();
            setupFormValidation();
            setupTermsValidation();
            initPlaceOrderButton();
        }

        // Gestion des méthodes de paiement
        function setupPaymentMethods() {
            const paymentMethods = document.querySelectorAll('.payment-method');
            const cardPayment = document.getElementById('card-payment');
            const paypalPayment = document.getElementById('paypal-payment');

            paymentMethods.forEach(method => {
                method.addEventListener('click', function() {
                    paymentMethods.forEach(m => m.classList.remove('active'));
                    this.classList.add('active');

                    const selectedMethod = this.dataset.method;
                    
                    if (selectedMethod === 'card') {
                        cardPayment.style.display = 'block';
                        paypalPayment.style.display = 'none';
                    } else if (selectedMethod === 'paypal') {
                        cardPayment.style.display = 'none';
                        paypalPayment.style.display = 'block';
                    }

                    validateForm();
                });
            });
        }

        // Formatage des champs de carte
        function setupCardFormatting() {
            const cardNumber = document.getElementById('card-number');
            const expiry = document.getElementById('expiry');
            const cvv = document.getElementById('cvv');

            if (cardNumber) {
                cardNumber.addEventListener('input', function() {
                    let value = this.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
                    let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                    
                    if (formattedValue.length > 19) {
                        formattedValue = formattedValue.substring(0, 19);
                    }
                    
                    this.value = formattedValue;
                    detectCardType(value);
                    validateField(this);
                });
            }

            if (expiry) {
                expiry.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    
                    if (value.length >= 2) {
                        value = value.substring(0, 2) + '/' + value.substring(2, 4);
                    }
                    
                    this.value = value;
                    validateField(this);
                });
            }

            if (cvv) {
                cvv.addEventListener('input', function() {
                    this.value = this.value.replace(/\D/g, '');
                    validateField(this);
                });
            }
        }

        // Détection du type de carte
        function detectCardType(number) {
            const cardTypeIcon = document.querySelector('.card-type-icon');
            if (!cardTypeIcon) return;

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

        // Validation des formulaires
        function setupFormValidation() {
            const inputs = document.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                input.addEventListener('input', () => validateField(input));
                input.addEventListener('blur', () => validateField(input));
            });
        }

        // Validation des conditions générales
        function setupTermsValidation() {
            const termsCheckbox = document.getElementById('accept-terms');
            if (termsCheckbox) {
                termsCheckbox.addEventListener('change', validateForm);
            }
        }

        // Validation d'un champ individuel
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
            if (value) {
                switch (field.type) {
                    case 'email':
                        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                        if (!emailRegex.test(value)) {
                            isValid = false;
                            errorMessage = 'Format d\'email invalide';
                        }
                        break;
                    
                    case 'tel':
                        const phoneRegex = /^(?:(?:\+|00)33|0)\s*[1-9](?:[\s.-]*\d{2}){4}$/;
                        if (!phoneRegex.test(value.replace(/\s/g, ''))) {
                            isValid = false;
                            errorMessage = 'Format de téléphone invalide';
                        }
                        break;
                }

                // Validations spécifiques pour les champs de carte
                if (field.id === 'card-number') {
                    const cardNumber = value.replace(/\s/g, '');
                    if (cardNumber.length < 13 || cardNumber.length > 19) {
                        isValid = false;
                        errorMessage = 'Numéro de carte invalide';
                    }
                } else if (field.id === 'expiry') {
                    if (!/^\d{2}\/\d{2}$/.test(value)) {
                        isValid = false;
                        errorMessage = 'Format MM/AA requis';
                    } else {
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
                    if (value.length < 3 || value.length > 4) {
                        isValid = false;
                        errorMessage = 'CVV invalide (3-4 chiffres)';
                    }
                }
            }

            // Affichage de l'erreur
            showFieldError(field, isValid, errorMessage);
            validateForm();
            
            return isValid;
        }

        // Affichage des erreurs de validation
        function showFieldError(field, isValid, errorMessage) {
            const existingError = field.parentNode.querySelector('.field-error');
            if (existingError) {
                existingError.remove();
            }

            field.classList.remove('error', 'valid');

            if (!isValid && errorMessage) {
                field.classList.add('error');
                
                const errorDiv = document.createElement('div');
                errorDiv.className = 'field-error';
                errorDiv.textContent = errorMessage;
                
                field.parentNode.appendChild(errorDiv);
            } else if (isValid && field.value.trim()) {
                field.classList.add('valid');
            }
        }

        // Validation globale du formulaire
        function validateForm() {
            const requiredFields = document.querySelectorAll('input[required], select[required], textarea[required]');
            const activePaymentMethod = document.querySelector('.payment-method.active').dataset.method;
            const termsCheckbox = document.getElementById('accept-terms');
            const placeOrderBtn = document.getElementById('place-order');
            
            let allValid = true;

            // Valider les champs de livraison
            requiredFields.forEach(field => {
                if (field.closest('#card-payment') && activePaymentMethod !== 'card') {
                    return; // Ignorer les champs de carte si PayPal est sélectionné
                }
                
                if (!field.value.trim() || field.classList.contains('error')) {
                    allValid = false;
                }
            });

            // Vérifier les conditions générales
            if (!termsCheckbox.checked) {
                allValid = false;
            }

            // Activer/désactiver le bouton
            placeOrderBtn.disabled = !allValid;
            
            return allValid;
        }

        // Initialisation du bouton de commande
        function initPlaceOrderButton() {
            const placeOrderBtn = document.getElementById('place-order');
            
            if (placeOrderBtn) {
                placeOrderBtn.addEventListener('click', function() {
                    if (validateForm()) {
                        processOrder();
                    }
                });
            }
        }

        // Traitement de la commande
        function processOrder() {
            const placeOrderBtn = document.getElementById('place-order');
            
            // Désactiver le bouton et afficher le chargement
            placeOrderBtn.disabled = true;
            placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Traitement en cours...';
            
            const checkoutContent = document.querySelector('.checkout-content');
            checkoutContent.classList.add('processing');

            // Récupérer les données du formulaire
            const formData = getFormData();

            // Simuler le traitement du paiement
            setTimeout(() => {
                processPayment(formData);
            }, 2000);
        }

        // Récupération des données du formulaire
        function getFormData() {
            const activePaymentMethod = document.querySelector('.payment-method.active').dataset.method;
            
            const data = {
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
                paiement: {
                    methode: activePaymentMethod,
                    montant: MONTANT_TOTAL
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

        // Traitement du paiement
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
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notif => notif.remove());

            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close">&times;</button>
            `;

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
                    .notification.show { transform: translateX(0); }
                    .notification-success { background: linear-gradient(135deg, #27ae60, #229954); }
                    .notification-error { background: linear-gradient(135deg, #e74c3c, #c0392b); }
                    .notification-info { background: linear-gradient(135deg, #3498db, #2980b9); }
                    .notification-close {
                        background: none; border: none; color: white; font-size: 18px;
                        cursor: pointer; padding: 0; margin-left: auto;
                    }
                    .field-error { color: #e74c3c; font-size: 0.85rem; margin-top: 5px; }
                    input.error, select.error, textarea.error {
                        border-color: #e74c3c !important; background: #fdf2f2 !important;
                    }
                    input.valid, select.valid, textarea.valid {
                        border-color: #27ae60 !important; background: #f2fff2 !important;
                    }
                `;
                document.head.appendChild(styles);
            }

            document.body.appendChild(notification);
            setTimeout(() => notification.classList.add('show'), 100);

            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            });

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 5000);
        }
        // Obtenir l'icône de notification en fonction du type
        function getNotificationIcon(type) {
            switch (type) {
                case 'success': return 'check-circle';
                case 'error': return 'times-circle';
                case 'info': return 'info-circle';
                default: return 'bell';
            }
        }
    </script>
</body>
</html>

