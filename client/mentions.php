<?php
// Inclure le header
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions Légales | Artisano</title>
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
            line-height: 1.6;
            padding-top: 0; /* Supprime l'espace en haut */

        }

        .legal-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .legal-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .legal-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .legal-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto;
        }

        .legal-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 3rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .section {
            margin-bottom: 2.5rem;
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
        }

        .section:nth-child(1) { animation-delay: 0.3s; }
        .section:nth-child(2) { animation-delay: 0.4s; }
        .section:nth-child(3) { animation-delay: 0.5s; }
        .section:nth-child(4) { animation-delay: 0.6s; }
        .section:nth-child(5) { animation-delay: 0.7s; }
        .section:nth-child(6) { animation-delay: 0.8s; }
        .section:nth-child(7) { animation-delay: 0.9s; }
        .section:nth-child(8) { animation-delay: 1.0s; }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            position: relative;
            padding-left: 1.5rem;
        }

        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 2rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .section-content {
            color: #4a5568;
            font-size: 1rem;
            margin-bottom: 1rem;
            padding-left: 1.5rem;
        }

        .section-content p {
            margin-bottom: 1rem;
        }

        .section-content ul {
            margin: 1rem 0;
            padding-left: 2rem;
        }

        .section-content li {
            margin-bottom: 0.5rem;
            position: relative;
        }

        .section-content li::before {
            content: '▶';
            color: #667eea;
            position: absolute;
            left: -1.5rem;
            top: 0;
        }

        .contact-info {
            background: rgba(102, 126, 234, 0.05);
            border-left: 4px solid #667eea;
            padding: 1.5rem;
            border-radius: 0 15px 15px 0;
            margin: 1.5rem 0;
        }

        .contact-info h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .contact-info p {
            margin: 0.5rem 0;
            color: #4a5568;
        }

        .contact-info a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .contact-info a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        .highlight-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            position: relative;
            overflow: hidden;
        }

        .highlight-box::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }

        .highlight-box h4 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .highlight-box p {
            color: #4a5568;
            margin: 0;
        }

        .table-container {
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .legal-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .legal-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .legal-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            color: #4a5568;
        }

        .legal-table tr:last-child td {
            border-bottom: none;
        }

        .legal-table tr:nth-child(even) {
            background: rgba(102, 126, 234, 0.02);
        }

        .back-to-top {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            opacity: 0;
            visibility: hidden;
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .toc {
            background: rgba(102, 126, 234, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .toc h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .toc ul {
            list-style: none;
            padding: 0;
        }

        .toc li {
            margin-bottom: 0.5rem;
        }

        .toc a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            display: block;
            transition: all 0.3s ease;
        }

        .toc a:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #764ba2;
            transform: translateX(10px);
        }

        .last-updated {
            background: rgba(102, 126, 234, 0.1);
            border-radius: 15px;
            padding: 1rem;
            margin-bottom: 2rem;
            text-align: center;
            color: #4a5568;
            font-style: italic;
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

        /* Responsive */
        @media (max-width: 768px) {
            .legal-container {
                padding: 1rem;
            }

            .legal-title {
                font-size: 2.5rem;
            }

            .legal-content {
                padding: 2rem;
            }

            .section-title {
                font-size: 1.5rem;
            }

            .section-content {
                padding-left: 0;
            }

            .section-title {
                padding-left: 1rem;
            }

            .back-to-top {
                width: 50px;
                height: 50px;
                bottom: 1rem;
                right: 1rem;
            }
        }

        @media (max-width: 480px) {
            .legal-title {
                font-size: 2rem;
            }

            .legal-content {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.3rem;
            }

            .contact-info,
            .highlight-box {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="legal-container">
        <!-- Header -->
        <div class="legal-header">
            <h1 class="legal-title">Mentions Légales</h1>
            <p class="legal-subtitle">
                Informations légales obligatoires concernant le site Artisano
            </p>
        </div>

        <!-- Content -->
        <div class="legal-content">
            <!-- Last Updated -->
            <div class="last-updated">
                <strong>Dernière mise à jour :</strong> <?= date('d/m/Y') ?>
            </div>

            <!-- Table of Contents -->
            <div class="toc">
                <h3>Table des matières</h3>
                <ul>
                    <li><a href="#editeur">1. Éditeur du site</a></li>
                    <li><a href="#responsable">2. Responsable de publication</a></li>
                    <li><a href="#hebergeur">3. Hébergeur</a></li>
                    <li><a href="#propriete">4. Propriété intellectuelle</a></li>
                    <li><a href="#donnees">5. Protection des données</a></li>
                    <li><a href="#cookies">6. Cookies</a></li>
                    <li><a href="#responsabilite">7. Limitation de responsabilité</a></li>
                    <li><a href="#contact">8. Contact</a></li>
                </ul>
            </div>

            <!-- Section 1: Éditeur -->
            <div class="section" id="editeur">
                <h2 class="section-title">1. Éditeur du site</h2>
                <div class="section-content">
                    <div class="contact-info">
                        <h4>Artisano SARL</h4>
                        <p><strong>Forme juridique :</strong> Société à Responsabilité Limitée</p>
                        <p><strong>Capital social :</strong> 50 000 €</p>
                        <p><strong>RCS :</strong> Paris B 123 456 789</p>
                        <p><strong>SIRET :</strong> 123 456 789 00012</p>
                        <p><strong>Code APE :</strong> 4791B</p>
                        <p><strong>TVA Intracommunautaire :</strong> FR12 123456789</p>
                        <p><strong>Adresse :</strong> 123 Rue de l'Artisanat, 75001 Paris, France</p>
                        <p><strong>Téléphone :</strong> <a href="tel:+33123456789">+33 1 23 45 67 89</a></p>
                        <p><strong>Email :</strong> <a href="mailto:contact@artisano.fr">contact@artisano.fr</a></p>
                    </div>
                </div>
            </div>

            <!-- Section 2: Responsable de publication -->
            <div class="section" id="responsable">
                <h2 class="section-title">2. Responsable de publication</h2>
                <div class="section-content">
                    <p>Le responsable de la publication est Monsieur Jean DUPONT, en qualité de gérant de la société Artisano SARL.</p>
                    <div class="highlight-box">
                        <h4>Contact du responsable de publication :</h4>
                        <p>Email : <a href="mailto:direction@artisano.fr">direction@artisano.fr</a></p>
                        <p>Téléphone : <a href="tel:+33123456789">+33 1 23 45 67 89</a></p>
                    </div>
                </div>
            </div>

            <!-- Section 3: Hébergeur -->
            <div class="section" id="hebergeur">
                <h2 class="section-title">3. Hébergeur</h2>
                <div class="section-content">
                    <p>Le site est hébergé par :</p>
                    <div class="contact-info">
                        <h4>OVH SAS</h4>
                        <p><strong>Adresse :</strong> 2 rue Kellermann, 59100 Roubaix, France</p>
                        <p><strong>Téléphone :</strong> +33 9 72 10 10 07</p>
                        <p><strong>Site web :</strong> <a href="https://www.ovh.com" target="_blank">www.ovh.com</a></p>
                    </div>
                </div>
            </div>

            <!-- Section 4: Propriété intellectuelle -->
            <div class="section" id="propriete">
                <h2 class="section-title">4. Propriété intellectuelle</h2>
                <div class="section-content">
                    <p>L'ensemble du contenu de ce site (textes, images, vidéos, logos, icônes, sons, logiciels, etc.) est protégé par les lois en vigueur sur la propriété intellectuelle.</p>
                    
                    <div class="highlight-box">
                        <h4>Droits d'auteur</h4>
                        <p>Tous les éléments du site sont et restent la propriété intellectuelle et exclusive d'Artisano SARL. Toute reproduction, modification, transmission, publication, adaptation de tout ou partie des éléments du site, quel que soit le moyen ou le procédé utilisé, est interdite, sauf autorisation écrite préalable.</p>
                    </div>

                    <h4>Marques et logos</h4>
                    <p>Les marques, logos et signes distinctifs reproduits sur le site sont protégés et ne peuvent être reproduits ou utilisés sans l'autorisation expresse de leurs propriétaires.</p>

                    <h4>Œuvres d'art</h4>
                    <p>Les œuvres présentées sur le site appartiennent à leurs créateurs respectifs. Toute reproduction sans autorisation est strictement interdite.</p>
                </div>
            </div>

            <!-- Section 5: Protection des données -->
            <div class="section" id="donnees">
                <h2 class="section-title">5. Protection des données personnelles</h2>
                <div class="section-content">
                    <p>Artisano SARL s'engage à respecter la réglementation en vigueur applicable au traitement des données personnelles et notamment le Règlement Général sur la Protection des Données (RGPD).</p>

                    <h4>Responsable du traitement</h4>
                    <p>Artisano SARL, représentée par son gérant Monsieur Jean DUPONT.</p>

                    <h4>Finalités du traitement</h4>
                    <ul>
                        <li>Gestion des comptes utilisateurs</li>
                        <li>Traitement des commandes</li>
                        <li>Communication commerciale (avec consentement)</li>
                        <li>Amélioration de nos services</li>
                        <li>Respect de nos obligations légales</li>
                    </ul>

                    <div class="highlight-box">
                        <h4>Vos droits</h4>
                        <p>Conformément au RGPD, vous disposez des droits suivants : accès, rectification, effacement, portabilité, limitation du traitement, opposition et retrait du consentement. Pour exercer ces droits, contactez-nous à : <a href="mailto:dpo@artisano.fr">dpo@artisano.fr</a></p>
                    </div>

                    <h4>Conservation des données</h4>
                    <div class="table-container">
                        <table class="legal-table">
                            <thead>
                                <tr>
                                    <th>Type de données</th>
                                    <th>Durée de conservation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Données de compte client</td>
                                    <td>3 ans après la dernière connexion</td>
                                </tr>
                                <tr>
                                    <td>Données de commande</td>
                                    <td>10 ans (obligations comptables)</td>
                                </tr>
                                <tr>
                                    <td>Données de prospection</td>
                                    <td>3 ans après le dernier contact</td>
                                </tr>
                                <tr>
                                    <td>Cookies</td>
                                    <td>13 mois maximum</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section 6: Cookies -->
            <div class="section" id="cookies">
                <h2 class="section-title">6. Cookies</h2>
                <div class="section-content">
                    <p>Le site utilise des cookies pour améliorer votre expérience de navigation et analyser l'utilisation du site.</p>

                    <h4>Types de cookies utilisés :</h4>
                    <ul>
                        <li><strong>Cookies essentiels :</strong> Nécessaires au fonctionnement du site</li>
                        <li><strong>Cookies de performance :</strong> Pour analyser l'utilisation du site</li>
                        <li><strong>Cookies de fonctionnalité :</strong> Pour mémoriser vos préférences</li>
                        <li><strong>Cookies publicitaires :</strong> Pour personnaliser la publicité (avec consentement)</li>
                    </ul>

                    <div class="highlight-box">
                        <h4>Gestion des cookies</h4>
                        <p>Vous pouvez à tout moment modifier vos préférences de cookies via notre bandeau de consentement ou dans les paramètres de votre navigateur.</p>
                    </div>
                </div>
            </div>

            <!-- Section 7: Limitation de responsabilité -->
            <div class="section" id="responsabilite">
                <h2 class="section-title">7. Limitation de responsabilité</h2>
                <div class="section-content">
                    <h4>Disponibilité du site</h4>
                    <p>Artisano s'efforce de maintenir accessible le site 24h/24 et 7j/7. Cependant, nous ne pouvons garantir une disponibilité absolue et nous nous réservons le droit d'interrompre temporairement l'accès pour maintenance.</p>

                    <h4>Contenu du site</h4>
                    <p>Les informations présentes sur le site sont données à titre indicatif. Malgré nos efforts pour maintenir l'exactitude des informations, nous ne pouvons garantir l'absence d'erreurs ou d'omissions.</p>

                    <h4>Liens externes</h4>
                    <p>Le site peut contenir des liens vers des sites tiers. Nous n'exerçons aucun contrôle sur ces sites et déclinons toute responsabilité quant à leur contenu.</p>

                    <div class="highlight-box">
                        <h4>Force majeure</h4>
                        <p>Artisano ne pourra être tenue responsable de l'inexécution de ses obligations en cas de force majeure ou de circonstances exceptionnelles indépendantes de sa volonté.</p>
                    </div>
                </div>
            </div>

            <!-- Section 8: Contact -->
            <div class="section" id="contact">
                <h2 class="section-title">8. Contact</h2>
                <div class="section-content">
                    <p>Pour toute question relative aux présentes mentions légales, vous pouvez nous contacter :</p>
                    
                    <div class="contact-info">
                        <h4>Service juridique - Artisano</h4>
                        <p><strong>Email :</strong> <a href="mailto:legal@artisano.fr">legal@artisano.fr</a></p>
                        <p><strong>Courrier :</strong><br>
                        Artisano SARL<br>
                        Service Juridique<br>
                        123 Rue de l'Artisanat<br>
                        75001 Paris, France</p>
                        <p><strong>Téléphone :</strong> <a href="tel:+33123456789">+33 1 23 45 67 89</a></p>
                    </div>

                    <div class="highlight-box">
                        <h4>Médiation</h4>
                        <p>En cas de litige, vous pouvez recourir à la médiation de la consommation. Médiateur : <a href="https://www.economie.gouv.fr/mediation-conso" target="_blank">Plateforme de résolution des litiges en ligne</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" id="backToTop" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Back to top functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTop.classList.add('visible');
            } else {
                backToTop.classList.remove('visible');
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Smooth scrolling for TOC links
        document.querySelectorAll('.toc a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    targetElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Animation observer
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

        // Observer all sections
        document.querySelectorAll('.section').forEach(section => {
            observer.observe(section);
        });

        console.log('Mentions légales initialisées avec succès!');
    </script>
    <?php
// Inclure le header
include 'includes/footer.php';
?>

</body>
</html>