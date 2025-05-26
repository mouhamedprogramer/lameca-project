<?php
// Démarrer la session en tout premier
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Connexion à la base de données
require_once 'includes/conn.php';

// Vérifier si l'ID de l'œuvre est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: oeuvres.php');
    exit;
}

$idOeuvre = intval($_GET['id']);

// Récupérer les détails de l'œuvre
$sql_oeuvre = "SELECT o.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom, u.photo as artisan_photo, a.specialite 
               FROM oeuvre o 
               LEFT JOIN artisan a ON o.idArtisan = a.idArtisan 
               LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
               WHERE o.idOeuvre = ?";
$stmt = $conn->prepare($sql_oeuvre);
$stmt->bind_param("i", $idOeuvre);
$stmt->execute();
$result_oeuvre = $stmt->get_result();

if ($result_oeuvre->num_rows === 0) {
    header('Location: oeuvres.php');
    exit;
}

$oeuvre = $result_oeuvre->fetch_assoc();

// Récupérer toutes les photos de l'œuvre
$sql_photos = "SELECT * FROM Photooeuvre WHERE idOeuvre = ? ORDER BY idPhoto ASC";
$stmt = $conn->prepare($sql_photos);
$stmt->bind_param("i", $idOeuvre);
$stmt->execute();
$result_photos = $stmt->get_result();
$photos = [];

if ($result_photos->num_rows > 0) {
    while ($row = $result_photos->fetch_assoc()) {
        $photos[] = $row;
    }
}

// Fonction pour formater les prix
function formaterPrix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}

// Titre de la page
$pageTitle = htmlspecialchars($oeuvre['titre']) . " - Artisano";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Font Awesome pour les icônes -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        /* ============================================
           DESIGN ULTRA-MODERNE FLUIDE
           ============================================ */

        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --secondary: #ec4899;
            --accent: #06b6d4;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            
            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-dark: #0f172a;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
            
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);
            
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 25px rgba(0, 0, 0, 0.15);
            --shadow-xl: 0 25px 50px rgba(0, 0, 0, 0.25);
            --shadow-glow: 0 0 40px rgba(99, 102, 241, 0.4);
            
            --border-radius: 20px;
            --border-radius-lg: 30px;
            --border-radius-xl: 40px;
            
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-fast: all 0.15s ease;
            --transition-bounce: all 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Inter', sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Arrière-plan animé avec particules */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.6);
            border-radius: 50%;
            animation: float 15s infinite linear;
        }

        @keyframes float {
            0% {
                transform: translateY(100vh) rotate(0deg);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-10vh) rotate(360deg);
                opacity: 0;
            }
        }

        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
            backdrop-filter: blur(10px);
            animation: shapeFloat 20s ease-in-out infinite;
        }

        .shape:nth-child(1) {
            width: 200px;
            height: 200px;
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }

        .shape:nth-child(2) {
            width: 150px;
            height: 150px;
            top: 60%;
            right: 15%;
            animation-delay: -5s;
        }

        .shape:nth-child(3) {
            width: 100px;
            height: 100px;
            bottom: 20%;
            left: 20%;
            animation-delay: -10s;
        }

        @keyframes shapeFloat {
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

        /* Container principal avec glassmorphism */
        .main-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
            position: relative;
            z-index: 1;
        }

        /* Breadcrumb moderne */
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 40px;
            padding: 15px 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-xl);
            font-size: 14px;
            font-weight: 500;
        }

        .breadcrumb a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 15px;
            transition: var(--transition);
            background: rgba(255, 255, 255, 0.1);
        }

        .breadcrumb a:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-2px);
        }

        .breadcrumb-separator {
            color: rgba(255, 255, 255, 0.5);
        }

        .breadcrumb-current {
            color: white;
            font-weight: 600;
        }

        /* Carte principale avec effet de profondeur */
        .artwork-card {
            background: var(--glass-bg);
            backdrop-filter: blur(30px);
            border: 1px solid var(--glass-border);
            border-radius: var(--border-radius-xl);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            animation: slideUp 0.8s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(60px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .artwork-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 700px;
        }

        /* Section galerie */
        .gallery-section {
            padding: 50px;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(248, 250, 252, 0.9));
            position: relative;
        }

        .gallery-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 30% 70%, rgba(99, 102, 241, 0.1), transparent 60%);
            pointer-events: none;
        }

        .main-image-container {
            position: relative;
            margin-bottom: 30px;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            transition: var(--transition);
            cursor: pointer;
        }

        .main-image-container:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: var(--shadow-xl);
        }

        .main-image {
            width: 100%;
            height: 450px;
            object-fit: cover;
            transition: var(--transition);
        }

        .main-image-container:hover .main-image {
            transform: scale(1.1);
        }

        .image-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(99, 102, 241, 0.3), rgba(236, 72, 153, 0.3));
            opacity: 0;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .main-image-container:hover .image-overlay {
            opacity: 1;
        }

        .zoom-icon {
            color: white;
            font-size: 2rem;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid rgba(255, 255, 255, 0.3);
            transition: var(--transition-bounce);
        }

        .main-image-container:hover .zoom-icon {
            transform: scale(1.2);
        }

        /* Miniatures */
        .thumbnails {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            padding: 10px 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .thumbnails::-webkit-scrollbar {
            display: none;
        }

        .thumbnail {
            flex-shrink: 0;
            width: 80px;
            height: 80px;
            border-radius: 15px;
            overflow: hidden;
            cursor: pointer;
            border: 3px solid transparent;
            transition: var(--transition);
            position: relative;
            box-shadow: var(--shadow-md);
        }

        .thumbnail:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: var(--shadow-lg);
        }

        .thumbnail.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.3);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: var(--transition);
        }

        .thumbnail:hover img {
            transform: scale(1.2);
        }

        /* Section informations */
        .info-section {
            padding: 50px;
            background: white;
            position: relative;
            overflow-y: auto;
        }

        .artwork-title {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: titleGlow 3s ease-in-out infinite alternate;
        }

        @keyframes titleGlow {
            0% {
                filter: brightness(1);
            }
            100% {
                filter: brightness(1.2);
            }
        }

        .availability-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
            backdrop-filter: blur(10px);
        }

        .availability-badge.available {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .availability-badge.unavailable {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.5);
                opacity: 0.7;
            }
        }

        /* Prix avec animation */
        .price-container {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            padding: 30px;
            border-radius: var(--border-radius-lg);
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(99, 102, 241, 0.1);
        }

        .price-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            animation: priceShimmer 3s infinite;
        }

        @keyframes priceShimmer {
            0% {
                left: -100%;
            }
            100% {
                left: 100%;
            }
        }

        .price-label {
            font-size: 12px;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 10px;
            font-weight: 600;
        }

        .price {
            font-size: 2.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--success), #059669);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }

        /* Carte artisan premium */
        .artist-card {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(99, 102, 241, 0.1);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .artist-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .artist-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, rgba(99, 102, 241, 0.1), transparent);
            animation: artistRotate 10s linear infinite;
            opacity: 0;
            transition: var(--transition);
        }

        .artist-card:hover::before {
            opacity: 1;
        }

        @keyframes artistRotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .artist-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .artist-avatar {
            position: relative;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: var(--shadow-md);
        }

        .artist-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .artist-avatar::after {
            content: '';
            position: absolute;
            top: -4px;
            left: -4px;
            right: -4px;
            bottom: -4px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--secondary));
            z-index: -1;
            animation: avatarGlow 3s ease-in-out infinite;
        }

        @keyframes avatarGlow {
            0%, 100% {
                opacity: 0.5;
                transform: scale(1);
            }
            50% {
                opacity: 1;
                transform: scale(1.1);
            }
        }

        .artist-info h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 5px;
        }

        .artist-specialty {
            color: var(--primary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .rating {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .stars {
            display: flex;
            gap: 2px;
        }

        .star {
            color: #fbbf24;
            font-size: 14px;
            animation: starTwinkle 2s ease-in-out infinite;
        }

        .star:nth-child(1) { animation-delay: 0s; }
        .star:nth-child(2) { animation-delay: 0.2s; }
        .star:nth-child(3) { animation-delay: 0.4s; }
        .star:nth-child(4) { animation-delay: 0.6s; }
        .star:nth-child(5) { animation-delay: 0.8s; }

        @keyframes starTwinkle {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        .rating-text {
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 500;
        }

        /* Sections de contenu */
        .content-section {
            margin-bottom: 30px;
            opacity: 0;
            transform: translateY(20px);
            transition: var(--transition);
        }

        .content-section.visible {
            opacity: 1;
            transform: translateY(0);
        }

        .section-title {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 15px;
        }

        .section-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }

        .section-content {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            padding: 25px;
            border-radius: var(--border-radius);
            border-left: 4px solid var(--primary);
            line-height: 1.7;
            color: var(--text-secondary);
        }

        /* Boutons d'action avec effets magiques */
        .action-buttons {
            display: flex;
            gap: 15px;
            margin: 40px 0;
        }

        .btn {
            flex: 1;
            padding: 18px 30px;
            border: none;
            border-radius: var(--border-radius);
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
            transition: var(--transition);
            text-align: center;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: var(--transition);
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: var(--shadow-md);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-glow);
        }

        .btn-secondary {
            background: white;
            color: var(--text-secondary);
            border: 2px solid #e2e8f0;
            box-shadow: var(--shadow-sm);
        }

        .btn-secondary:hover {
            background: #f8fafc;
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-2px);
        }

        .btn-secondary.active {
            background: linear-gradient(135deg, var(--secondary), #db2777);
            color: white;
            border-color: transparent;
        }

        /* Section contact */
        .contact-section {
            background: linear-gradient(135deg, #f8fafc, #ffffff);
            border-radius: var(--border-radius-lg);
            padding: 30px;
            border: 1px solid rgba(99, 102, 241, 0.1);
            text-align: center;
        }

        .contact-description {
            color: var(--text-secondary);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .contact-btn {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: white;
            padding: 15px 30px;
            border-radius: var(--border-radius);
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .contact-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .contact-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #4b5563, #374151);
            opacity: 0;
            transition: var(--transition);
        }

        .contact-btn:hover::before {
            opacity: 1;
        }

        .contact-btn span {
            position: relative;
            z-index: 1;
        }

        /* Modal image */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: var(--transition);
        }

        .modal.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
        }

        .modal-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            transition: var(--transition);
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.1);
        }

        .modal-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: var(--transition);
        }

        .modal-nav:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-50%) scale(1.1);
        }

        .modal-prev {
            left: 20px;
        }

        .modal-next {
            right: 20px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .artwork-content {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 20px 15px;
            }

            .gallery-section,
            .info-section {
                padding: 30px 25px;
            }

            .main-image {
                height: 350px;
            }

            .artwork-title {
                font-size: 2rem;
            }

            .price {
                font-size: 2rem;
            }

            .artist-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .thumbnails {
                gap: 10px;
            }

            .thumbnail {
                width: 60px;
                height: 60px;
            }
        }

        @media (max-width: 480px) {
            .main-container {
                padding: 15px 10px;
            }

            .gallery-section,
            .info-section {
                padding: 20px 15px;
            }

            .main-image {
                height: 280px;
            }

            .artwork-title {
                font-size: 1.8rem;
            }

            .price {
                font-size: 1.8rem;
            }

            .artist-card,
            .price-container,
            .contact-section {
                padding: 20px;
            }

            .btn {
                padding: 15px 20px;
                font-size: 0.9rem;
            }

            .thumbnail {
                width: 50px;
                height: 50px;
            }
        }

        /* Animations de scroll */
        .scroll-animate {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .scroll-animate.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Effet de loading */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        /* Effet de succès */
        .success {
            background: linear-gradient(135deg, var(--success), #059669) !important;
        }

        .success-check {
            display: inline-block;
            animation: checkmark 0.6s ease;
        }

        @keyframes checkmark {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.3);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Optimisations performance */
        .gpu-accelerated {
            will-change: transform;
            transform: translateZ(0);
            backface-visibility: hidden;
        }
    </style>
</head>
<body>
    <!-- Arrière-plan animé -->
    <div class="animated-bg">
        <div class="floating-shapes">
            <div class="shape"></div>
            <div class="shape"></div>
            <div class="shape"></div>
        </div>
    </div>

    <?php include 'includes/header.php'; ?>

    <div class="main-container">
        <!-- Breadcrumb moderne -->
        <nav class="breadcrumb">
            <a href="index.php">
                <i class="fas fa-home"></i>
                Accueil
            </a>
            <span class="breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </span>
            <a href="oeuvres.php">
                <i class="fas fa-palette"></i>
                Œuvres
            </a>
            <span class="breadcrumb-separator">
                <i class="fas fa-chevron-right"></i>
            </span>
            <span class="breadcrumb-current">
                <?php echo htmlspecialchars($oeuvre['titre']); ?>
            </span>
        </nav>

        <!-- Carte principale -->
        <div class="artwork-card">
            <div class="artwork-content">
                <!-- Section Galerie -->
                <div class="gallery-section">
                    <?php if (count($photos) > 0): ?>
                        <div class="main-image-container" onclick="openModal()">
                            <img id="main-image" src="../<?php echo $photos[0]['url']; ?>" alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>" class="main-image">
                            <div class="image-overlay">
                                <div class="zoom-icon">
                                    <i class="fas fa-search-plus"></i>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (count($photos) > 1): ?>
                            <div class="thumbnails">
                                <?php foreach ($photos as $index => $photo): ?>
                                    <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
                                         onclick="changeMainImage('../<?php echo $photo['url']; ?>', this, <?php echo $index; ?>)">
                                        <img src="../<?php echo $photo['url']; ?>" alt="Vue <?php echo $index + 1; ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="main-image-container">
                            <img src="../../images/oeuvre-placeholder.jpg" alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>" class="main-image">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Section Informations -->
                <div class="info-section">
                    <h1 class="artwork-title"><?php echo htmlspecialchars($oeuvre['titre']); ?></h1>
                    
                    <div class="availability-badge <?php echo $oeuvre['disponibilite'] ? 'available' : 'unavailable'; ?>">
                        <div class="status-dot"></div>
                        <?php echo $oeuvre['disponibilite'] ? 'Disponible' : 'Indisponible'; ?>
                    </div>

                    <!-- Prix -->
                    <div class="price-container">
                        <div class="price-label">Prix</div>
                        <div class="price"><?php echo formaterPrix($oeuvre['prix']); ?></div>
                    </div>

                    <!-- Carte artisan -->
                    <div class="artist-card scroll-animate">
                        <div class="artist-content">
                            <div class="artist-avatar">
                                <?php
                                $artisan_photo = !empty($oeuvre['artisan_photo']) ? '../images/' . $oeuvre['artisan_photo'] : '../images/profile-placeholder.jpg';
                                ?>
                                <img src="<?php echo $artisan_photo; ?>" alt="<?php echo htmlspecialchars($oeuvre['artisan_prenom'] . ' ' . $oeuvre['artisan_nom']); ?>">
                            </div>
                            <div class="artist-info">
                                <h3><?php echo htmlspecialchars($oeuvre['artisan_prenom'] . ' ' . $oeuvre['artisan_nom']); ?></h3>
                                <p class="artist-specialty"><?php echo htmlspecialchars($oeuvre['specialite']); ?></p>
                                <div class="rating">
                                    <div class="stars">
                                        <i class="fas fa-star star"></i>
                                        <i class="fas fa-star star"></i>
                                        <i class="fas fa-star star"></i>
                                        <i class="fas fa-star star"></i>
                                        <i class="fas fa-star star"></i>
                                    </div>
                                    <span class="rating-text">4.9 (127 avis)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="content-section scroll-animate">
                        <h2 class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-align-left"></i>
                            </div>
                            Description
                        </h2>
                        <div class="section-content">
                            <?php echo nl2br(htmlspecialchars($oeuvre['description'])); ?>
                        </div>
                    </div>

                    <?php if (!empty($oeuvre['caracteristiques'])): ?>
                    <div class="content-section scroll-animate">
                        <h2 class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-cog"></i>
                            </div>
                            Caractéristiques
                        </h2>
                        <div class="section-content">
                            <?php echo nl2br(htmlspecialchars($oeuvre['caracteristiques'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Boutons d'action -->
                    <?php if ($oeuvre['disponibilite']): ?>
                    <div class="action-buttons scroll-animate">
                        <button class="btn btn-primary" onclick="addToCart(<?php echo $oeuvre['idOeuvre']; ?>, this)">
                            <i class="fas fa-shopping-cart"></i>
                            <span>Ajouter au panier</span>
                        </button>
                        
                        <button class="btn btn-secondary" onclick="toggleWishlist(<?php echo $oeuvre['idOeuvre']; ?>, this)">
                            <i class="far fa-heart"></i>
                            <span>Favoris</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Contact artisan -->
                    <div class="contact-section scroll-animate">
                        <h2 class="section-title">
                            <div class="section-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            Contacter l'artisan
                        </h2>
                        <p class="contact-description">
                            Une question sur cette œuvre ? Contactez directement l'artisan pour obtenir plus d'informations.
                        </p>
                        <a href="contact-artisan.php?id=<?php echo $oeuvre['idArtisan']; ?>" class="contact-btn">
                            <i class="fas fa-paper-plane"></i>
                            <span>Envoyer un message</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal image -->
    <div class="modal" id="imageModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
            <img id="modal-image" src="" alt="" class="modal-image">
            <?php if (count($photos) > 1): ?>
                <button class="modal-nav modal-prev" onclick="previousImage()">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button class="modal-nav modal-next" onclick="nextImage()">
                    <i class="fas fa-chevron-right"></i>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Variables globales
        let currentImageIndex = 0;
        const images = [
            <?php foreach ($photos as $index => $photo): ?>
            "../<?php echo $photo['url']; ?>"<?php echo $index < count($photos) - 1 ? ',' : ''; ?>
            <?php endforeach; ?>
        ];

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            initParticles();
            initScrollAnimations();
            preloadImages();
        });

        // Système de particules
        function initParticles() {
            const bg = document.querySelector('.animated-bg');
            const particleCount = window.innerWidth < 768 ? 30 : 50;
            
            for (let i = 0; i < particleCount; i++) {
                createParticle(bg);
            }
        }

        function createParticle(container) {
            const particle = document.createElement('div');
            particle.className = 'particle';
            
            const size = Math.random() * 4 + 2;
            const x = Math.random() * window.innerWidth;
            const duration = Math.random() * 15 + 10;
            const delay = Math.random() * 20;
            
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = x + 'px';
            particle.style.animationDuration = duration + 's';
            particle.style.animationDelay = delay + 's';
            
            container.appendChild(particle);
        }

        // Animations au scroll
        function initScrollAnimations() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            });

            document.querySelectorAll('.scroll-animate').forEach(el => {
                observer.observe(el);
            });
        }

        // Préchargement des images
        function preloadImages() {
            images.forEach(src => {
                const img = new Image();
                img.src = src;
            });
        }

        // Galerie d'images
        function changeMainImage(src, thumbnail, index) {
            const mainImage = document.getElementById('main-image');
            
            // Animation de transition
            mainImage.style.transform = 'scale(0.9)';
            mainImage.style.opacity = '0.7';
            
            setTimeout(() => {
                mainImage.src = src;
                currentImageIndex = index;
                mainImage.style.transform = 'scale(1)';
                mainImage.style.opacity = '1';
                
                // Mettre à jour les miniatures actives
                document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                thumbnail.classList.add('active');
            }, 150);
        }

        // Modal d'images
        function openModal() {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modal-image');
            const mainImage = document.getElementById('main-image');
            
            modalImage.src = mainImage.src;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
        }

        function previousImage() {
            if (images.length <= 1) return;
            
            currentImageIndex = currentImageIndex > 0 ? currentImageIndex - 1 : images.length - 1;
            updateModalImage();
        }

        function nextImage() {
            if (images.length <= 1) return;
            
            currentImageIndex = currentImageIndex < images.length - 1 ? currentImageIndex + 1 : 0;
            updateModalImage();
        }

        function updateModalImage() {
            const modalImage = document.getElementById('modal-image');
            const mainImage = document.getElementById('main-image');
            
            modalImage.style.transform = 'scale(0.8)';
            modalImage.style.opacity = '0.5';
            
            setTimeout(() => {
                modalImage.src = images[currentImageIndex];
                mainImage.src = images[currentImageIndex];
                modalImage.style.transform = 'scale(1)';
                modalImage.style.opacity = '1';
                
                // Mettre à jour les miniatures
                document.querySelectorAll('.thumbnail').forEach((thumb, index) => {
                    thumb.classList.toggle('active', index === currentImageIndex);
                });
            }, 150);
        }

        // Actions des boutons
        function addToCart(idOeuvre, button) {
            const icon = button.querySelector('i');
            const text = button.querySelector('span');
            
            // État de chargement
            button.classList.add('loading');
            icon.className = 'spinner';
            text.textContent = 'Ajout en cours...';
            
            // Simulation d'ajout au panier
            setTimeout(() => {
                button.classList.remove('loading');
                button.classList.add('success');
                icon.className = 'fas fa-check success-check';
                text.textContent = 'Ajouté au panier !';
                
                // Animation de particules de succès
                createSuccessParticles(button);
                
                // Retour à l'état normal après 3 secondes
                setTimeout(() => {
                    button.classList.remove('success');
                    icon.className = 'fas fa-shopping-cart';
                    text.textContent = 'Ajouter au panier';
                }, 3000);
            }, 1500);
        }

        function toggleWishlist(idOeuvre, button) {
            const icon = button.querySelector('i');
            const text = button.querySelector('span');
            const isCurrentlyActive = button.classList.contains('active');
            
            // Animation du bouton
            const originalContent = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Traitement...</span>';
            
            // Déterminer l'action
            const action = isCurrentlyActive ? 'remove' : 'add';
            
            fetch('actions/wishlist.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    idOeuvre: parseInt(idOeuvre),
                    action: action
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    button.classList.toggle('active');
                    
                    if (button.classList.contains('active')) {
                        icon.className = 'fas fa-heart';
                        text.textContent = 'Dans les favoris';
                        
                        // Animation de coeur
                        icon.style.animation = 'heartBeat 0.6s ease';
                        setTimeout(() => {
                            icon.style.animation = '';
                        }, 600);
                        
                        showNotification('Œuvre ajoutée à vos favoris !', 'success');
                    } else {
                        icon.className = 'far fa-heart';
                        text.textContent = 'Favoris';
                        showNotification('Œuvre retirée de vos favoris', 'success');
                    }
                    
                    // Mettre à jour le compteur dans le header
                    if (window.updateBadgeCount) {
                        fetch('actions/get-wishlist-count.php')
                            .then(response => response.json())
                            .then(countData => {
                                if (countData.success) {
                                    window.updateBadgeCount('badge-wishlist', countData.count);
                                }
                            });
                    }
                } else {
                    showNotification(data.message || 'Erreur lors de la modification des favoris', 'error');
                    button.innerHTML = originalContent;
                }
            })
            .catch(error => {
                console.error('Erreur wishlist:', error);
                showNotification('Erreur de connexion', 'error');
                button.innerHTML = originalContent;
            })
            .finally(() => {
                button.disabled = false;
            });
        }

        // Effet de particules de succès
        function createSuccessParticles(element) {
            const rect = element.getBoundingClientRect();
            const particleCount = 12;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.style.position = 'fixed';
                particle.style.left = (rect.left + rect.width / 2) + 'px';
                particle.style.top = (rect.top + rect.height / 2) + 'px';
                particle.style.width = '4px';
                particle.style.height = '4px';
                particle.style.backgroundColor = '#10b981';
                particle.style.borderRadius = '50%';
                particle.style.pointerEvents = 'none';
                particle.style.zIndex = '9999';
                
                const angle = (i / particleCount) * Math.PI * 2;
                const velocity = 100;
                const vx = Math.cos(angle) * velocity;
                const vy = Math.sin(angle) * velocity;
                
                particle.style.animation = `particleExplode 0.8s ease-out forwards`;
                particle.style.setProperty('--vx', vx + 'px');
                particle.style.setProperty('--vy', vy + 'px');
                
                document.body.appendChild(particle);
                
                setTimeout(() => {
                    particle.remove();
                }, 800);
            }
        }

        // CSS dynamique pour l'animation des particules
        const style = document.createElement('style');
        style.textContent = `
            @keyframes particleExplode {
                0% {
                    transform: translate(0, 0) scale(1);
                    opacity: 1;
                }
                100% {
                    transform: translate(var(--vx), var(--vy)) scale(0);
                    opacity: 0;
                }
            }
            
            @keyframes heartBeat {
                0%, 100% { transform: scale(1); }
                25% { transform: scale(1.3); }
                50% { transform: scale(1.1); }
                75% { transform: scale(1.2); }
            }
        `;
        document.head.appendChild(style);

        // Navigation clavier
        document.addEventListener('keydown', function(e) {
            const modal = document.getElementById('imageModal');
            if (modal.classList.contains('active')) {
                if (e.key === 'Escape') {
                    closeModal();
                } else if (e.key === 'ArrowLeft') {
                    previousImage();
                } else if (e.key === 'ArrowRight') {
                    nextImage();
                }
            }
        });

        // Parallax sur le scroll
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const shapes = document.querySelectorAll('.shape');
            
            shapes.forEach((shape, index) => {
                const speed = 0.5 + (index * 0.2);
                shape.style.transform = `translateY(${scrolled * speed}px)`;
            });
        });

        // Optimisation des performances
        window.addEventListener('resize', function() {
            // Recréer les particules lors du redimensionnement
            clearTimeout(this.resizeTimeout);
            this.resizeTimeout = setTimeout(() => {
                const bg = document.querySelector('.animated-bg');
                bg.querySelectorAll('.particle').forEach(p => p.remove());
                initParticles();
            }, 250);
        });

        // Préchargement et lazy loading des images
        function lazyLoadImages() {
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
        }

        // Gestion des erreurs d'images
        document.querySelectorAll('img').forEach(img => {
            img.addEventListener('error', function() {
                this.src = '../../images/placeholder.jpg';
            });
        });

        // Amélioration de l'accessibilité
        document.addEventListener('focusin', function(e) {
            if (e.target.matches('.btn, .thumbnail, .modal-close, .modal-nav')) {
                e.target.style.outline = '3px solid #6366f1';
                e.target.style.outlineOffset = '3px';
            }
        });

        document.addEventListener('focusout', function(e) {
            if (e.target.matches('.btn, .thumbnail, .modal-close, .modal-nav')) {
                e.target.style.outline = '';
                e.target.style.outlineOffset = '';
            }
        });

        // Messages de feedback pour les actions
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: ${type === 'success' ? 'linear-gradient(135deg, #10b981, #059669)' : 'linear-gradient(135deg, #ef4444, #dc2626)'};
                color: white;
                border-radius: 10px;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
                z-index: 10000;
                transform: translateX(100%);
                transition: transform 0.3s ease;
                font-weight: 600;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 100);
            
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>