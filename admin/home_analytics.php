<?php
include 'includes/session.php';

// Cette page peut être utilisée pour générer des statistiques avancées et des rapports

// Fonction pour calculer le taux de conversion (nombre de commandes / nombre de clients)
function getTauxConversion($conn) {
    $sql = "SELECT COUNT(DISTINCT idClient) as total_clients FROM client";
    $query = $conn->query($sql);
    $total_clients = $query->fetch_assoc()['total_clients'];
    
    $sql = "SELECT COUNT(DISTINCT idClient) as clients_avec_commandes FROM commande";
    $query = $conn->query($sql);
    $clients_avec_commandes = $query->fetch_assoc()['clients_avec_commandes'];
    
    if ($total_clients > 0) {
        return round(($clients_avec_commandes / $total_clients) * 100, 2);
    }
    return 0;
}

// Fonction pour calculer le panier moyen
function getPanierMoyen($conn) {
    $sql = "SELECT AVG(o.prix * c.nombreArticles) as panier_moyen
            FROM commande c 
            JOIN oeuvre o ON c.idOeuvre = o.idOeuvre";
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();
    
    return round($row['panier_moyen'] ?? 0, 2);
}

// Fonction pour obtenir les artisans les plus populaires
function getTopArtisans($conn, $limit = 5) {
    $sql = "SELECT a.idArtisan, u.prenom, u.nom, COUNT(c.idCommande) as nb_ventes,
            SUM(o.prix * c.nombreArticles) as ca_total
            FROM artisan a
            JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
            JOIN oeuvre o ON a.idArtisan = o.idArtisan
            JOIN commande c ON o.idOeuvre = c.idOeuvre
            GROUP BY a.idArtisan
            ORDER BY nb_ventes DESC
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $artisans = [];
    while ($row = $result->fetch_assoc()) {
        $artisans[] = $row;
    }
    
    return $artisans;
}

// Fonction pour obtenir les indicateurs clés de performance (KPI)
function getKPIs($conn) {
    $kpis = [];
    
    // Taux de conversion
    $kpis['taux_conversion'] = getTauxConversion($conn);
    
    // Panier moyen
    $kpis['panier_moyen'] = getPanierMoyen($conn);
    
    // Chiffre d'affaires mensuel
    $month_start = date('Y-m-01');
    $month_end = date('Y-m-t');
    
    $sql = "SELECT SUM(o.prix * c.nombreArticles) as ca_mensuel
            FROM commande c 
            JOIN oeuvre o ON c.idOeuvre = o.idOeuvre
            WHERE c.dateCommande BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $month_start, $month_end);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $kpis['ca_mensuel'] = $row['ca_mensuel'] ?? 0;
    
    // Nombre d'avis moyen par œuvre
    $sql = "SELECT AVG(nb_avis) as moyenne_avis
            FROM (
                SELECT o.idOeuvre, COUNT(a.idAvis) as nb_avis
                FROM oeuvre o
                LEFT JOIN avisoeuvre a ON o.idOeuvre = a.idOeuvre
                GROUP BY o.idOeuvre
            ) as sous_requete";
    
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();
    
    $kpis['moyenne_avis'] = round($row['moyenne_avis'] ?? 0, 2);
    
    // Note moyenne des œuvres
    $sql = "SELECT AVG(notation) as note_moyenne
            FROM avisoeuvre";
    
    $query = $conn->query($sql);
    $row = $query->fetch_assoc();
    
    $kpis['note_moyenne'] = round($row['note_moyenne'] ?? 0, 2);
    
    return $kpis;
}

// Calcul des indicateurs
$taux_conversion = getTauxConversion($conn);
$panier_moyen = getPanierMoyen($conn);
$top_artisans = getTopArtisans($conn);
$kpis = getKPIs($conn);

// Si la page est appelée via AJAX, renvoyer les données au format JSON
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    header('Content-Type: application/json');
    echo json_encode([
        'taux_conversion' => $taux_conversion,
        'panier_moyen' => $panier_moyen,
        'top_artisans' => $top_artisans,
        'kpis' => $kpis
    ]);
    exit;
}

// Sinon, continuer avec l'affichage HTML...
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Statistiques avancées</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.7 -->
    <link rel="stylesheet" href="bower_components/bootstrap/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="bower_components/font-awesome/css/font-awesome.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
    <!-- AdminLTE Skins -->
    <link rel="stylesheet" href="dist/css/skins/_all-skins.min.css">
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>

    <div class="content-wrapper">
        <section class="content-header">
            <h1>
                Statistiques avancées
            </h1>
            <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Accueil</a></li>
                <li class="active">Statistiques avancées</li>
            </ol>
        </section>

        <section class="content">
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-percent"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Taux de conversion</span>
                            <span class="info-box-number"><?php echo $taux_conversion; ?>%</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fa fa-shopping-cart"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Panier moyen</span>
                            <span class="info-box-number"><?php echo number_format($panier_moyen, 2, ',', ' '); ?> €</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-yellow"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">CA du mois</span>
                            <span class="info-box-number"><?php echo number_format($kpis['ca_mensuel'], 0, ',', ' '); ?> €</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-red"><i class="fa fa-star"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Note moyenne</span>
                            <span class="info-box-number"><?php echo $kpis['note_moyenne']; ?>/5 (<?php echo $kpis['moyenne_avis']; ?> avis/œuvre)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Top artisans</h3>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 10px">#</th>
                                        <th>Artisan</th>
                                        <th>Ventes</th>
                                        <th>Chiffre d'affaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($top_artisans as $artisan) {
                                        echo "
                                            <tr>
                                                <td>".$i.".</td>
                                                <td>".$artisan['prenom'].' '.$artisan['nom']."</td>
                                                <td>".$artisan['nb_ventes']."</td>
                                                <td>".number_format($artisan['ca_total'], 2, ',', ' ')." €</td>
                                            </tr>
                                        ";
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <?php include 'includes/footer.php'; ?>
</div>

<!-- jQuery 3 -->
<script src="bower_components/jquery/dist/jquery.min.js"></script>
<!-- Bootstrap 3.3.7 -->
<script src="bower_components/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.min.js"></script>
</body>
</html>