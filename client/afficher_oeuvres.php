<?php
// Connexion à la base de données
$host = 'localhost';
$user = 'root';
$pass = 'root'; // ou 'root' selon MAMP/XAMPP
$dbname = 'lameca';
$port = 3306; // important si tu es sur MAMP

$conn = new mysqli($host, $user, $pass, $dbname, $port);

// Vérifie la connexion
if ($conn->connect_error) {
    die("Erreur de connexion : " . $conn->connect_error);
}

// Requête pour récupérer toutes les œuvres
$sql = "SELECT * FROM Oeuvre ORDER BY datePublication DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Œuvres disponibles</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 15px;
            width: 300px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .card h2 {
            margin-top: 0;
        }
        .prix {
            color: #007BFF;
            font-weight: bold;
        }
        .disponible {
            color: green;
        }
        .indisponible {
            color: red;
        }
    </style>
</head>
<body>

<h1>Liste des œuvres</h1>

<div class="container">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<div class="card">';
            echo '<h2>' . htmlspecialchars($row['titre']) . '</h2>';
            echo '<p><strong>Description :</strong> ' . nl2br(htmlspecialchars($row['description'])) . '</p>';
            echo '<p><strong>Caractéristiques :</strong> ' . htmlspecialchars($row['caracteristiques']) . '</p>';
            echo '<p><strong>Date de publication :</strong> ' . htmlspecialchars($row['datePublication']) . '</p>';
            echo '<p class="prix">Prix : ' . number_format($row['prix'], 2) . ' €</p>';
            echo '<p><strong>Disponibilité :</strong> <span class="' . 
                 ($row['disponibilite'] ? 'disponible' : 'indisponible') . '">' .
                 ($row['disponibilite'] ? 'Disponible' : 'Indisponible') . '</span></p>';
            echo '<p><strong>ID Artisan :</strong> ' . htmlspecialchars($row['idArtisan']) . '</p>';
            echo '</div>';
        }
    } else {
        echo '<p>Aucune œuvre trouvée.</p>';
    }

    $conn->close();
    ?>
</div>

</body>
</html>
