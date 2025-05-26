<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    header('Location: connexion.php');
    exit;
}

// Vérifier si l'ID de l'artisan est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: artisans.php');
    exit;
}

$idArtisan = intval($_GET['id']);
$idClient = $_SESSION['idUtilisateur'];

// Récupérer les informations de l'artisan
$sql_artisan = "SELECT a.*, u.nom, u.prenom, u.photo, u.ville, u.pays 
                FROM Artisan a 
                JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur 
                WHERE a.idArtisan = ?";

$stmt = $conn->prepare($sql_artisan);
$stmt->bind_param("i", $idArtisan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: artisans.php');
    exit;
}

$artisan = $result->fetch_assoc();

// Récupérer les œuvres de l'artisan pour suggestions
$sql_oeuvres = "SELECT o.idOeuvre, o.titre, o.prix,
                (SELECT p.url FROM Photooeuvre p WHERE p.idOeuvre = o.idOeuvre ORDER BY p.idPhoto ASC LIMIT 1) as photo_principale
                FROM Oeuvre o 
                WHERE o.idArtisan = ? AND o.disponibilite = 1
                ORDER BY o.datePublication DESC 
                LIMIT 6";

$stmt_oeuvres = $conn->prepare($sql_oeuvres);
$stmt_oeuvres->bind_param("i", $idArtisan);
$stmt_oeuvres->execute();
$result_oeuvres = $stmt_oeuvres->get_result();

// Traitement du formulaire
$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sujet = htmlspecialchars(trim($_POST['sujet'] ?? ''));
    $contenu = htmlspecialchars(trim($_POST['contenu'] ?? ''));
    $oeuvre_id = !empty($_POST['oeuvre_id']) ? intval($_POST['oeuvre_id']) : null;
    
    // Validation
    $errors = [];
    
    if (empty($sujet)) $errors[] = "Le sujet est requis";
    if (empty($contenu)) $errors[] = "Le message est requis";
    if (strlen($contenu) < 10) $errors[] = "Le message doit contenir au moins 10 caractères";
    
    if (empty($errors)) {
        // Ajouter référence à l'œuvre dans le contenu si spécifiée
        if ($oeuvre_id) {
            $sql_oeuvre = "SELECT titre FROM Oeuvre WHERE idOeuvre = ? AND idArtisan = ?";
            $stmt_oeuvre = $conn->prepare($sql_oeuvre);
            $stmt_oeuvre->bind_param("ii", $oeuvre_id, $idArtisan);
            $stmt_oeuvre->execute();
            $result_oeuvre = $stmt_oeuvre->get_result();
            
            if ($result_oeuvre->num_rows > 0) {
                $oeuvre = $result_oeuvre->fetch_assoc();
                $contenu = "Concernant l'œuvre : \"" . $oeuvre['titre'] . "\"\n\n" . $contenu;
            }
        }
        
        // Insérer le message
        $sql_insert = "INSERT INTO Message (idEmetteur, idRecepteur, contenu) VALUES (?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $message_complet = "Sujet: " . $sujet . "\n\n" . $contenu;
        $stmt_insert->bind_param("iis", $idClient, $idArtisan, $message_complet);
        
        if ($stmt_insert->execute()) {
            $message_sent = true;
        } else {
            $error_message = "Erreur lors de l'envoi du message. Veuillez réessayer.";
        }
    } else {
        $error_message = implode('<br>', $errors);
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacter <?= htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']) ?> | Artisano</title>
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .contact-header {
            text-align: center;
            margin-bottom: 2rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .contact-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .contact-subtitle {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .contact-main {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .artisan-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            height: fit-content;
        }

        .artisan-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .artisan-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #667eea;
        }

        .artisan-info h3 {
            color: #2c3e50;
            font-size: 1.3rem;
            margin-bottom: 0.3rem;
        }

        .artisan-speciality {
            color: #667eea;
            font-weight: 500;
            margin-bottom: 0.3rem;
        }

        .artisan-location {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .artisan-works {
            margin-top: 1.5rem;
        }

        .artisan-works h4 {
            color: #2c3e50;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .works-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .work-item {
            cursor: pointer;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .work-item:hover {
            transform: translateY(-2px);
            border-color: #667eea;
        }

        .work-item.selected {
            border-color: #667eea;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .work-item img {
            width: 100%;
            height: 80px;
            object-fit: cover;
        }

        .work-info {
            padding: 0.8rem;
            background: white;
        }

        .work-info h5 {
            font-size: 0.9rem;
            color: #2c3e50;
            margin-bottom: 0.3rem;
            line-height: 1.2;
        }

        .work-price {
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .contact-form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .form-title {
            color: #2c3e50;
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: slideDown 0.3s ease-out;
        }

        .alert-success {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
            border: none;
        }

        .alert-error {
            background: linear-gradient(135deg, #e74c3c, #ec7063);
            color: white;
            border: none;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #2c3e50;
            font-size: 0.9rem;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            background: white;
            outline: none;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-1px);
        }

        .form-textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-button {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-family: 'Poppins', sans-serif;
            position: relative;
            overflow: hidden;
        }

        .form-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
            transition: all 0.3s ease;
        }

        .form-button:hover::before {
            left: 0;
        }

        .form-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .form-button:active {
            transform: translateY(0);
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: white;
            transform: translateX(-5px);
        }

        .char-counter {
            text-align: right;
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 0.3rem;
        }

        .char-counter.warning {
            color: #f39c12;
        }

        .char-counter.danger {
            color: #e74c3c;
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

        @keyframes slideDown {
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
                font-size: 2rem;
            }

            .contact-main {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .artisan-card,
            .contact-form-container {
                padding: 1.5rem;
            }

            .works-grid {
                grid-template-columns: 1fr;
            }

            .artisan-profile {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .contact-title {
                font-size: 1.8rem;
            }

            .form-title {
                font-size: 1.3rem;
            }

            .artisan-card,
            .contact-form-container {
                padding: 1rem;
            }
        }
    </style>
</head>
<body><br><br><br>
    <div class="contact-container">
        <a href="profil-artisan.php?id=<?= $idArtisan ?>" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Retour au profil
        </a>

        <div class="contact-header">
            <h1 class="contact-title">Contacter l'artisan</h1>
            <p class="contact-subtitle">
                Envoyez un message à <?= htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']) ?>
            </p>
        </div>

        <div class="contact-main">
            <!-- Informations artisan -->
            <div class="artisan-card">
                <div class="artisan-profile">
                    <img src="<?= !empty($artisan['photo'])? '../images/' . $artisan['photo'] : 'images/profile-placeholder.jpg' ?>" 
                         alt="<?= htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']) ?>" 
                         class="artisan-avatar">
                    <div class="artisan-info">
                        <h3><?= htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']) ?></h3>
                        <div class="artisan-speciality"><?= htmlspecialchars($artisan['specialite']) ?></div>
                        <div class="artisan-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($artisan['ville'] . ', ' . $artisan['pays']) ?>
                        </div>
                    </div>
                </div>

                <?php if ($result_oeuvres->num_rows > 0): ?>
                <div class="artisan-works">
                    <h4><i class="fas fa-palette"></i> Œuvres récentes</h4>
                    <div class="works-grid">
                        <?php while ($oeuvre = $result_oeuvres->fetch_assoc()): ?>
                            <div class="work-item" onclick="selectWork(<?= $oeuvre['idOeuvre'] ?>, '<?= htmlspecialchars($oeuvre['titre']) ?>')">
                                <img src="<?= !empty($oeuvre['photo_principale']) ? '../'.$oeuvre['photo_principale'] : 'images/oeuvre-placeholder.jpg' ?>" 
                                     alt="<?= htmlspecialchars($oeuvre['titre']) ?>">
                                <div class="work-info">
                                    <h5><?= htmlspecialchars($oeuvre['titre']) ?></h5>
                                    <div class="work-price"><?= number_format($oeuvre['prix'], 2, ',', ' ') ?> €</div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <p style="font-size: 0.9rem; color: #6c757d; margin-top: 1rem; text-align: center;">
                        <i class="fas fa-info-circle"></i> 
                        Cliquez sur une œuvre pour l'associer à votre message
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Formulaire de contact -->
            <div class="contact-form-container">
                <h2 class="form-title">
                    <i class="fas fa-envelope"></i>
                    Votre message
                </h2>

                <?php if ($message_sent): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        Votre message a été envoyé avec succès ! L'artisan vous répondra bientôt.
                    </div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-triangle"></i>
                        <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="" id="contactForm">
                    <input type="hidden" name="oeuvre_id" id="oeuvre_id" value="">
                    
                    <div class="form-group">
                        <label for="sujet" class="form-label">Sujet *</label>
                        <input type="text" id="sujet" name="sujet" class="form-input" 
                               value="<?= isset($_POST['sujet']) ? htmlspecialchars($_POST['sujet']) : '' ?>" 
                               placeholder="Objet de votre message"
                               required>
                    </div>

                    <div id="selected-work" style="display: none; background: rgba(102, 126, 234, 0.1); padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; color: #2c3e50;">
                            <i class="fas fa-palette"></i>
                            <span>Œuvre sélectionnée : <strong id="selected-work-title"></strong></span>
                            <button type="button" onclick="unselectWork()" style="background: none; border: none; color: #e74c3c; cursor: pointer; margin-left: auto;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contenu" class="form-label">Message *</label>
                        <textarea id="contenu" name="contenu" class="form-textarea" 
                                  placeholder="Écrivez votre message ici... (minimum 10 caractères)"
                                  required><?= isset($_POST['contenu']) ? htmlspecialchars($_POST['contenu']) : '' ?></textarea>
                        <div class="char-counter" id="char-counter">0 caractères</div>
                    </div>

                    <button type="submit" class="form-button">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer le message
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        let selectedWorkId = null;

        // Sélection d'une œuvre
        function selectWork(id, title) {
            // Retirer la sélection précédente
            document.querySelectorAll('.work-item').forEach(item => {
                item.classList.remove('selected');
            });

            // Sélectionner la nouvelle œuvre
            event.currentTarget.classList.add('selected');
            selectedWorkId = id;
            
            // Mettre à jour les champs
            document.getElementById('oeuvre_id').value = id;
            document.getElementById('selected-work-title').textContent = title;
            document.getElementById('selected-work').style.display = 'block';

            // Mettre à jour le sujet si vide
            const sujetInput = document.getElementById('sujet');
            if (!sujetInput.value.trim()) {
                sujetInput.value = `Question concernant "${title}"`;
            }
        }

        // Désélectionner une œuvre
        function unselectWork() {
            document.querySelectorAll('.work-item').forEach(item => {
                item.classList.remove('selected');
            });
            
            selectedWorkId = null;
            document.getElementById('oeuvre_id').value = '';
            document.getElementById('selected-work').style.display = 'none';
        }

        // Compteur de caractères
        const contenuTextarea = document.getElementById('contenu');
        const charCounter = document.getElementById('char-counter');

        function updateCharCounter() {
            const length = contenuTextarea.value.length;
            charCounter.textContent = `${length} caractères`;
            
            if (length < 10) {
                charCounter.className = 'char-counter danger';
            } else if (length > 500) {
                charCounter.className = 'char-counter warning';
            } else {
                charCounter.className = 'char-counter';
            }
        }

        contenuTextarea.addEventListener('input', updateCharCounter);
        updateCharCounter(); // Appel initial

        // Auto-resize du textarea
        contenuTextarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Validation du formulaire
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const sujet = document.getElementById('sujet').value.trim();
            const contenu = document.getElementById('contenu').value.trim();
            
            if (!sujet) {
                e.preventDefault();
                alert('Veuillez saisir un sujet pour votre message.');
                document.getElementById('sujet').focus();
                return;
            }
            
            if (!contenu || contenu.length < 10) {
                e.preventDefault();
                alert('Votre message doit contenir au moins 10 caractères.');
                document.getElementById('contenu').focus();
                return;
            }
        });

        // Messages prédéfinis selon le type de demande
        const messageTemplates = {
            question: "Bonjour,\n\nJ'aimerais avoir des informations concernant cette œuvre :\n- \n- \n\nMerci pour votre réponse.\n\nCordialement,",
            commande: "Bonjour,\n\nJe suis intéressé(e) par l'achat de cette œuvre. Pourriez-vous me confirmer :\n- Sa disponibilité\n- Les délais de livraison\n- Les modalités de paiement\n\nMerci.\n\nCordialement,",
            personnalisation: "Bonjour,\n\nJe souhaiterais savoir s'il est possible de personnaliser cette œuvre ou d'en créer une similaire avec des modifications spécifiques :\n- \n- \n\nMerci pour votre retour.\n\nCordialement,"
        };

        // Suggestions de sujets
        const sujetInput = document.getElementById('sujet');
        const suggestions = [
            "Question sur une œuvre",
            "Demande de personnalisation",
            "Informations sur la livraison",
            "Commande spéciale",
            "Collaboration artistique"
        ];

        sujetInput.addEventListener('focus', function() {
            if (!this.value && !selectedWorkId) {
                this.placeholder = suggestions[Math.floor(Math.random() * suggestions.length)];
            }
        });

        // Animation au scroll
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

        // Notification de succès avec auto-redirection
        <?php if ($message_sent): ?>
        setTimeout(() => {
            if (confirm('Message envoyé ! Souhaitez-vous consulter vos messages ?')) {
                window.location.href = 'messages.php';
            } else {
                window.location.href = 'profil-artisan.php?id=<?= $idArtisan ?>';
            }
        }, 2000);
        <?php endif; ?>

        console.log('Page de contact artisan initialisée avec succès!');
    </script>
</body>
</html>