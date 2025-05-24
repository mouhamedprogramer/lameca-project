<?php
session_start();
require_once 'includes/conn.php';

// Vérifier si l'utilisateur est connecté et est un client
if (!isset($_SESSION['idUtilisateur']) || $_SESSION['role'] !== 'Client') {
    header('Location: connexion.php');
    exit;
}

$idClient = $_SESSION['idUtilisateur'];

// Récupérer les articles du panier
$sql = "SELECT c.*, o.titre, o.prix, o.description, 
        (SELECT p.url FROM Photooeuvre p WHERE p.idOeuvre = o.idOeuvre ORDER BY p.idPhoto ASC LIMIT 1) as photo,
        a.idArtisan, u.nom as artisan_nom, u.prenom as artisan_prenom
        FROM Commande c
        JOIN Oeuvre o ON c.idOeuvre = o.idOeuvre
        JOIN Artisan a ON o.idArtisan = a.idArtisan
        JOIN Utilisateur u ON a.idArtisan = u.idUtilisateur
        WHERE c.idClient = ? AND c.statut = 'En attente'
        ORDER BY c.dateCommande DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idClient);
$stmt->execute();
$result = $stmt->get_result();

$total = 0;
$articles = [];
while ($row = $result->fetch_assoc()) {
    $articles[] = $row;
    $total += $row['prix'] * $row['nombreArticles'];
}

function formaterPrix($prix) {
    return number_format($prix, 2, ',', ' ') . ' €';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier - Artisano</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/modern.css">
    <link rel="stylesheet" href="css/panier.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <main class="main-content">
        <div class="container">
            <div class="page-header">
                <h1><i class="fas fa-shopping-cart"></i> Mon Panier</h1>
                <p>Vos œuvres sélectionnées (<?php echo count($articles); ?> article<?php echo count($articles) > 1 ? 's' : ''; ?>)</p>
            </div>

            <?php if (empty($articles)): ?>
                <div class="empty-cart">
                    <div class="empty-cart-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h2>Votre panier est vide</h2>
                    <p>Découvrez nos magnifiques œuvres d'art et ajoutez-les à votre panier</p>
                    <a href="oeuvres.php" class="btn-primary">
                        <i class="fas fa-palette"></i> Découvrir les œuvres
                    </a>
                </div>
            <?php else: ?>
                <div class="cart-content">
                    <div class="cart-items">
                        <?php foreach ($articles as $article): ?>
                            <div class="cart-item" data-id="<?php echo $article['idCommande']; ?>">
                                <div class="item-image">
                                    <?php 
                                    $image_src = !empty($article['photo']) ? '../' . $article['photo'] : 'images/oeuvre-placeholder.jpg';
                                    ?>
                                    <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($article['titre']); ?>">
                                </div>
                                
                                <div class="item-details">
                                    <h3 class="item-title"><?php echo htmlspecialchars($article['titre']); ?></h3>
                                    <p class="item-artisan">
                                        Par <?php echo htmlspecialchars($article['artisan_prenom'] . ' ' . $article['artisan_nom']); ?>
                                    </p>
                                    <p class="item-description">
                                        <?php echo htmlspecialchars(substr($article['description'], 0, 100)) . '...'; ?>
                                    </p>
                                </div>
                                
                                <div class="item-quantity">
                                    <label>Quantité:</label>
                                    <div class="quantity-controls">
                                        <button class="qty-btn minus" data-id="<?php echo $article['idCommande']; ?>">-</button>
                                        <input type="number" value="<?php echo $article['nombreArticles']; ?>" min="1" max="10" class="qty-input" data-id="<?php echo $article['idCommande']; ?>">
                                        <button class="qty-btn plus" data-id="<?php echo $article['idCommande']; ?>">+</button>
                                    </div>
                                </div>
                                
                                <div class="item-price">
                                    <div class="unit-price"><?php echo formaterPrix($article['prix']); ?></div>
                                    <div class="total-price" data-unit-price="<?php echo $article['prix']; ?>">
                                        <?php echo formaterPrix($article['prix'] * $article['nombreArticles']); ?>
                                    </div>
                                </div>
                                
                                <div class="item-actions">
                                    <button class="btn-remove" data-id="<?php echo $article['idCommande']; ?>" title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="cart-summary">
                        <div class="summary-card">
                            <h3>Résumé de la commande</h3>
                            
                            <div class="summary-line">
                                <span>Sous-total:</span>
                                <span id="subtotal"><?php echo formaterPrix($total); ?></span>
                            </div>
                            
                            <div class="summary-line">
                                <span>Frais de livraison:</span>
                                <span>Gratuit</span>
                            </div>
                            
                            <div class="summary-line total-line">
                                <span>Total:</span>
                                <span id="total"><?php echo formaterPrix($total); ?></span>
                            </div>
                            
                            <div class="summary-actions">
                                <a href="oeuvres.php" class="btn-outline">
                                    <i class="fas fa-arrow-left"></i> Continuer mes achats
                                </a>
                                <button class="btn-primary checkout-btn">
                                    <i class="fas fa-credit-card"></i> Procéder au paiement
                                </button>
                            </div>
                        </div>
                        
                        <div class="promo-section">
                            <h4>Code promo</h4>
                            <div class="promo-input">
                                <input type="text" placeholder="Entrez votre code" id="promo-code">
                                <button class="btn-secondary" id="apply-promo">Appliquer</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="js/panier.js"></script>
</body>
</html>