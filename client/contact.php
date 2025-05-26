<?php
include_once 'includes/header.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Artisano</title>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg,rgb(120, 130, 174) 0%,rgb(151, 96, 206) 100%);
            min-height: 100vh;
            padding-top: 0;
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

        .map-header p {
            color: #6c757d;
            font-size: 0.95rem;
        }

        #map {
            width: 100%;
            height: 400px;
            border-radius: 15px;
            border: 2px solid #e9ecef;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 10px;
        }

        .custom-popup {
            text-align: center;
            font-family: 'Poppins', sans-serif;
        }

        .custom-popup h3 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .custom-popup p {
            color: #6c757d;
            font-size: 0.9rem;
            margin: 0.3rem 0;
        }

        .custom-popup .address {
            font-weight: 600;
            color: #667eea;
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

            #map {
                height: 300px;
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
                        📍
                    </div>
                    <div class="info-details">
                        <h3>Adresse</h3>
                        <p>2 Rues des Côtes d'Auty<br>91480 Varennes-Jarcy, France</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        📞
                    </div>
                    <div class="info-details">
                        <h3>Téléphone</h3>
                        <p>+33 1 23 45 67 89</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        ✉️
                    </div>
                    <div class="info-details">
                        <h3>Email</h3>
                        <p>contact@artisano.fr</p>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        🕒
                    </div>
                    <div class="info-details">
                        <h3>Horaires</h3>
                        <p>Lun-Ven: 9h-18h<br>Sam: 10h-16h</p>
                    </div>
                </div>

                <div class="social-links">
                    <a href="#" class="social-link">📘</a>
                    <a href="#" class="social-link">📷</a>
                    <a href="#" class="social-link">🐦</a>
                    <a href="#" class="social-link">💼</a>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form">
                <div class="form-header">
                    <h2>Envoyez-nous un message</h2>
                    <p>Nous vous répondrons dans les plus brefs délais</p>
                </div>

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
                            <input type="text" id="nom" name="nom" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="telephone">Téléphone</label>
                            <input type="tel" id="telephone" name="telephone">
                        </div>
                        <div class="form-group">
                            <label for="sujet">Sujet <span class="required">*</span></label>
                            <input type="text" id="sujet" name="sujet" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" placeholder="Décrivez votre demande..." required></textarea>
                    </div>

                    <button type="submit" name="send_message" class="submit-btn">
                        ✈️ Envoyer le message
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
                    <p>Rendez-nous visite dans nos bureaux de Varennes-Jarcy</p>
                </div>
                <div id="map"></div>
            </div>
        </div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        // Initialisation de la carte
        document.addEventListener('DOMContentLoaded', function() {
            // Coordonnées approximatives de Varennes-Jarcy
            const lat = 48.6833;
            const lng = 2.5667;
            
            // Initialiser la carte
            const map = L.map('map').setView([lat, lng], 15);
            
            // Ajouter les tuiles de la carte (OpenStreetMap)
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);
            
            // Créer une icône personnalisée
            const customIcon = L.divIcon({
                className: 'custom-marker',
                html: '<div style="background: linear-gradient(135deg, #667eea, #764ba2); width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.3);"></div>',
                iconSize: [30, 30],
                iconAnchor: [15, 30],
                popupAnchor: [0, -30]
            });
            
            // Ajouter un marqueur
            const marker = L.marker([lat, lng], { icon: customIcon }).addTo(map);
            
            // Contenu de la popup
            const popupContent = `
                <div class="custom-popup">
                    <h3>🏢 Artisano</h3>
                    <p class="address">2 Rues des Côtes d'Auty</p>
                    <p>91480 Varennes-Jarcy</p>
                    <p>📞 +33 1 23 45 67 89</p>
                    <p>✉️ contact@artisano.fr</p>
                    <hr style="margin: 10px 0; border: none; height: 1px; background: #e9ecef;">
                    <p style="font-size: 0.8rem; color: #6c757d;">Lun-Ven: 9h-18h | Sam: 10h-16h</p>
                </div>
            `;
            
            marker.bindPopup(popupContent, {
                maxWidth: 250,
                className: 'custom-leaflet-popup'
            });
            
            // Ouvrir la popup par défaut
            marker.openPopup();
            
            // Ajouter un cercle pour montrer la zone de service
            L.circle([lat, lng], {
                color: '#667eea',
                fillColor: '#667eea',
                fillOpacity: 0.1,
                radius: 500
            }).addTo(map);
            
            // Effet de rebond au clic sur le marqueur
            marker.on('click', function() {
                this.setLatLng([lat + 0.001, lng]).setLatLng([lat, lng]);
            });
        });

        // Gestion des onglets
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                document.getElementById('typeContact').value = this.dataset.tab;
                
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

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                e.preventDefault();
                showNotification('Veuillez saisir une adresse email valide.', 'error');
                return false;
            }

            const submitBtn = document.querySelector('.submit-btn');
            submitBtn.innerHTML = '⏳ Envoi en cours...';
            submitBtn.disabled = true;
        });

        // Fonction de notification
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
            notification.innerHTML = `${type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️'} ${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }

        // Animations au scroll
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

        document.querySelectorAll('.faq-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });

        // Auto-resize du textarea
        document.getElementById('message').addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = `${this.scrollHeight}px`;
        });

        // Styles pour les animations
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
            .custom-leaflet-popup .leaflet-popup-content-wrapper {
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            .custom-leaflet-popup .leaflet-popup-tip {
                background: white;
            }
        `;
        document.head.appendChild(style);
    </script>
    <?php
include_once 'includes/footer.php';
?>
</body>
</html>