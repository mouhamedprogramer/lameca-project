<?php
// Inclure le header
include 'includes/header.php';

// Traitement du formulaire de contact
$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    // Récupération et validation des données
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $type_contact = $_POST['type_contact'] ?? 'general';
    
    // Validation basique
    if (empty($nom) || empty($email) || empty($sujet) || empty($message)) {
        $error_message = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Veuillez saisir une adresse email valide.';
    } else {
        // Ici vous pouvez ajouter l'envoi d'email ou l'insertion en base de données
        // Pour cet exemple, on simule un envoi réussi
        $message_sent = true;
        
        // Exemple d'insertion en base (à décommenter si vous avez une table Messages)
        /*
        if (isset($conn)) {
            try {
                $query = "INSERT INTO Messages (nom, email, telephone, sujet, message, type_contact, date_creation) 
                         VALUES (?, ?, ?, ?, ?, ?, NOW())";
                $stmt = mysqli_prepare($conn, $query);
                mysqli_stmt_bind_param($stmt, "ssssss", $nom, $email, $telephone, $sujet, $message, $type_contact);
                mysqli_stmt_execute($stmt);
                $message_sent = true;
            } catch (Exception $e) {
                $error_message = 'Erreur lors de l\'envoi du message. Veuillez réessayer.';
            }
        }
        */
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Artisano</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding-top: 80px;
            padding-top: 0; /* Supprime l'espace en haut */
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .contact-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .contact-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .contact-info {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 2.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .contact-info h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(10px);
        }

        .info-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .info-details h3 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
        }

        .info-details p {
            opacity: 0.9;
            font-size: 0.95rem;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .social-link {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-5px);
        }

        .contact-form {
            background: white;
            border-radius: 25px;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }

        .form-header p {
            color: #6c757d;
            font-size: 0.95rem;
        }

        .form-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            background: #f8f9fa;
            padding: 0.5rem;
            border-radius: 15px;
        }

        .tab-btn {
            flex: 1;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 10px;
            background: transparent;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .tab-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .required {
            color: #e74c3c;
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        select,
        textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            outline: none;
            font-family: 'Poppins', sans-serif;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(-1px);
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.5s ease;
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .success-message,
        .error-message {
            padding: 1rem 1.5rem;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
        }

        .success-message {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            animation: slideInDown 0.5s ease;
        }

        .error-message {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            animation: slideInDown 0.5s ease;
        }

        .faq-section {
            margin-top: 3rem;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .faq-title {
            font-size: 2rem;
            color: white;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
        }

        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .faq-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            transition: all 0.3s ease;
        }

        .faq-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-5px);
        }

        .faq-card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.8rem;
            color: #fff;
        }

        .faq-card p {
            opacity: 0.9;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .office-hours {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
        }

        .office-hours h3 {
            font-size: 1.3rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .hours-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .hour-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .map-section {
            margin-top: 3rem;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .map-container {
            background: white;
            border-radius: 25px;
            padding: 2rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }

        .map-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .map-header h2 {
            font-size: 1.8rem;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }

        .map-placeholder {
            width: 100%;
            height: 400px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 1.1rem;
            border: 2px dashed #dee2e6;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .contact-container {
                padding: 1rem;
            }

            .contact-title {
                font-size: 2.5rem;
            }

            .contact-content {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-tabs {
                flex-direction: column;
            }

            .social-links {
                justify-content: center;
            }

            .hours-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .contact-title {
                font-size: 2rem;
            }

            .contact-info,
            .contact-form {
                padding: 1.5rem;
            }

            .info-item {
                flex-direction: column;
                text-align: center;
            }

            .info-item:hover {
                transform: translateY(-5px);
            }
        }
    </style>
</head>
<body>
    <div class="contact-container">
        <!-- Header -->
        <div class="contact-header">
            <h1 class="contact-title">Contactez-nous</h1>
            <p class="contact-subtitle">
                Notre équipe est là pour vous accompagner dans votre découverte de l'art artisanal. 
                N'hésitez pas à nous contacter pour toute question ou demande d'information.
            </p>
        </div>

        <!-- Contact Content -->
        <div class="contact-content">
            <!-- Contact Info -->
            <div class="contact-info">
                <h2>Informations de contact</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-details">
                        <h3>Adresse</h3>
                        <p>123 Rue des Artisans<br>75001 Paris, France</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-details">
                        <h3>Téléphone</h3>
                        <p>+33 1 23 45 67 89</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-details">
                        <h3>Email</h3>
                        <p>contact@artisano.fr</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="info-details">
                        <h3>Horaires</h3>
                        <p>Lun-Ven: 9h-18h<br>Sam: 10h-16h</p>
                    </div>
                </div>

                <div class="social-links">
                    <a href="#" class="social-link">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link">
                        <i class="fab fa-linkedin-in"></i>
                    </a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <div class="form-header">
                    <h2>Envoyez-nous un message</h2>
                    <p>Nous vous répondrons dans les plus brefs délais</p>
                </div>

                <?php if ($message_sent): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        Votre message a été envoyé avec succès ! Nous vous répondrons bientôt.
                    </div>
                <?php endif; ?>

                <?php if ($error_message): ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <div class="form-tabs">
                    <button type="button" class="tab-btn active" data-tab="general">Question générale</button>
                    <button type="button" class="tab-btn" data-tab="artisan">Devenir artisan</button>
                    <button type="button" class="tab-btn" data-tab="support">Support technique</button>
                </div>

                <form method="POST" action="" id="contactForm">
                    <input type="hidden" name="type_contact" id="typeContact" value="general">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nom">Nom complet <span class="required">*</span></label>
                            <input type="text" id="nom" name="nom" required value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone" value="<?= htmlspecialchars($_POST['telephone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="sujet">Sujet <span class="required">*</span></label>
                            <input type="text" id="sujet" name="sujet" required value="<?= htmlspecialchars($_POST['sujet'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" placeholder="Décrivez votre demande..." required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" name="send_message" class="submit-btn">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="faq-section">
            <div class="faq-header">
                <h2 class="faq-title">Questions fréquentes</h2>
            </div>
            
            <div class="faq-grid">
                <div class="faq-card">
                    <h3>Comment passer une commande ?</h3>
                    <p>Parcourez notre galerie, sélectionnez une œuvre et suivez le processus de commande. Vous recevrez une confirmation par email.</p>
                </div>
                
                <div class="faq-card">
                    <h3>Délais de livraison</h3>
                    <p>Les délais varient selon l'artisan et la complexité de l'œuvre, généralement entre 1 à 4 semaines.</p>
                </div>
                
                <div class="faq-card">
                    <h3>Retours et remboursements</h3>
                    <p>Vous disposez de 14 jours pour retourner un article non conforme. Consultez nos conditions générales.</p>
                </div>
                
                <div class="faq-card">
                    <h3>Devenir artisan partenaire</h3>
                    <p>Contactez-nous avec votre portfolio. Notre équipe évaluera votre candidature sous 5 jours ouvrés.</p>
                </div>
            </div>
        </div>

        <!-- Office Hours -->
        <div class="office-hours">
            <h3>Horaires d'ouverture</h3>
            <div class="hours-grid">
                <div class="hour-item">
                    <span>Lundi - Vendredi</span>
                    <span>9h00 - 18h00</span>
                </div>
                <div class="hour-item">
                    <span>Samedi</span>
                    <span>10h00 - 16h00</span>
                </div>
                <div class="hour-item">
                    <span>Dimanche</span>
                    <span>Fermé</span>
                </div>
            </div>
        </div>

        <!-- Map Section -->
        <div class="map-section">
            <div class="map-container">
                <div class="map-header">
                    <h2>Notre localisation</h2>
                    <p>Rendez-nous visite dans nos bureaux parisiens</p>
                </div>
                <div class="map-placeholder">
                    <div style="text-align: center;">
                        <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 1rem; color: #667eea;"></i>
                        <p>Carte interactive à intégrer<br>(Google Maps, OpenStreetMap...)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Gestion des onglets
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');
                
                // Mettre à jour le type de contact
                document.getElementById('typeContact').value = this.dataset.tab;
                
                // Adapter le placeholder du message selon le type
                const messageField = document.getElementById('message');
                switch(this.dataset.tab) {
                    case 'general':
                        messageField.placeholder = 'Décrivez votre demande...';
                        break;
                    case 'artisan':
                        messageField.placeholder = 'Parlez-nous de votre art, votre expérience et vos motivations...';
                        break;
                    case 'support':
                        messageField.placeholder = 'Décrivez le problème technique rencontré...';
                        break;
                }
            });
        });

        // Validation du formulaire
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const nom = document.getElementById('nom').value.trim();
            const email = document.getElementById('email').value.trim();
            const sujet = document.getElementById('sujet').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!nom || !email || !sujet || !message) {
                e.preventDefault();
                showNotification('Veuillez remplir tous les champs obligatoires.', 'error');
                return false;
            }

            // Validation email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showNotification('Veuillez saisir une adresse email valide.', 'error');
                return false;
            }

            // Animation du bouton de soumission
            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
            submitBtn.disabled = true;
        });

        // Fonction pour afficher les notifications
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'error' ? '#e74c3c' : type === 'success' ? '#27ae60' : '#3498db'};
                color: white;
                padding: 1rem 2rem;
                border-radius: 10px;
                z-index: 10000;
                animation: slideInRight 0.3s ease;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            `;
            notification.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'success' ? 'check-circle' : 'info-circle'}"></i> ${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        // Ajout des animations CSS pour les notifications
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Animation des éléments au scroll
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

        // Observer les cartes FAQ
        document.querySelectorAll('.faq-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });

        // Effet parallaxe léger
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const header = document.querySelector('.contact-header');
            if (header) {
                header.style.transform = `translateY(${scrolled * 0.1}px)`;
            }
        });

        // Auto-resize du textarea
        document.getElementById('message').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = `${this.scrollHeight}px`;
        });
    </script>
</body>
</html>
<?php
// Inclure le footer
include 'includes/footer.php';
