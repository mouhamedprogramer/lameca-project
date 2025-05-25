<?php
// Inclure le header
include 'includes/header.php';

// Récupérer toutes les œuvres avec les informations de l'artisan et les photos
$oeuvres = [];
if (isset($conn)) {
    try {
        $query = "
            SELECT 
                o.idOeuvre,
                o.titre,
                o.description,
                o.prix,
                o.caracteristiques,
                o.datePublication,
                o.disponibilite,
                u.nom as nomArtisan,
                u.prenom as prenomArtisan,
                u.photo as photoArtisan,
                a.specialite,
                po.url as photoUrl,
                (SELECT COUNT(*) FROM Aimer am WHERE am.idOeuvre = o.idOeuvre) as nbLikes,
                (SELECT AVG(av.notation) FROM Avisoeuvre av WHERE av.idOeuvre = o.idOeuvre) as noteAvis,
                (SELECT COUNT(*) FROM Avisoeuvre av WHERE av.idOeuvre = o.idOeuvre) as nbAvis
            FROM Oeuvre o
            JOIN Artisan a ON o.idArtisan = a.idArtisan
            JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
            LEFT JOIN Photooeuvre po ON o.idOeuvre = po.idOeuvre
            WHERE o.disponibilite = 1
            ORDER BY o.datePublication DESC
        ";
        
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            $temp_oeuvres = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $idOeuvre = $row['idOeuvre'];
                
                if (!isset($temp_oeuvres[$idOeuvre])) {
                    $temp_oeuvres[$idOeuvre] = [
                        'idOeuvre' => $row['idOeuvre'],
                        'titre' => $row['titre'],
                        'description' => $row['description'],
                        'prix' => $row['prix'],
                        'caracteristiques' => $row['caracteristiques'],
                        'datePublication' => $row['datePublication'],
                        'disponibilite' => $row['disponibilite'],
                        'nomArtisan' => $row['nomArtisan'],
                        'prenomArtisan' => $row['prenomArtisan'],
                        'photoArtisan' => $row['photoArtisan'],
                        'specialite' => $row['specialite'],
                        'nbLikes' => $row['nbLikes'],
                        'noteAvis' => $row['noteAvis'] ? round($row['noteAvis'], 1) : 0,
                        'nbAvis' => $row['nbAvis'],
                        'photos' => []
                    ];
                }
                
                if ($row['photoUrl']) {
                    $temp_oeuvres[$idOeuvre]['photos'][] = $row['photoUrl'];
                }
            }
            
            $oeuvres = array_values($temp_oeuvres);
        }
    } catch (Exception $e) {
        $oeuvres = [];
    }
}

// Récupérer les spécialités pour les filtres
$specialites = [];
if (isset($conn)) {
    try {
        $query = "SELECT DISTINCT specialite FROM Artisan WHERE specialite IS NOT NULL AND specialite != '' ORDER BY specialite";
        $result = mysqli_query($conn, $query);
        
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $specialites[] = $row['specialite'];
            }
        }
    } catch (Exception $e) {
        $specialites = [];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galerie Virtuelle | Artisano</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding-top: 80px;
            padding-top: 0; /* Supprime l'espace en haut */

        }

        .gallery-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }

        .gallery-header {
            text-align: center;
            margin-bottom: 3rem;
            animation: fadeInUp 0.8s ease-out;
        }

        .gallery-title {
            font-size: 3.5rem;
            font-weight: 700;
            color: #667eea;
            argin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .gallery-subtitle {
            font-size: 1.2rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .gallery-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-top: 2rem;
            animation: fadeInUp 0.8s ease-out 0.2s both;
        }

        .stat-item {
            background: white;
            padding: 1.5rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            color:  #667eea;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        .controls-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            gap: 1rem;
            flex-wrap: wrap;
            animation: fadeInUp 0.8s ease-out 0.4s both;
        }

        .search-filter-container {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            flex: 1;
        }

        .search-container {
            position: relative;
            min-width: 300px;
        }

        .search-input {
            width: 100%;
            padding: 0.8rem 1rem 0.8rem 3rem;
            border: none;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #667eea;
            font-size: 1.1rem;
        }

        .filter-select {
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            font-size: 1rem;
            outline: none;
            cursor: pointer;
            min-width: 150px;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            background: white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(-2px);
        }

        .view-controls {
            display: flex;
            gap: 0.5rem;
        }

        .view-btn {
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 10px;
            background: white;
            color:  #667eea;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .view-btn.active,
        .view-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .gallery-grid {
            display: grid;
            gap: 2rem;
            animation: fadeInUp 0.8s ease-out 0.6s both;
        }

        .grid-masonry {
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        }

        .grid-large {
            grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
        }

        .artwork-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            cursor: pointer;
        }

        .artwork-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
        }

        .artwork-image-container {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .artwork-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: all 0.6s ease;
        }

        .artwork-card:hover .artwork-image {
            transform: scale(1.1);
        }

        .artwork-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
            opacity: 0;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
        }

        .artwork-card:hover .artwork-overlay {
            opacity: 1;
        }

        .overlay-btn {
            padding: 0.8rem;
            border: 2px solid white;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .overlay-btn:hover {
            background: white;
            color: #667eea;
            transform: scale(1.1);
        }

        .artwork-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(255, 255, 255, 0.95);
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #667eea;
            backdrop-filter: blur(10px);
        }

        .artwork-info {
            padding: 1.5rem;
        }

        .artwork-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .artwork-description {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-bottom: 1rem;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .artwork-artist {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            margin-bottom: 1rem;
            padding: 0.8rem;
            background: #f8f9fa;
            border-radius: 12px;
        }

        .artist-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #667eea;
        }

        .artist-info h4 {
            font-size: 0.9rem;
            color: #2c3e50;
            margin-bottom: 0.2rem;
        }

        .artist-speciality {
            font-size: 0.8rem;
            color: #667eea;
            font-weight: 500;
        }

        .artwork-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e9ecef;
            padding-top: 1rem;
        }

        .artwork-price {
            font-size: 1.4rem;
            font-weight: 700;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .artwork-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            padding: 0.5rem;
            border: none;
            border-radius: 8px;
            background: #f8f9fa;
            color: #6c757d;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .action-btn.liked {
            background: #e74c3c;
            color: white;
        }

        .artwork-stats {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
            font-size: 0.8rem;
            color: #6c757d;
        }

        .stat-item-small {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .rating-stars {
            color: #ffc107;
        }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.2rem;
            display: none;
        }

        .no-results i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        /* Modal pour vue détaillée */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 10000;
            backdrop-filter: blur(10px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            animation: modalSlideIn 0.3s ease-out;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            z-index: 10001;
            transition: all 0.3s ease;
        }

        .modal-close:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: scale(1.1);
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translate(-50%, -50%) scale(0.8);
            }
            to {
                opacity: 1;
                transform: translate(-50%, -50%) scale(1);
            }
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
            .gallery-container {
                padding: 1rem;
            }

            .gallery-title {
                font-size: 2.5rem;
            }

            .gallery-stats {
                flex-direction: column;
                gap: 1rem;
            }

            .controls-section {
                flex-direction: column;
                align-items: stretch;
            }

            .search-filter-container {
                flex-direction: column;
            }

            .search-container {
                min-width: auto;
            }

            .gallery-grid {
                grid-template-columns: 1fr !important;
            }

            .artwork-image-container {
                height: 200px;
            }
        }

        @media (max-width: 480px) {
            .gallery-title {
                font-size: 2rem;
            }

            .artwork-info {
                padding: 1rem;
            }

            .artwork-title {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <div class="gallery-container">
        <!-- Header -->
        <div class="gallery-header">
            <h1 class="gallery-title">Galerie Virtuelle</h1>
            <p class="gallery-subtitle">
                Découvrez une collection exceptionnelle d'œuvres d'art créées par nos artisans talentueux. 
                Chaque pièce raconte une histoire unique et reflète la passion de son créateur.
            </p>
            <div class="gallery-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= count($oeuvres) ?></span>
                    <span class="stat-label">Œuvres disponibles</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= count($specialites) ?></span>
                    <span class="stat-label">Spécialités</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= count(array_unique(array_column($oeuvres, 'nomArtisan'))) ?></span>
                    <span class="stat-label">Artisans</span>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="controls-section">
            <div class="search-filter-container">
                <div class="search-container">
                    <i class="fas fa-search search-icon"></i>
                    <input type="text" id="searchInput" class="search-input" placeholder="Rechercher une œuvre, un artisan...">
                </div>
                
                <select id="specialityFilter" class="filter-select">
                    <option value="">Toutes les spécialités</option>
                    <?php foreach ($specialites as $specialite): ?>
                        <option value="<?= htmlspecialchars($specialite) ?>"><?= htmlspecialchars($specialite) ?></option>
                    <?php endforeach; ?>
                </select>

                <select id="priceFilter" class="filter-select">
                    <option value="">Tous les prix</option>
                    <option value="0-50">0€ - 50€</option>
                    <option value="50-100">50€ - 100€</option>
                    <option value="100-200">100€ - 200€</option>
                    <option value="200-500">200€ - 500€</option>
                    <option value="500+">500€+</option>
                </select>
            </div>

            <div class="view-controls">
                <button class="view-btn active" data-view="masonry" title="Vue Masonry">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" data-view="large" title="Vue Large">
                    <i class="fas fa-th-large"></i>
                </button>
            </div>
        </div>

        <!-- Gallery Grid -->
        <div class="gallery-grid grid-masonry" id="galleryGrid">
            <?php if (!empty($oeuvres)): ?>
                <?php foreach ($oeuvres as $oeuvre): ?>
                    <div class="artwork-card" 
                         data-titre="<?= strtolower(htmlspecialchars($oeuvre['titre'])) ?>"
                         data-artisan="<?= strtolower(htmlspecialchars($oeuvre['prenomArtisan'] . ' ' . $oeuvre['nomArtisan'])) ?>"
                         data-specialite="<?= strtolower(htmlspecialchars($oeuvre['specialite'] ?? '')) ?>"
                         data-prix="<?= $oeuvre['prix'] ?>"
                         onclick="openModal(<?= $oeuvre['idOeuvre'] ?>)">
                        
                        <div class="artwork-image-container">
                            <?php 
                            $mainImage = !empty($oeuvre['photos']) ? '../'.$oeuvre['photos'][0] : 'Images/placeholder-artwork.jpg';
                            ?>
                            <img src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($oeuvre['titre']) ?>" class="artwork-image">
                            
                            <div class="artwork-overlay">
                                <button class="overlay-btn" title="Voir les détails">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button class="overlay-btn" title="Ajouter aux favoris">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="overlay-btn" title="Ajouter au panier">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                            </div>

                            <?php if ($oeuvre['disponibilite']): ?>
                                <div class="artwork-badge">Disponible</div>
                            <?php endif; ?>
                        </div>

                        <div class="artwork-info">
                            <h3 class="artwork-title"><?= htmlspecialchars($oeuvre['titre']) ?></h3>
                            <p class="artwork-description"><?= htmlspecialchars($oeuvre['description'] ?? '') ?></p>

                            <div class="artwork-artist">
                                <img src="<?= htmlspecialchars($oeuvre['photoArtisan'] ?? 'Images/default-avatar.jpg') ?>" 
                                     alt="<?= htmlspecialchars($oeuvre['prenomArtisan'] . ' ' . $oeuvre['nomArtisan']) ?>" 
                                     class="artist-avatar">
                                <div class="artist-info">
                                    <h4><?= htmlspecialchars($oeuvre['prenomArtisan'] . ' ' . $oeuvre['nomArtisan']) ?></h4>
                                    <span class="artist-speciality"><?= htmlspecialchars($oeuvre['specialite'] ?? '') ?></span>
                                </div>
                            </div>

                            <div class="artwork-footer">
                                <div class="artwork-price"><?= number_format($oeuvre['prix'], 2) ?>€</div>
                                <div class="artwork-actions">
                                    <button class="action-btn" title="Ajouter aux favoris">
                                        <i class="far fa-heart"></i> <?= $oeuvre['nbLikes'] ?>
                                    </button>
                                    <button class="action-btn" title="Ajouter au panier">
                                        <i class="fas fa-shopping-cart"></i>
                                    </button>
                                </div>
                            </div>

                            <?php if ($oeuvre['nbAvis'] > 0): ?>
                                <div class="artwork-stats">
                                    <div class="stat-item-small">
                                        <span class="rating-stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star<?= $i <= $oeuvre['noteAvis'] ? '' : ' text-muted' ?>"></i>
                                            <?php endfor; ?>
                                        </span>
                                        <span><?= $oeuvre['noteAvis'] ?>/5</span>
                                    </div>
                                    <div class="stat-item-small">
                                        <i class="fas fa-comment"></i>
                                        <span><?= $oeuvre['nbAvis'] ?> avis</span>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- No Results -->
        <div class="no-results" id="noResults">
            <i class="fas fa-search"></i>
            <p>Aucune œuvre ne correspond à vos critères de recherche.</p>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="artworkModal">
        <button class="modal-close" onclick="closeModal()">
            <i class="fas fa-times"></i>
        </button>
        <div class="modal-content" id="modalContent">
            <!-- Contenu du modal sera injeté ici -->
        </div>
    </div>

    <script>
        // Variables globales
        let currentView = 'masonry';
        const galleryGrid = document.getElementById('galleryGrid');
        const searchInput = document.getElementById('searchInput');
        const specialityFilter = document.getElementById('specialityFilter');
        const priceFilter = document.getElementById('priceFilter');
        const noResults = document.getElementById('noResults');

        // Gestion des vues
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                currentView = this.dataset.view;
                galleryGrid.className = `gallery-grid grid-${currentView}`;
            });
        });

        // Fonction de filtrage
        function filterArtworks() {
            const searchTerm = searchInput.value.toLowerCase().trim();
            const selectedSpeciality = specialityFilter.value.toLowerCase();
            const selectedPriceRange = priceFilter.value;
            
            const artworkCards = document.querySelectorAll('.artwork-card');
            let visibleCount = 0;

            artworkCards.forEach(card => {
                const titre = card.dataset.titre;
                const artisan = card.dataset.artisan;
                const specialite = card.dataset.specialite;
                const prix = parseFloat(card.dataset.prix);

                // Filtre de recherche
                const matchesSearch = !searchTerm || 
                    titre.includes(searchTerm) || 
                    artisan.includes(searchTerm) ||
                    specialite.includes(searchTerm);

                // Filtre de spécialité
                const matchesSpeciality = !selectedSpeciality || 
                    specialite === selectedSpeciality;

                // Filtre de prix
                let matchesPrice = true;
                if (selectedPriceRange) {
                    switch (selectedPriceRange) {
                        case '0-50':
                            matchesPrice = prix <= 50;
                            break;
                        case '50-100':
                            matchesPrice = prix > 50 && prix <= 100;
                            break;
                        case '100-200':
                            matchesPrice = prix > 100 && prix <= 200;
                            break;
                        case '200-500':
                            matchesPrice = prix > 200 && prix <= 500;
                            break;
                        case '500+':
                            matchesPrice = prix > 500;
                            break;
                    }
                }

                // Afficher/masquer la carte
                if (matchesSearch && matchesSpeciality && matchesPrice) {
                    card.style.display = 'block';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Afficher/masquer le message "aucun résultat"
            noResults.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Event listeners pour les filtres
        searchInput.addEventListener('input', filterArtworks);
        specialityFilter.addEventListener('change', filterArtworks);
        priceFilter.addEventListener('change', filterArtworks);

        // Fonction pour ouvrir le modal
        function openModal(oeuvreId) {
            // Ici vous pouvez ajouter une requête AJAX pour récupérer les détails complets de l'œuvre
            document.getElementById('artworkModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        // Fonction pour fermer le modal
        function closeModal() {
            document.getElementById('artworkModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Fermer le modal en cliquant à l'extérieur
        document.getElementById('artworkModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });

        // Fermer le modal avec la touche Échap
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Animation d'apparition des cartes au scroll
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

        // Observer toutes les cartes
        document.querySelectorAll('.artwork-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
            observer.observe(card);
        });

        // Gestion des likes (simulation)
        document.querySelectorAll('.action-btn').forEach(btn => {
            if (btn.innerHTML.includes('fa-heart')) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const icon = this.querySelector('i');
                    const isLiked = icon.classList.contains('fas');
                    
                    if (isLiked) {
                        icon.classList.remove('fas');
                        icon.classList.add('far');
                        this.classList.remove('liked');
                    } else {
                        icon.classList.remove('far');
                        icon.classList.add('fas');
                        this.classList.add('liked');
                    }
                    
                    // Animation de like
                    this.style.transform = 'scale(1.2)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                    }, 200);
                });
            }
        });

        // Effet parallaxe subtil sur le header
        window.addEventListener('scroll', function() {
            const scrolled = window.pageYOffset;
            const header = document.querySelector('.gallery-header');
            header.style.transform = `translateY(${scrolled * 0.1}px)`;
        });

        // Lazy loading des images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }

        // Fonction pour mélanger les œuvres (shuffle)
        function shuffleArtworks() {
            const cards = Array.from(document.querySelectorAll('.artwork-card'));
            const parent = cards[0].parentNode;
            
            // Mélanger l'array
            for (let i = cards.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [cards[i], cards[j]] = [cards[j], cards[i]];
            }
            
            // Réinsérer les éléments dans le nouveau ordre
            cards.forEach(card => parent.appendChild(card));
        }

        // Ajouter un bouton de mélange (optionnel)
        const shuffleBtn = document.createElement('button');
        shuffleBtn.innerHTML = '<i class="fas fa-random"></i>';
        shuffleBtn.className = 'view-btn';
        shuffleBtn.title = 'Mélanger les œuvres';
        shuffleBtn.addEventListener('click', shuffleArtworks);
        document.querySelector('.view-controls').appendChild(shuffleBtn);

        // Compteur de vues (simulation)
        let viewCount = 0;
        document.querySelectorAll('.artwork-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                viewCount++;
                console.log(`Œuvre vue ${viewCount} fois`);
            });
        });

        // Animation de compte à rebours pour les statistiques
        function animateCounters() {
            const counters = document.querySelectorAll('.stat-number');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                const increment = target / 50;
                let count = 0;
                
                const updateCounter = () => {
                    if (count < target) {
                        count += increment;
                        counter.textContent = Math.ceil(count);
                        setTimeout(updateCounter, 30);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                updateCounter();
            });
        }

        // Démarrer l'animation des compteurs après un délai
        setTimeout(animateCounters, 1000);

        // Fonction de tri
        function sortArtworks(criteria) {
            const cards = Array.from(document.querySelectorAll('.artwork-card'));
            const parent = cards[0].parentNode;
            
            cards.sort((a, b) => {
                switch (criteria) {
                    case 'price-asc':
                        return parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix);
                    case 'price-desc':
                        return parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix);
                    case 'title':
                        return a.dataset.titre.localeCompare(b.dataset.titre);
                    case 'artist':
                        return a.dataset.artisan.localeCompare(b.dataset.artisan);
                    default:
                        return 0;
                }
            });
            
            // Animation de tri
            cards.forEach((card, index) => {
                card.style.order = index;
                card.style.animation = `fadeInUp 0.5s ease ${index * 0.05}s both`;
            });
        }

        // Ajouter un sélecteur de tri
        const sortSelect = document.createElement('select');
        sortSelect.className = 'filter-select';
        sortSelect.innerHTML = `
            <option value="">Trier par...</option>
            <option value="price-asc">Prix croissant</option>
            <option value="price-desc">Prix décroissant</option>
            <option value="title">Titre A-Z</option>
            <option value="artist">Artisan A-Z</option>
        `;
        sortSelect.addEventListener('change', function() {
            if (this.value) {
                sortArtworks(this.value);
            }
        });
        document.querySelector('.search-filter-container').appendChild(sortSelect);

        // Effet de survol amélioré pour les cartes
        document.querySelectorAll('.artwork-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '10';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '1';
            });
        });

        // Gestion du responsive - ajustement automatique de la grille
        function adjustGridLayout() {
            const container = document.querySelector('.gallery-grid');
            const containerWidth = container.offsetWidth;
            
            if (containerWidth < 768) {
                container.className = 'gallery-grid';
                container.style.gridTemplateColumns = '1fr';
            } else if (containerWidth < 1200) {
                container.className = 'gallery-grid';
                container.style.gridTemplateColumns = 'repeat(2, 1fr)';
            } else {
                container.className = `gallery-grid grid-${currentView}`;
            }
        }

        // Ajuster la grille au redimensionnement
        window.addEventListener('resize', adjustGridLayout);
        adjustGridLayout(); // Appel initial

        // Préchargement des images au survol
        document.querySelectorAll('.artwork-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                const img = this.querySelector('.artwork-image');
                if (img && img.dataset.hoverSrc) {
                    const preloadImg = new Image();
                    preloadImg.src = img.dataset.hoverSrc;
                }
            });
        });

        // Feedback visuel pour les actions
        function showFeedback(message, type = 'success') {
            const feedback = document.createElement('div');
            feedback.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#27ae60' : '#e74c3c'};
                color: white;
                padding: 1rem 2rem;
                border-radius: 10px;
                z-index: 10000;
                animation: slideInRight 0.3s ease;
            `;
            feedback.textContent = message;
            document.body.appendChild(feedback);
            
            setTimeout(() => {
                feedback.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => feedback.remove(), 300);
            }, 3000);
        }

        // Ajouter les animations CSS manquantes
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

        console.log('Galerie virtuelle initialisée avec succès!');
    </script>
    <?php
    // Inclure le footer
    require_once 'includes/footer.php';
    ?>
</body>
</html>