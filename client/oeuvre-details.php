<?php
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/detail-oeuvre.css">
    <!-- CSS spécifique pour la galerie d'images -->
    <style>
        .oeuvre-gallery {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        
        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .main-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .thumbnails {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .thumbnail {
            width: 80px;
            height: 80px;
            border-radius: 5px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .thumbnail.active {
            border-color: #4a90e2;
        }
        
        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .oeuvre-details-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-top: 40px;
        }
        
        @media (max-width: 768px) {
            .oeuvre-details-container {
                grid-template-columns: 1fr;
            }
        }
        
        .oeuvre-actions-btn {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }
        
        .artisan-info {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 20px 0;
            padding: 15px;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        
        .artisan-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            overflow: hidden;
        }
        
        .artisan-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="container">
    

        <div class="oeuvre-details-container">
            <div class="oeuvre-gallery">
                <?php if (count($photos) > 0): ?>
                    <div class="main-image">
                        <img id="main-image" src="../<?php echo $photos[0]['url']; ?>" alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
                    </div>
                    
                    <?php if (count($photos) > 1): ?>
                        <div class="thumbnails">
                            <?php foreach ($photos as $index => $photo): ?>
                                <div class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" data-src="../<?php echo $photo['url']; ?>">
                                    <img src="../<?php echo $photo['url']; ?>" alt="Miniature <?php echo $index + 1; ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="main-image">
                        <img src="../../images/oeuvre-placeholder.jpg" alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
                    </div>
                <?php endif; ?>
            </div>

            <div class="oeuvre-info">
                <h1><?php echo htmlspecialchars($oeuvre['titre']); ?></h1>
                
                <div class="artisan-info">
                    <div class="artisan-photo">
                        <?php
$artisan_photo = !empty($oeuvre['artisan_photo']) ? '../images/' . $oeuvre['artisan_photo'] : '../images/profile-placeholder.jpg';
?>
                        <img src="<?php echo $artisan_photo; ?>" alt="<?php echo htmlspecialchars($oeuvre['artisan_prenom'] . ' ' . $oeuvre['artisan_nom']); ?>">
                    </div>
                    <div>
                        <h3><?php echo htmlspecialchars($oeuvre['artisan_prenom'] . ' ' . $oeuvre['artisan_nom']); ?></h3>
                        <p><?php echo htmlspecialchars($oeuvre['specialite']); ?></p>
                    </div>
                </div>
                
                <div class="price-availability">
                    <div class="price"><?php echo formaterPrix($oeuvre['prix']); ?></div>
                    <div class="availability <?php echo $oeuvre['disponibilite'] ? 'in-stock' : 'out-of-stock'; ?>">
                        <?php echo $oeuvre['disponibilite'] ? 'Disponible' : 'Indisponible'; ?>
                    </div>
                </div>
                
                <div class="oeuvre-description">
                    <h2>Description</h2>
                    <p><?php echo nl2br(htmlspecialchars($oeuvre['description'])); ?></p>
                </div>
                
                <?php if (!empty($oeuvre['caracteristiques'])): ?>
                <div class="oeuvre-caracteristiques">
                    <h2>Caractéristiques</h2>
                    <p><?php echo nl2br(htmlspecialchars($oeuvre['caracteristiques'])); ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($oeuvre['disponibilite']): ?>
                <div class="oeuvre-actions-btn">
                    <button class="btn-primary add-to-cart-btn" data-id="<?php echo $oeuvre['idOeuvre']; ?>">
                        <i class="fas fa-shopping-cart"></i> Ajouter au panier
                    </button>
                    <button class="btn-outline wishlist-btn" data-id="<?php echo $oeuvre['idOeuvre']; ?>">
                        <i class="far fa-heart"></i> Ajouter aux favoris
                    </button>
                </div>
                <?php endif; ?>
                
                <div class="contact-artisan">
                    <h2>Contacter l'artisan</h2>
                    <a href="contact-artisan.php?id=<?php echo $oeuvre['idArtisan']; ?>" class="btn-secondary">
                        <i class="far fa-envelope"></i> Envoyer un message
                    </a>
                </div>
            </div>
        </div>
    </main>
    <br><br><br>
    <?php include 'includes/footer.php'; ?>

    <script>
        // Script pour la galerie d'images
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.thumbnail');
            const mainImage = document.getElementById('main-image');
            
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    // Mettre à jour l'image principale
                    mainImage.src = this.getAttribute('data-src');
                    
                    // Mettre à jour la classe active
                    thumbnails.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                });
            });
            
            // Ajouter au panier
            const addToCartBtn = document.querySelector('.add-to-cart-btn');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function() {
                    const idOeuvre = this.getAttribute('data-id');
                    // Ajouter au panier via AJAX ici
                    alert('Produit ajouté au panier !');
                });
            }
            
            // Ajouter aux favoris
            const wishlistBtn = document.querySelector('.wishlist-btn');
            if (wishlistBtn) {
                wishlistBtn.addEventListener('click', function() {
                    const idOeuvre = this.getAttribute('data-id');
                    // Ajouter aux favoris via AJAX ici
                    alert('Produit ajouté aux favoris !');
                });
            }
        });
    </script>
</body>
</html>