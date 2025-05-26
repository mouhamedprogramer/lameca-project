<?php
// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/conn.php';

// Statistiques pour la page
$stats = [
    'artisans' => 0,
    'oeuvres' => 0,
    'clients' => 0,
    'evenements' => 0
];

try {
    // Nombre d'artisans
    $result = $conn->query("SELECT COUNT(*) as total FROM Artisan");
    if ($result) {
        $stats['artisans'] = $result->fetch_assoc()['total'];
    }
    
    // Nombre d'œuvres
    $result = $conn->query("SELECT COUNT(*) as total FROM Oeuvre WHERE disponibilite = TRUE");
    if ($result) {
        $stats['oeuvres'] = $result->fetch_assoc()['total'];
    }
    
    // Nombre de clients
    $result = $conn->query("SELECT COUNT(*) as total FROM Utilisateur WHERE role = 'Client'");
    if ($result) {
        $stats['clients'] = $result->fetch_assoc()['total'];
    }
    
    // Nombre d'événements
    $result = $conn->query("SELECT COUNT(*) as total FROM Evenement WHERE dateDebut >= CURDATE()");
    if ($result) {
        $stats['evenements'] = $result->fetch_assoc()['total'];
    }
} catch (Exception $e) {
    // En cas d'erreur, garder les valeurs par défaut
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>À Propos - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* Variables CSS */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --accent-gradient: linear-gradient(135deg,rgb(31, 109, 178) 0%, #00f2fe 100%);
            --success-gradient: linear-gradient(135deg,rgb(42, 138, 130) 0%,rgb(31, 175, 86) 100%);
            
            --text-primary: #1a1a2e;
            --text-secondary: #6b7280;
            --text-light: #9ca3af;
            --text-white: #ffffff;
            
            --bg-glass: rgba(255, 255, 255, 0.1);
            --bg-card: rgba(255, 255, 255, 0.95);
            
            --shadow-card: 0 20px 40px rgba(0, 0, 0, 0.1);
            --shadow-hover: 0 30px 60px rgba(0, 0, 0, 0.15);
            
            --border-radius: 20px;
            --border-radius-lg: 30px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--primary-gradient);
            overflow-x: hidden;
        }

        /* Arrière-plan animé */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .bg-shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            animation: float 20s ease-in-out infinite;
        }

        .bg-shape:nth-child(1) {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .bg-shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 70%;
            right: 20%;
            animation-delay: -7s;
        }

        .bg-shape:nth-child(3) {
            width: 300px;
            height: 300px;
            bottom: 10%;
            left: 30%;
            animation-delay: -14s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
                opacity: 0.3;
            }
            25% {
                transform: translateY(-30px) rotate(90deg);
                opacity: 0.7;
            }
            50% {
                transform: translateY(-60px) rotate(180deg);
                opacity: 0.5;
            }
            75% {
                transform: translateY(-30px) rotate(270deg);
                opacity: 0.8;
            }
        }

        /* Hero Section */
        .hero-section {
            min-height: 80vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            text-align: center;
            color: white;
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 700;
            margin-bottom: 20px;
            background: linear-gradient(45deg, #ffffff, #f0f0f0, #ffffff);
            background-size: 300% 300%;
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: shimmerText 3s ease-in-out infinite;
        }

        @keyframes shimmerText {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        .hero-subtitle {
            font-size: 1.5rem;
            margin-bottom: 30px;
            opacity: 0.9;
            font-weight: 300;
        }

        .hero-description {
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto 40px;
            opacity: 0.8;
            line-height: 1.8;
        }

        /* Container principal */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            position: relative;
            z-index: 1;
        }

        /* Sections */
        .section {
            margin: 80px 0;
            animation: fadeInUp 0.8s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(60px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Cartes modernes */
        .card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            padding: 40px;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
            transition: var(--transition);
        }

        .card:hover::before {
            left: 100%;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        /* Statistiques */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 80px;
        }

        .stat-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary-gradient);
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: var(--primary-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 10px;
            display: block;
        }

        .stat-label {
            font-size: 1.1rem;
            color: var(--text-secondary);
            font-weight: 500;
        }

        .stat-icon {
            font-size: 2.5rem;
            background: var(--primary-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 15px;
        }

        /* Mission Section */
        .mission-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin: 80px 0;
        }

        .mission-content h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
            background-clip: text;
            -webkit-background-clip: text;
            color : white;
        }

        .mission-content p {
            font-size: 1.1rem;
            color: var(--text-secondary);
            line-height: 1.8;
            margin-bottom: 20px;
        }

        .mission-image {
            position: relative;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-card);
        }

        .mission-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            transition: var(--transition);
        }

        .mission-image:hover img {
            transform: scale(1.05);
        }

        /* Valeurs */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 80px 0;
        }

        .value-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
            position: relative;
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-hover);
        }

        .value-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            box-shadow: var(--shadow-card);
        }

        .value-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .value-description {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        /* Équipe (simulation) */
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin: 80px 0;
        }

        .team-card {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius);
            padding: 30px;
            text-align: center;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: var(--transition);
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: var(--shadow-hover);
        }

        .team-avatar {
            width: 100px;
            height: 100px;
            margin: 0 auto 20px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: var(--shadow-card);
        }

        .team-name {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 5px;
            color: var(--text-primary);
        }

        .team-role {
            color: var(--text-secondary);
            margin-bottom: 15px;
            font-weight: 500;
        }

        .team-description {
            color: var(--text-secondary);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Section CTA */
        .cta-section {
            background: var(--bg-card);
            backdrop-filter: blur(20px);
            border-radius: var(--border-radius-lg);
            padding: 60px 40px;
            text-align: center;
            box-shadow: var(--shadow-card);
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin: 80px 0;
        }

        .cta-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            margin-bottom: 20px;
            background: var(--primary-gradient);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .cta-description {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 40px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 15px 30px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: white;
            box-shadow: var(--shadow-card);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
        }

        .btn-outline {
            background: transparent;
            color: var(--text-primary);
            border: 2px solid rgba(102, 126, 234, 0.3);
        }

        .btn-outline:hover {
            background: var(--primary-gradient);
            color: white;
            border-color: transparent;
        }

        /* Section Title */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 50px;
            background-clip: text;
            -webkit-background-clip: text;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 3rem;
            }

            .hero-subtitle {
                font-size: 1.2rem;
            }

            .mission-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }

            .card {
                padding: 30px 20px;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 0 15px;
            }

            .hero-content {
                padding: 0 15px;
            }

            .section {
                margin: 60px 0;
            }

            .stat-number {
                font-size: 2.5rem;
            }

            .section-title {
                font-size: 2rem;
            }
        }

        /* Animations au scroll */
        .scroll-animate {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }

        .scroll-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <!-- Arrière-plan animé -->
    <div class="animated-bg">
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
        <div class="bg-shape"></div>
    </div>

    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1 class="hero-title">À Propos d'Artisano</h1>
            <p class="hero-subtitle">Votre passerelle vers l'art authentique</p>
            <p class="hero-description">
                Nous connectons les amateurs d'art avec des artisans talentueux, 
                créant un pont entre la tradition et la modernité, 
                entre le savoir-faire ancestral et l'innovation contemporaine.
            </p>
        </div>
    </section>

    <div class="main-container">
        <!-- Statistiques -->
        <section class="section">
            <div class="stats-grid scroll-animate">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <span class="stat-number"><?= $stats['artisans'] ?>+</span>
                    <div class="stat-label">Artisans Partenaires</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-palette"></i>
                    </div>
                    <span class="stat-number"><?= $stats['oeuvres'] ?>+</span>
                    <div class="stat-label">Œuvres Disponibles</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <span class="stat-number"><?= $stats['clients'] ?>+</span>
                    <div class="stat-label">Clients Satisfaits</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span class="stat-number"><?= $stats['evenements'] ?>+</span>
                    <div class="stat-label">Événements à Venir</div>
                </div>
            </div>
        </section>

        <!-- Notre Mission -->
        <section class="section scroll-animate">
            <div class="mission-grid">
                <div class="mission-content">
                    <h2>Notre Mission</h2>
                    <p>
                        Chez Artisano, nous croyons que l'art authentique mérite d'être célébré et partagé. 
                        Notre mission est de créer une plateforme où les artisans peuvent présenter leur travail 
                        avec fierté et où les amateurs d'art peuvent découvrir des pièces uniques qui racontent une histoire.
                    </p>
                    <p>
                        Nous nous engageons à soutenir les artisans locaux en leur offrant une vitrine moderne 
                        tout en préservant l'authenticité et la qualité de leur savoir-faire traditionnel.
                    </p>
                    <p>
                        Chaque œuvre sur notre plateforme est sélectionnée avec soin, garantissant à nos clients 
                        une expérience d'achat exceptionnelle et des créations d'une qualité irréprochable.
                    </p>
                </div>
                <div class="mission-image">
                    <img src="images/about-mission.jpg" alt="Notre Mission" onerror="this.src='https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=600&h=400&fit=crop'">
                </div>
            </div>
        </section>

        <!-- Nos Valeurs -->
        <section class="section">
            <h2 class="section-title scroll-animate">Nos Valeurs</h2>
            <div class="values-grid">
                <div class="value-card scroll-animate">
                    <div class="value-icon">
                        <i class="fas fa-hand-holding-heart"></i>
                    </div>
                    <h3 class="value-title">Authenticité</h3>
                    <p class="value-description">
                        Nous valorisons l'art authentique et le savoir-faire traditionnel. 
                        Chaque pièce reflète la passion et l'expertise de son créateur.
                    </p>
                </div>
                <div class="value-card scroll-animate">
                    <div class="value-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3 class="value-title">Qualité</h3>
                    <p class="value-description">
                        Nous nous engageons à présenter uniquement des œuvres de la plus haute qualité, 
                        sélectionnées avec rigueur et passion.
                    </p>
                </div>
                <div class="value-card scroll-animate">
                    <div class="value-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3 class="value-title">Communauté</h3>
                    <p class="value-description">
                        Nous créons une communauté où artisans et amateurs d'art se rencontrent, 
                        échangent et partagent leur passion commune.
                    </p>
                </div>
                <div class="value-card scroll-animate">
                    <div class="value-icon">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <h3 class="value-title">Durabilité</h3>
                    <p class="value-description">
                        Nous promouvons un art durable et responsable, respectueux de l'environnement 
                        et des traditions artisanales.
                    </p>
                </div>
                <div class="value-card scroll-animate">
                    <div class="value-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <h3 class="value-title">Innovation</h3>
                    <p class="value-description">
                        Nous combinons tradition et modernité, offrant aux artisans des outils 
                        numériques pour présenter leur art au monde.
                    </p>
                </div>
                <div class="value-card scroll-animate">
                    <div class="value-icon">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3 class="value-title">Accessibilité</h3>
                    <p class="value-description">
                        Nous rendons l'art accessible à tous, démocratisant l'accès aux œuvres 
                        d'art authentiques et de qualité.
                    </p>
                </div>
            </div>
        </section>

        <!-- Notre Équipe (simulation) -->
        <section class="section">
    <h2 class="section-title scroll-animate">Notre Équipe</h2>
    <div class="team-grid">
        <div class="team-card scroll-animate">
            <div class="team-avatar">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="team-name">Mouhamadou Soukhouna</h3>
            <p class="team-role">Élève ingénieur et alternant Data Engineer à BPI France</p>
            <p class="team-description">
                Passionné par l'analyse de données et l'innovation technologique, Mouhamadou 
                apporte son expertise en data science pour comprendre les tendances artistiques 
                et améliorer l'expérience des utilisateurs d'Artisano.
            </p>
        </div>
        <div class="team-card scroll-animate">
            <div class="team-avatar">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="team-name">Clémence Jacquot</h3>
            <p class="team-role">Élève ingénieur</p>
            <p class="team-description">
                Sensible à l'art et au design, Clémence contribue à créer une interface 
                intuitive et esthétique. Elle veille à ce que chaque détail de la plateforme 
                reflète la beauté et l'authenticité des œuvres présentées.
            </p>
        </div>
        <div class="team-card scroll-animate">
            <div class="team-avatar">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="team-name">Leopold Crestin</h3>
            <p class="team-role">Élève ingénieur</p>
            <p class="team-description">
                Développeur talentueux, Leopold maîtrise les technologies web modernes 
                pour donner vie à Artisano. Il s'assure que la plateforme soit à la fois 
                performante et agréable à utiliser pour tous nos visiteurs.
            </p>
        </div>
        <div class="team-card scroll-animate">
            <div class="team-avatar">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="team-name">Antoine Lefevre</h3>
            <p class="team-role">Élève ingénieur</p>
            <p class="team-description">
                Expert en architecture système, Antoine conçoit une infrastructure solide 
                et sécurisée pour Artisano. Il garantit que notre plateforme peut accueillir 
                une communauté grandissante d'artisans et d'amateurs d'art.
            </p>
        </div>
        <div class="team-card scroll-animate">
            <div class="team-avatar">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h3 class="team-name">Éloise Lenoir</h3>
            <p class="team-role">Élève ingénieur</p>
            <p class="team-description">
                Créative et communicante, Éloise tisse les liens entre artisans et clients. 
                Elle organise les événements et développe les partenariats qui font d'Artisano 
                une véritable communauté unie par la passion de l'art authentique.
            </p>
        </div>
    </div>
</section> <h3 class="team-name">Eloise Lenoir</h3>
            <p class="team-role">Community Manager & Relations Artisans</p>
            <p class="team-description">
                Élève ingénieur avec une sensibilité artistique, Éloïse anime notre 
                communauté d'artisans et de clients, organise les événements et 
                développe les partenariats avec les créateurs locaux.
            </p>
        </div>
    </div>
</section>
        <!-- Call to Action -->
        <section class="cta-section scroll-animate">
            <h2 class="cta-title">Rejoignez l'Aventure Artisano</h2>
            <p class="cta-description">
                Que vous soyez artisan désireux de partager votre art ou amateur d'art 
                à la recherche de pièces uniques, Artisano est fait pour vous. 
                Rejoignez notre communauté et découvrez un monde d'authenticité et de beauté.
            </p>
            <div class="cta-buttons">
                <a href="oeuvres.php" class="btn btn-primary">
                    <i class="fas fa-palette"></i>
                    Découvrir les Œuvres
                </a>
                <a href="artisans.php" class="btn btn-outline">
                    <i class="fas fa-users"></i>
                    Rencontrer les Artisans
                </a>
                <a href="contact.php" class="btn btn-outline">
                    <i class="fas fa-envelope"></i>
                    Nous Contacter
                </a>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Animations au scroll
        document.addEventListener('DOMContentLoaded', function() {
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            // Observer tous les éléments avec la classe scroll-animate
            document.querySelectorAll('.scroll-animate').forEach(el => {
                observer.observe(el);
            });

            // Animation des compteurs
            animateCounters();

            // Parallax léger pour les formes d'arrière-plan
            window.addEventListener('scroll', handleParallax);
        });

        // Animation des compteurs numériques
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            
            counters.forEach(counter => {
                const target = parseInt(counter.textContent.replace('+', ''));
                const duration = 2000; // 2 secondes
                const increment = target / (duration / 16); // 60 FPS
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.floor(current) + '+';
                        requestAnimationFrame(updateCounter);
                    } else {
                        counter.textContent = target + '+';
                    }
                };
                
                // Démarrer l'animation quand l'élément est visible
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCounter();
                            observer.unobserve(entry.target);
                        }
                    });
                });
                
                observer.observe(counter.closest('.stat-card'));
            });
        }

        // Effet parallax léger
        function handleParallax() {
            const scrolled = window.pageYOffset;
            const shapes = document.querySelectorAll('.bg-shape');
            
            shapes.forEach((shape, index) => {
                const speed = 0.3 + (index * 0.1);
                shape.style.transform = `translateY(${scrolled * speed}px)`;
            });
        }

        // Animation de hover pour les cartes d'équipe
        document.querySelectorAll('.team-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const avatar = this.querySelector('.team-avatar');
                avatar.style.transform = 'scale(1.1) rotate(5deg)';
            });
            
            card.addEventListener('mouseleave', function() {
                const avatar = this.querySelector('.team-avatar');
                avatar.style.transform = 'scale(1) rotate(0deg)';
            });
        });

        // Animation de hover pour les cartes de valeurs
        document.querySelectorAll('.value-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const icon = this.querySelector('.value-icon');
                icon.style.transform = 'scale(1.1)';
                icon.style.boxShadow = '0 15px 30px rgba(102, 126, 234, 0.4)';
            });
            
            card.addEventListener('mouseleave', function() {
                const icon = this.querySelector('.value-icon');
                icon.style.transform = 'scale(1)';
                icon.style.boxShadow = '0 20px 40px rgba(0, 0, 0, 0.1)';
            });
        });

        // Effet de typing pour le titre hero (optionnel)
        function typeWriter(element, text, speed = 100) {
            let i = 0;
            element.innerHTML = '';
            
            function type() {
                if (i < text.length) {
                    element.innerHTML += text.charAt(i);
                    i++;
                    setTimeout(type, speed);
                }
            }
            
            type();
        }

        // Animation des boutons CTA
        document.querySelectorAll('.btn').forEach(button => {
            button.addEventListener('click', function(e) {
                // Effet ripple
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // CSS pour l'animation ripple
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(2);
                    opacity: 0;
                }
            }
            
            .team-avatar {
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .value-icon {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
        `;
        document.head.appendChild(style);

        // Lazy loading pour les images
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    imageObserver.unobserve(img);
                }
            });
        });

        images.forEach(img => imageObserver.observe(img));

        // Smooth scroll pour les liens internes
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Gestion des erreurs d'images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                // Image de fallback générique
                this.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgdmlld0JveD0iMCAwIDYwMCA0MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSI2MDAiIGhlaWdodD0iNDAwIiBmaWxsPSIjRjNGNEY2Ii8+CjxwYXRoIGQ9Ik0yNzUgMTc1SDMyNVYyMjVIMjc1VjE3NVoiIGZpbGw9IiM5Q0EzQUYiLz4KPHBhdGggZD0iTTI1MCAyMDBMMzAwIDE1MEwzNTAgMjAwTDM1MCAyNTBIMjUwVjIwMFoiIGZpbGw9IiM5Q0EzQUYiLz4KPC9zdmc+';
            });
        });

        // Performance: debounce scroll events
        let ticking = false;
        
        function requestTick() {
            if (!ticking) {
                requestAnimationFrame(handleParallax);
                ticking = true;
            }
        }
        
        window.addEventListener('scroll', () => {
            requestTick();
            ticking = false;
        });

        // Accessibilité: gestion du focus
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Tab') {
                document.body.classList.add('using-keyboard');
            }
        });

        document.addEventListener('mousedown', function() {
            document.body.classList.remove('using-keyboard');
        });

        // Ajout des styles pour l'accessibilité
        const a11yStyle = document.createElement('style');
        a11yStyle.textContent = `
            .using-keyboard *:focus {
                outline: 3px solid #667eea !important;
                outline-offset: 2px !important;
            }
            
            /* Réduire les animations pour ceux qui les préfèrent */
            @media (prefers-reduced-motion: reduce) {
                *,
                *::before,
                *::after {
                    animation-duration: 0.01ms !important;
                    animation-iteration-count: 1 !important;
                    transition-duration: 0.01ms !important;
                    scroll-behavior: auto !important;
                }
            }
            
            /* Mode sombre automatique */
            @media (prefers-color-scheme: dark) {
                :root {
                    --text-primary: #f9fafb;
                    --text-secondary: #d1d5db;
                    --text-light: #9ca3af;
                    --bg-card: rgba(31, 41, 55, 0.95);
                }
            }
        `;
        document.head.appendChild(a11yStyle);
    </script>
</body>
</html>