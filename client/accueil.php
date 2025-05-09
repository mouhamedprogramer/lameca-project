<?php
// Connexion à la base de données
require_once 'includes/conn.php';

// Récupération des 6 oeuvres les plus chères qui sont disponibles
$sql_oeuvres = "SELECT o.*, a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom 
                FROM oeuvre o 
                LEFT JOIN artisan a ON o.idArtisan = a.idArtisan 
                LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                WHERE o.disponibilite = TRUE 
                ORDER BY o.prix DESC 
                LIMIT 9";
$result_oeuvres = $conn->query($sql_oeuvres);
$oeuvres = [];
if ($result_oeuvres->num_rows > 0) {
    while($row = $result_oeuvres->fetch_assoc()) {
        // Récupérer la première photo de l'œuvre
        $sql_photo = "SELECT url FROM photooeuvre WHERE idOeuvre = ? ORDER BY idPhoto ASC LIMIT 1";
        $stmt = $conn->prepare($sql_photo);
        $stmt->bind_param("i", $row['idOeuvre']);
        $stmt->execute();
        $result_photo = $stmt->get_result();
        
        if ($result_photo->num_rows > 0) {
            $photo = $result_photo->fetch_assoc();
            $row['photo_url'] = $photo['url'];
        } else {
            $row['photo_url'] = null;
        }
        
        $oeuvres[] = $row;
    }
}

// Récupération des 3 artisans aléatoires
$sql_artisans = "SELECT a.*, u.nom, u.prenom, u.photo, a.specialite 
                FROM artisan a 
                JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                WHERE a.statut_verification = TRUE 
                ORDER BY RAND() 
                LIMIT 3";
$result_artisans = $conn->query($sql_artisans);
$artisans = [];
if ($result_artisans->num_rows > 0) {
    while($row = $result_artisans->fetch_assoc()) {
        $artisans[] = $row;
    }
}

// Récupérer les événements mis en avant
$sql_evenements = "SELECT e.*, u.nom as artisan_nom, u.prenom as artisan_prenom 
                 FROM evenement e 
                 LEFT JOIN artisan a ON e.idArtisan = a.idArtisan 
                 LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                 WHERE e.mis_en_avant = TRUE AND e.dateDebut >= CURDATE() 
                 ORDER BY e.dateDebut ASC 
                 LIMIT 3";
$result_evenements = $conn->query($sql_evenements);
$evenements = [];
if ($result_evenements->num_rows > 0) {
    while($row = $result_evenements->fetch_assoc()) {
        $evenements[] = $row;
    }
}

// Fonction pour formater les prix
function formaterPrix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}
?>

<?php include 'includes/header.php'; ?>

    <main>
    <section class="hero-section">
    <div class="hero-slider" style=" height: 450px;">
        <div class="slides">
            <div class="slide active" style="background-image: url('../images/art/1.jpeg'); height: 600px;">
            </div>
            <div class="slide" style="background-image: url('../images/art/3.jpeg'); height: 600px;">
            </div>
            <div class="slide" style="background-image: url('../images/art/wp8898536.jpg'); height: 600px;">
            </div>
        </div>
        <div class="slider-controls">
            <button class="prev-slide"><i class="fas fa-chevron-left"></i></button>
            <button class="next-slide"><i class="fas fa-chevron-right"></i></button>
        </div>
        <div class="slider-dots"></div>
    </div>
</section>


        

       <!-- Section des œuvres -->
<section class="section oeuvres-section">
    <div class="section-header">
        <h2>Œuvres exclusives</h2>
        <p>Découvrez nos pièces les plus prestigieuses</p>
    </div>
    <div class="oeuvres-grid">
        <?php if (count($oeuvres) > 0): ?>
            <?php foreach ($oeuvres as $oeuvre): ?>
                <div class="oeuvre-card">
                    <div class="oeuvre-image">
                        <?php 
                        // Corriger le chemin vers les images d'œuvres
                        $image = !empty($oeuvre['photo_url']) ? '../' . $oeuvre['photo_url'] : '../images/oeuvre-placeholder.jpg';
                        ?>
                        <img src="<?php echo $image; ?>" alt="<?php echo htmlspecialchars($oeuvre['titre']); ?>">
                        <div class="oeuvre-actions">
                            <button class="action-btn wishlist-btn" data-id="<?php echo $oeuvre['idOeuvre']; ?>">
                                <i class="far fa-heart"></i>
                            </button>
                            <button class="action-btn cart-btn" data-id="<?php echo $oeuvre['idOeuvre']; ?>">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="oeuvre-details">
                        <h3><?php echo htmlspecialchars($oeuvre['titre']); ?></h3>
                        <p class="oeuvre-artisan">Par <?php echo htmlspecialchars($oeuvre['artisan_prenom'] . ' ' . $oeuvre['artisan_nom']); ?></p>
                        <p class="oeuvre-description"><?php echo htmlspecialchars(substr($oeuvre['description'], 0, 100)) . (strlen($oeuvre['description']) > 100 ? '...' : ''); ?></p>
                        <div class="oeuvre-footer">
                            <span class="oeuvre-prix"><?php echo formaterPrix($oeuvre['prix']); ?></span>
                            <a href="oeuvre-details.php?id=<?php echo $oeuvre['idOeuvre']; ?>" class="btn-secondary">Voir détails</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results">
                <p>Aucune œuvre disponible pour le moment.</p>
            </div>
        <?php endif; ?>
    </div>
    <div class="section-footer">
        <a href="oeuvres.php" class="btn-outline">Voir toutes les œuvres</a>
    </div>
</section>

        <!-- Section des artisans -->
        <section class="section artisans-section">
            <div class="section-header">
                <h2>Nos artisans talentueux</h2>
                <p>Les créateurs derrière les œuvres exceptionnelles</p>
            </div>
            <div class="artisans-grid">
                <?php if (count($artisans) > 0): ?>
                    <?php foreach ($artisans as $artisan): ?>
                        <div class="artisan-card">
                            <div class="artisan-image">
                                <?php 
                                // Corriger le chemin vers les photos des artisans
                                $image_artisan = !empty($artisan['photo']) ? '../images/' . $artisan['photo'] : '../images/user-placeholder.png';
                                ?>
                                <img src="<?php echo $image_artisan; ?>" alt="<?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?>">
                            </div>
                            <div class="artisan-details">
                                <h3><?php echo htmlspecialchars($artisan['prenom'] . ' ' . $artisan['nom']); ?></h3>
                                <p class="artisan-specialite"><?php echo htmlspecialchars($artisan['specialite']); ?></p>
                                <a href="artisan-details.php?id=<?php echo $artisan['idArtisan']; ?>" class="btn-secondary">Découvrir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>Aucun artisan disponible pour le moment.</p>
                    </div>
                <?php endif; ?>
            </div>
            <div class="section-footer">
                <a href="artisans.php" class="btn-outline">Voir tous les artisans</a>
            </div>
        </section>

        <!-- Section des événements -->
        <?php if (count($evenements) > 0): ?>
        <section class="section evenements-section">
            <div class="section-header">
                <h2>Événements à venir</h2>
                <p>Ne manquez pas nos prochains rendez-vous artistiques</p>
            </div>
            <div class="evenements-grid">
                <?php foreach ($evenements as $evenement): ?>
                    <div class="evenement-card">
                        <div class="evenement-date">
                            <?php 
                            $date = new DateTime($evenement['dateDebut']);
                            echo '<span class="jour">' . $date->format('d') . '</span>';
                            echo '<span class="mois">' . $date->format('M') . '</span>';
                            ?>
                        </div>
                        <div class="evenement-details">
                            <h3><?php echo htmlspecialchars($evenement['nomEvenement']); ?></h3>
                            <p class="evenement-lieu"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($evenement['lieu']); ?></p>
                            <p class="evenement-description"><?php echo htmlspecialchars(substr($evenement['description'], 0, 100)) . (strlen($evenement['description']) > 100 ? '...' : ''); ?></p>
                            <a href="evenement-details.php?id=<?php echo $evenement['idEvenement']; ?>" class="btn-secondary">En savoir plus</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="section-footer">
                <a href="evenements.php" class="btn-outline">Voir tous les événements</a>
            </div>
        </section>
        <?php endif; ?>

        <!-- Section Newsletter -->
        <section class="newsletter-section">
            <div class="newsletter-content">
                <h2>Restez informé</h2>
                <p>Inscrivez-vous à notre newsletter pour recevoir les dernières actualités et promotions exclusives</p>
                <form class="newsletter-form" id="newsletter-form">
                    <div class="form-group">
                        <input type="email" id="email" name="email" placeholder="Votre adresse email" required>
                        <button type="submit" class="btn-primary">S'inscrire</button>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" id="consent" name="consent" required>
                        <label for="consent">J'accepte de recevoir des informations par email</label>
                    </div>
                </form>
            </div>
        </section>
       
    </main>

    <!-- Footer -->
    <?php
    require_once 'includes/footer.php';
    ?>

    <!-- JavaScript -->
    <script src="js/main.js"></script>
</body>
</html>