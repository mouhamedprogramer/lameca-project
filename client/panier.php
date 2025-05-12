<?php 
// Connexion à la base de données
require_once 'includes/conn.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions Générales d'Utilisation</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="Styles/panier.css">

</head>
<body>

<?php include 'includes/header.php'; ?>

<!-- Titre -->
<section class="section oeuvres-section">
    <div class="section-header">
        <h2>Panier</h2>
    </div>
</section>

<div class="container">
    
    <h2>Mon panier (... article)</h2>
    
    <div class="encadre">
        <h2>Accéder à mon compte</h2>
        <button class="btn-noir" onclick="window.location.href='connexion.php'">Connexion</button>
    </div>


</div>
<br><br><br>
<?php require_once 'includes/footer.php'; ?>


