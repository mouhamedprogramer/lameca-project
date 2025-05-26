<?php
// Inclure le header
include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions Générales d'Utilisation | Artisano</title>
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

        .cgu-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .cgu-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .cgu-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .cgu-subtitle {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto;
        }

        .cgu-content {
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
        .section:nth-child(9) { animation-delay: 1.1s; }
        .section:nth-child(10) { animation-delay: 1.2s; }

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
            background: linear-gradient(135deg  , #667eea, #764ba2);
            border-radius: 2px;
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

        .section-content ul, .section-content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }

        .section-content li {
            margin-bottom: 0.5rem;
            position: relative;
        }

        .section-content ul li::before {
            content: '▶';
            color: #667eea;
            position: absolute;
            left: -1.5rem;
            top: 0;
        }

        .section-content ol li {
            padding-left: 0.5rem;
        }

        .warning-box {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.1), rgba(192, 57, 43, 0.1));
            border: 2px solid rgba(231, 76, 60, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            position: relative;
            overflow: hidden;
        }

        .warning-box::before {
            content: '⚠️';
            position: absolute;
            top: 1rem;
            left: 1rem;
            font-size: 1.5rem;
        }

        .warning-box h4 {
            color: #e74c3c;
            margin-bottom: 0.5rem;
            font-weight: 600;
            padding-left: 2.5rem;
        }

        .warning-box p {
            color: #4a5568;
            margin: 0;
            padding-left: 2.5rem;
        }

        .info-box {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
            border: 2px solid rgba(102, 126, 234, 0.2);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            position: relative;
            overflow: hidden;
        }

        .info-box::before {
            content: 'ℹ️';
            position: absolute;
            top: 1rem;
            left: 1rem;
            font-size: 1.5rem;
        }

        .info-box h4 {
            color: #2c3e50;
            margin-bottom: 0.5rem;
            font-weight: 600;
            padding-left: 2.5rem;
        }

        .info-box p {
            color: #4a5568;
            margin: 0;
            padding-left: 2.5rem;
        }

        .success-box {
            background: linear-gradient(135deg, rgba(39, 174, 96, 0.1), rgba(46, 204, 113, 0.1));
            border: 2px solid rgba(39, 174, 96, 0.3);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1.5rem 0;
            position: relative;
            overflow: hidden;
        }

        .success-box::before {
            content: '✅';
            position: absolute;
            top: 1rem;
            left: 1rem;
            font-size: 1.5rem;
        }

        .success-box h4 {
            color: #27ae60;
            margin-bottom: 0.5rem;
            font-weight: 600;
            padding-left: 2.5rem;
        }

        .success-box p {
            color: #4a5568;
            margin: 0;
            padding-left: 2.5rem;
        }

        .table-container {
            overflow-x: auto;
            margin: 1.5rem 0;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .cgu-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 15px;
            overflow: hidden;
        }

        .cgu-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }

        .cgu-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            color: #4a5568;
        }

        .cgu-table tr:last-child td {
            border-bottom: none;
        }

        .cgu-table tr:nth-child(even) {
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

        .definition {
            background: rgba(102, 126, 234, 0.05);
            border-left: 4px solid #667eea;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 0 10px 10px 0;
        }

        .definition strong {
            color: #2c3e50;
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
            .cgu-container {
                padding: 1rem;
            }

            .cgu-title {
                font-size: 2.5rem;
            }

            .cgu-content {
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
            .cgu-title {
                font-size: 2rem;
            }

            .cgu-content {
                padding: 1.5rem;
            }

            .section-title {
                font-size: 1.3rem;
            }

            .info-box,
            .warning-box,
            .success-box {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="cgu-container">
        <!-- Header -->
        <div class="cgu-header">
            <h1 class="cgu-title">Conditions Générales d'Utilisation</h1>
            <p class="cgu-subtitle">
                Conditions régissant l'utilisation de la plateforme Artisano
            </p>
        </div>

        <!-- Content -->
        <div class="cgu-content">
            <!-- Last Updated -->
            <div class="last-updated">
                <strong>Dernière mise à jour :</strong> <?= date('d/m/Y') ?> - Version 2.1
            </div>

            <!-- Table of Contents -->
            <div class="toc">
                <h3>Table des matières</h3>
                <ul>
                    <li><a href="#objet">1. Objet et champ d'application</a></li>
                    <li><a href="#definitions">2. Définitions</a></li>
                    <li><a href="#acceptation">3. Acceptation des CGU</a></li>
                    <li><a href="#inscription">4. Inscription et compte utilisateur</a></li>
                    <li><a href="#services">5. Description des services</a></li>
                    <li><a href="#obligations">6. Obligations des utilisateurs</a></li>
                    <li><a href="#transactions">7. Transactions et paiements</a></li>
                    <li><a href="#livraison">8. Livraison et retours</a></li>
                    <li><a href="#propriete">9. Propriété intellectuelle</a></li>
                    <li><a href="#responsabilite">10. Responsabilité et garanties</a></li>
                    <li><a href="#donnees">11. Protection des données personnelles</a></li>
                    <li><a href="#suspension">12. Suspension et résiliation</a></li>
                    <li><a href="#litiges">13. Règlement des litiges</a></li>
                    <li><a href="#modification">14. Modification des CGU</a></li>
                    <li><a href="#contact">15. Contact</a></li>
                </ul>
            </div>

            <!-- Section 1: Objet -->
            <div class="section" id="objet">
                <h2 class="section-title">1. Objet et champ d'application</h2>
                <div class="section-content">
                    <p>Les présentes Conditions Générales d'Utilisation (CGU) ont pour objet de définir les modalités et conditions d'utilisation de la plateforme Artisano, accessible à l'adresse www.artisano.fr.</p>
                    
                    <div class="info-box">
                        <h4>À qui s'appliquent ces CGU ?</h4>
                        <p>Ces conditions s'appliquent à tous les utilisateurs de la plateforme : visiteurs, clients, artisans partenaires et administrateurs.</p>
                    </div>

                    <p>L'utilisation de la plateforme implique l'acceptation pleine et entière des présentes CGU. Si vous n'acceptez pas ces conditions, vous devez cesser immédiatement l'utilisation de nos services.</p>
                </div>
            </div>

            <!-- Section 2: Définitions -->
            <div class="section" id="definitions">
                <h2 class="section-title">2. Définitions</h2>
                <div class="section-content">
                    <p>Aux fins des présentes CGU, les termes suivants sont définis comme suit :</p>

                    <div class="definition">
                        <strong>« Plateforme » ou « Site »</strong> : désigne le site internet Artisano accessible à l'adresse www.artisano.fr
                    </div>

                    <div class="definition">
                        <strong>« Artisano » ou « Nous »</strong> : désigne la société Artisano SARL, éditeur de la plateforme
                    </div>

                    <div class="definition">
                        <strong>« Utilisateur »</strong> : désigne toute personne physique ou morale utilisant la plateforme
                    </div>

                    <div class="definition">
                        <strong>« Client »</strong> : désigne tout utilisateur qui effectue un achat sur la plateforme
                    </div>

                    <div class="definition">
                        <strong>« Artisan »</strong> : désigne tout créateur professionnel ou amateur proposant ses œuvres sur la plateforme
                    </div>

                    <div class="definition">
                        <strong>« Œuvre »</strong> : désigne tout produit artisanal proposé à la vente sur la plateforme
                    </div>

                    <div class="definition">
                        <strong>« Compte »</strong> : désigne l'espace personnel de l'utilisateur sur la plateforme
                    </div>
                </div>
            </div>

            <!-- Section 3: Acceptation -->
            <div class="section" id="acceptation">
                <h2 class="section-title">3. Acceptation des CGU</h2>
                <div class="section-content">
                    <p>L'acceptation des présentes CGU est matérialisée par :</p>
                    <ul>
                        <li>La création d'un compte utilisateur</li>
                        <li>La passation d'une commande</li>
                        <li>L'utilisation continue des services après modification des CGU</li>
                        <li>Le clic sur "J'accepte les CGU" lors de l'inscription</li>
                    </ul>

                    <div class="warning-box">
                        <h4>Capacité juridique</h4>
                        <p>L'utilisation de la plateforme est réservée aux personnes majeures ou aux mineurs disposant de l'autorisation de leurs représentants légaux.</p>
                    </div>

                    <p>En acceptant ces CGU, vous déclarez et garantissez que vous avez la capacité juridique pour contracter et utiliser nos services.</p>
                </div>
            </div>

            <!-- Section 4: Inscription -->
            <div class="section" id="inscription">
                <h2 class="section-title">4. Inscription et compte utilisateur</h2>
                <div class="section-content">
                    <h4>4.1 Création de compte</h4>
                    <p>Pour accéder à certains services, vous devez créer un compte en fournissant des informations exactes et à jour :</p>
                    <ul>
                        <li>Nom et prénom</li>
                        <li>Adresse email valide</li>
                        <li>Mot de passe sécurisé</li>
                        <li>Adresse de livraison</li>
                        <li>Numéro de téléphone (optionnel)</li>
                    </ul>

                    <h4>4.2 Responsabilité du compte</h4>
                    <p>Vous êtes responsable de :</p>
                    <ul>
                        <li>La confidentialité de vos identifiants</li>
                        <li>Toutes les activités effectuées sous votre compte</li>
                        <li>La mise à jour de vos informations personnelles</li>
                        <li>La notification immédiate de tout usage non autorisé</li>
                    </ul>

                    <div class="success-box">
                        <h4>Compte artisan</h4>
                        <p>Les artisans souhaitant vendre sur la plateforme doivent suivre un processus de vérification spécifique incluant la validation de leurs créations et de leur identité.</p>
                    </div>
                </div>
            </div>

            <!-- Section 5: Services -->
            <div class="section" id="services">
                <h2 class="section-title">5. Description des services</h2>
                <div class="section-content">
                    <h4>5.1 Services pour les clients</h4>
                    <ul>
                        <li>Navigation et recherche d'œuvres artisanales</li>
                        <li>Achat en ligne sécurisé</li>
                        <li>Suivi de commandes</li>
                        <li>Liste de souhaits et favoris</li>
                        <li>Avis et évaluations</li>
                        <li>Participation aux événements</li>
                        <li>Galerie virtuelle</li>
                    </ul>

                    <h4>5.2 Services pour les artisans</h4>
                    <ul>
                        <li>Création de profil professionnel</li>
                        <li>Publication et gestion des œuvres</li>
                        <li>Outils de communication avec les clients</li>
                        <li>Gestion des commandes et stocks</li>
                        <li>Statistiques de vente</li>
                        <li>Participation aux événements</li>
                    </ul>

                    <div class="info-box">
                        <h4>Évolution des services</h4>
                        <p>Artisano se réserve le droit de faire évoluer, modifier ou interrompre tout ou partie de ses services, temporairement ou définitivement, sans préavis.</p>
                    </div>
                </div>
            </div>

            <!-- Section 6: Obligations -->
            <div class="section" id="obligations">
                <h2 class="section-title">6. Obligations des utilisateurs</h2>
                <div class="section-content">
                    <h4>6.1 Obligations générales</h4>
                    <p>Tout utilisateur s'engage à :</p>
                    <ul>
                        <li>Respecter les lois et règlements en vigueur</li>
                        <li>Ne pas porter atteinte aux droits d'autrui</li>
                        <li>Utiliser la plateforme de manière loyale et respectueuse</li>
                        <li>Ne pas tenter de contourner les mesures de sécurité</li>
                        <li>Signaler tout contenu inapproprié</li>
                    </ul>

                    <h4>6.2 Contenus interdits</h4>
                    <div class="warning-box">
                        <h4>Il est strictement interdit de publier :</h4>
                        <p>Des contenus illégaux, diffamatoires, violents, pornographiques, discriminatoires ou portant atteinte aux droits de propriété intellectuelle.</p>
                    </div>

                    <h4>6.3 Obligations spécifiques aux artisans</h4>
                    <ul>
                        <li>Garantir l'authenticité et l'originalité des œuvres</li>
                        <li>Fournir des descriptions exactes et des photos fidèles</li>
                        <li>Respecter les délais de traitement des commandes</li>
                        <li>Assurer un service client de qualité</li>
                        <li>Déclarer leurs revenus conformément à la législation</li>
                    </ul>

                    <div class="table-container">
                        <table class="cgu-table">
                            <thead>
                                <tr>
                                    <th>Violation</th>
                                    <th>Sanction</th>
                                    <th>Récidive</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Contenu inapproprié</td>
                                    <td>Avertissement + suppression</td>
                                    <td>Suspension temporaire</td>
                                </tr>
                                <tr>
                                    <td>Faux avis/évaluations</td>
                                    <td>Suppression + avertissement</td>
                                    <td>Suspension 30 jours</td>
                                </tr>
                                <tr>
                                    <td>Contrefaçon</td>
                                    <td>Suspension immédiate</td>
                                    <td>Exclusion définitive</td>
                                </tr>
                                <tr>
                                    <td>Fraude/escroquerie</td>
                                    <td>Exclusion définitive</td>
                                    <td>Signalement autorités</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Section 7: Transactions -->
            <div class="section" id="transactions">
                <h2 class="section-title">7. Transactions et paiements</h2>
                <div class="section-content">
                    <h4>7.1 Prix et disponibilité</h4>
                    <p>Les prix sont indiqués en euros, toutes taxes comprises. Ils peuvent être modifiés à tout moment par les artisans. La disponibilité des œuvres est mise à jour en temps réel mais n'est pas garantie.</p>

                    <h4>7.2 Commande</h4>
                    <p>Le processus de commande comprend :</p>
                    <ol>
                        <li>Sélection des œuvres et ajout au panier</li>
                        <li>Vérification du panier et des informations de livraison</li>
                        <li>Choix du mode de livraison et de paiement</li>
                        <li>Confirmation et validation de la commande</li>
                        <li>Paiement sécurisé</li>
                    </ol>

                    <div class="info-box">
                        <h4>Confirmation de commande</h4>
                        <p>Une confirmation de commande vous sera envoyée par email. Elle fait foi jusqu'à preuve du contraire.</p>
                    </div>

                    <h4>7.3 Moyens de paiement</h4>
                    <p>Nous acceptons les moyens de paiement suivants :</p>
                    <ul>
                        <li>Cartes bancaires (Visa, MasterCard, American Express)</li>
                        <li>PayPal</li>
                        <li>Virement bancaire (pour les commandes supérieures à 500€)</li>
                        <li>Paiement en plusieurs fois (selon conditions)</li>
                    </ul>

                    <h4>7.4 Commission</h4>
                    <p>Artisano prélève une commission de 8% (TTC) sur chaque vente réalisée par les artisans. Cette commission couvre :</p>
                    <ul>
                        <li>L'hébergement et la maintenance de la plateforme</li>
                        <li>Le traitement des paiements</li>
                        <li>Le service client</li>
                        <li>Les outils marketing et de promotion</li>
                    </ul>
                </div>
            </div>

            <!-- Section 8: Livraison -->
            <div class="section" id="livraison">
                <h2 class="section-title">8. Livraison et retours</h2>
                <div class="section-content">
                    <h4>8.1 Livraison</h4>
                    <p>Les délais de livraison sont indicatifs et dépendent de l'artisan et du transporteur choisi. Artisano ne peut être tenue responsable des retards de livraison.</p>

                    <div class="table-container">
                        <table class="cgu-table">
                            <thead>
                                <tr>
                                    <th>Zone de livraison</th>
                                    <th>Délai indicatif</th>
                                    <th>Frais de port</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>France métropolitaine</td>
                                    <td>3-7 jours ouvrés</td>
                                    <td>À partir de 4,90€</td>
                                </tr>
                                <tr>
                                    <td>Union Européenne</td>
                                    <td>5-10 jours ouvrés</td>
                                    <td>À partir de 9,90€</td>
                                </tr>
                                <tr>
                                    <td>International</td>
                                    <td>10-21 jours ouvrés</td>
                                    <td>À partir de 19,90€</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h4>8.2 Droit de rétractation</h4>
                    <p>Conformément à l'article L221-18 du Code de la consommation, vous disposez d'un délai de 14 jours calendaires pour vous rétracter sans avoir à justifier de motifs.</p>

                    <div class="warning-box">
                        <h4>Exceptions au droit de rétractation</h4>
                        <p>Les œuvres sur mesure ou personnalisées ne peuvent pas faire l'objet d'une rétractation, sauf défaut de conformité.</p>
                    </div>

                    <h4>8.3 Retours</h4>
                    <p>Pour retourner un article :</p>
                    <ol>
                        <li>Contactez le service client dans les 14 jours</li>
                        <li>Obtenez un numéro de retour</li>
                        <li>Renvoyez l'article dans son emballage d'origine</li>
                        <li>Les frais de retour sont à votre charge</li>
                        <li>Remboursement sous 14 jours après réception</li>
                    </ol>
                </div>
            </div>

            <!-- Section 9: Propriété intellectuelle -->
            <div class="section" id="propriete">
                <h2 class="section-title">9. Propriété intellectuelle</h2>
                <div class="section-content">
                    <h4>9.1 Droits d'Artisano</h4>
                    <p>Tous les éléments de la plateforme (design, logos, textes, codes, etc.) sont protégés par les droits de propriété intellectuelle et appartiennent à Artisano ou à ses partenaires.</p>

                    <h4>9.2 Droits des artisans</h4>
                    <p>Les artisans conservent tous leurs droits de propriété intellectuelle sur leurs œuvres. En publiant sur la plateforme, ils accordent à Artisano :</p>
                    <ul>
                        <li>Une licence d'utilisation pour la promotion des œuvres</li>
                        <li>Le droit de reproduire les images à des fins commerciales</li>
                        <li>Le droit d'utiliser les œuvres dans les supports marketing</li>
                    </ul>

                    <div class="success-box">
                        <h4>Protection contre la contrefaçon</h4>
                        <p>Artisano met en place des mesures pour lutter contre la contrefaçon et protéger les créations originales de ses artisans partenaires.</p>
                    </div>

                    <h4>9.3 Signalement de violation</h4>
                    <p>Si vous constatez une violation de droits de propriété intellectuelle, contactez-nous à : <strong>legal@artisano.fr</strong></p>
                </div>
            </div>

            <!-- Section 10: Responsabilité -->
            <div class="section" id="responsabilite">
                <h2 class="section-title">10. Responsabilité et garanties</h2>
                <div class="section-content">
                    <h4>10.1 Rôle d'Artisano</h4>
                    <p>Artisano agit en tant qu'intermédiaire technique entre les artisans et les clients. Nous ne sommes pas parties aux contrats de vente conclus entre eux.</p>

                    <h4>10.2 Limitations de responsabilité</h4>
                    <p>La responsabilité d'Artisano est limitée dans les cas suivants :</p>
                    <ul>
                        <li>Indisponibilité temporaire de la plateforme</li>
                        <li>Perte de données due à un cas de force majeure</li>
                        <li>Dommages indirects ou immatériels</li>
                        <li>Actes des artisans ou des transporteurs</li>
                    </ul>

                    <div class="warning-box">
                        <h4>Garanties des artisans</h4>
                        <p>Les artisans sont seuls responsables de la qualité, de la conformité et de la sécurité de leurs œuvres. Ils doivent respecter toutes les réglementations applicables.</p>
                    </div>

                    <h4>10.3 Assurance</h4>
                    <p>Artisano souscrit une assurance responsabilité civile professionnelle couvrant ses activités. Les détails de cette assurance sont disponibles sur demande.</p>
                </div>
            </div>

            <!-- Section 11: Données personnelles -->
            <div class="section" id="donnees">
                <h2 class="section-title">11    . Protection des données personnelles</h2>
                <div class="section-content">
                    <h4>11.1 Collecte des données</h4>
                    <p>Artisano collecte les données personnelles nécessaires à la création du compte, à la gestion des commandes et à l'amélioration de ses services :</p>
                    <ul>
                        <li>Identité (nom, prénom, email)</li>
                        <li>Adresse de livraison</li>
                        <li>Historique des commandes</li>
                        <li>Préférences de communication</li>
                    </ul>

                    <h4>11.2 Finalités du traitement</h4>
                    <p>Les données collectées sont utilisées pour :</p>
                    <ul>
                        <li>Gérer les comptes utilisateurs</li>
                        <li>Traiter les commandes et paiements</li>
                        <li>Assurer le service client</li>
                        <li>Envoyer des newsletters et offres promotionnelles (avec consentement)</li>
                        <li>Améliorer la plateforme et les services proposés</li>
                    </ul>

                    <div class="info-box">
                        <h4>Droits des utilisateurs</h4>
                        <p>Conformément au RGPD, vous disposez des droits suivants :</p>
                        <ul>
                            <li>Droit d'accès et de rectification</li>
                            <li>Droit à l'effacement (« droit à l'oubli »)</li>
                            <li>Droit à la limitation du traitement</li>
                            <li>Droit à la portabilité des données</li>
                            <li>Droit d'opposition au traitement</li>
                        </ul>
                    </div>

                    <h4>11.3 Sécurité des données</h4>
                    <p>Artisano met en œuvre des mesures techniques et organisationnelles pour garantir la sécurité et la confidentialité de vos données personnelles.</p>

                    <div class="success-box">
                        <h4>Transfert international de données</h4>
                        <p>Aucune donnée personnelle n'est transférée en dehors de l'Union Européenne sans garanties appropriées.</p>
                    </div>

                    <h4>11.4 Politique de confidentialité</h4>
                    <p>Pour plus d'informations sur la collecte et le traitement de vos données personnelles, consultez notre politique de confidentialité disponible sur le site.</p>
                </div>
            </div>
            <!-- Section 12: Suspension -->
            <div class="section" id="suspension">
                <h2 class="section-title">12. Suspension et résiliation</h2>
                <div class="section-content">
                    <h4>12.1 Suspension temporaire</h4>
                    <p>Artisano se réserve le droit de suspendre temporairement l'accès à la plateforme en cas de maintenance, d'incidents techniques ou de violation des CGU.</p>

                    <h4>12.2 Résiliation du compte</h4>
                    <p>Vous pouvez résilier votre compte à tout moment en nous contactant par email. La résiliation prendra effet dans un délai de 30 jours.</p>

                    <div class="warning-box">
                        <h4>Résiliation pour faute grave</h4>
                        <p>Artisano peut résilier immédiatement votre compte en cas de violation grave des CGU, notamment en cas de fraude, contrefaçon ou comportement inapproprié.</p>
                    </div>

                    <h4>12.3 Effets de la résiliation</h4>
                    <p>En cas de résiliation, vous perdrez l'accès à votre compte et à vos données personnelles. Les commandes en cours seront traitées conformément aux CGU.</p>
                </div>
            </div>
        </div>
        <!-- Section 13: Litiges -->
         
         

    </div>
    <button class="back-to-top" id="backToTop">↑</button>
    <script>
        // Back to top button functionality
        const backToTopButton = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTopButton.classList.add('visible');
            } else {
                backToTopButton.classList.remove('visible');
            }
        });

        backToTopButton.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    </script>
    <?php
    // Footer inclusion
    include 'includes/footer.php';
    ?>
</div>
</body>
</html>