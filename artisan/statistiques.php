<?php include 'includes/session.php'; ?>
<?php include 'includes/slugify.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Mes Statistiques
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Accueil</a></li>
        <li class="active">Statistiques</li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Erreur!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Succès!</h4>
              ".$_SESSION['success']."
            </div>
          ";
          unset($_SESSION['success']);
        }
      ?>
      
      <!-- Boîtes de statistiques principales -->
      <div class="row">
        <?php
          // Nombre total de clients
          $sql = "SELECT COUNT(DISTINCT c.idClient) as total
                  FROM client c
                  JOIN utilisateur u ON c.idClient = u.idUtilisateur
                  JOIN commande co ON u.idUtilisateur = co.idClient
                  JOIN oeuvre o ON co.idOeuvre = o.idOeuvre
                  WHERE o.idArtisan = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("i", $_SESSION['artisan']);
          $stmt->execute();
          $result = $stmt->get_result();
          $row = $result->fetch_assoc();
          $total_clients = $row['total'];

          //Nombre total de commandes
          $sql = "SELECT COUNT(DISTINCT idCommande) as total FROM commande c
                  JOIN oeuvre o ON c.idOeuvre=o.idOeuvre
                  WHERE o.idArtisan = {$_SESSION['artisan']}";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          $total_commandes = $row['total'];
          
          // Nombre total d'œuvres
          $sql = "SELECT COUNT(*) as total FROM oeuvre WHERE idArtisan = {$_SESSION['artisan']}";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          $total_oeuvres = $row['total'];
          
          // Chiffre d'affaires total
          $sql = "SELECT SUM(o.prix * c.nombreArticles) as ca_total 
                  FROM commande c 
                  JOIN oeuvre o ON c.idOeuvre = o.idOeuvre
                  WHERE o.idArtisan = {$_SESSION['artisan']}";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          $ca_total = $row['ca_total'] ?? 0;
          
          // Nombre d'événements à venir
          $sql = "SELECT COUNT(*) as total FROM evenement WHERE dateDebut >= CURDATE()";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          $evenements_a_venir = $row['total'];
        ?>
        
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?php echo $total_clients; ?></h3>
              <p>Nombre total de clients</p>
            </div>
            <div class="icon">
              <i class="fa fa-users"></i>
            </div>
            <a href="clients.php" class="small-box-footer">Plus d'informations <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green">
            <div class="inner">
              <h3><?php echo $total_commandes; ?></h3>
              <p>Nombre total de commandes</p>
            </div>
            <div class="icon">
              <i class="fa fa-shopping-cart"></i>
            </div>
            <a href="commandes.php" class="small-box-footer">Plus d'informations <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3><?php echo $total_oeuvres; ?></h3>
              <p>Œuvres publiées</p>
            </div>
            <div class="icon">
              <i class="fa fa-image"></i>
            </div>
            <a href="produits.php" class="small-box-footer">Plus d'informations <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
        
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-red">
            <div class="inner">
              <h3><?php echo number_format($ca_total, 0, ',', ' '); ?> €</h3>
              <p>Chiffre d'affaires total</p>
            </div>
            <div class="icon">
              <i class="fa fa-money"></i>
            </div>
            <a href="commandes.php" class="small-box-footer">Plus d'informations <i class="fa fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>
      
      <!-- Statistiques sur les commandes et chiffre d'affaires -->
      <div class="row">
        <div class="col-md-8">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Évolution des ventes et du chiffre d'affaires</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="chart">
                <canvas id="salesChart" style="height:250px"></canvas>
              </div>
            </div>
          </div>
        </div>
        
        <div class="col-md-4">
          <div class="box box-danger">
            <div class="box-header with-border">
              <h3 class="box-title">Répartition des commandes par statut</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
            </div>
            <div class="box-body">
              <?php
                // Requête pour obtenir le nombre de commandes par statut
                $sql = "SELECT statut, COUNT(*) as count 
                        FROM commande c
                        JOIN oeuvre o ON c.idOeuvre = o.idOeuvre
                        WHERE o.idArtisan = {$_SESSION['artisan']}
                        GROUP BY statut 
                        ORDER BY FIELD(statut, 'En attente', 'Confirmée', 'Expédiée', 'Livrée')";
                $query = $conn->query($sql);
                $statuts = [];
                $counts = [];
                
                while($row = $query->fetch_assoc()){
                    $statuts[] = $row['statut'];
                    $counts[] = $row['count'];
                }
                
                // Convertir en format JSON pour JavaScript
                $statuts_json = json_encode($statuts);
                $counts_json = json_encode($counts);
              ?>
              <canvas id="pieChart" style="height:250px"></canvas>
            </div>
          </div>
        </div>
      </div>
      
          
      <!-- Top des œuvres et activité récente -->
      <div class="row">
        <div class="col-md-6">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Top 5 des œuvres les plus vendues</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
            </div>
            <div class="box-body">
              <table class="table table-bordered">
                <thead>
                  <tr>
                    <th style="width: 10px">#</th>
                    <th>Titre</th>
                    <th>Artisan</th>
                    <th>Prix</th>
                    <th style="width: 60px">Ventes</th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                    // Requête pour obtenir les 5 œuvres les plus vendues
                    $sql = "SELECT o.idOeuvre, o.titre, o.prix, 
                            u.prenom, u.nom, 
                            COUNT(c.idCommande) as nb_ventes 
                            FROM oeuvre o 
                            LEFT JOIN commande c ON o.idOeuvre = c.idOeuvre 
                            LEFT JOIN artisan a ON o.idArtisan = a.idArtisan 
                            LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
                            WHERE o.idArtisan = {$_SESSION['artisan']}
                            GROUP BY o.idOeuvre 
                            ORDER BY nb_ventes DESC 
                            LIMIT 5";
                    $query = $conn->query($sql);
                    $i = 1;
                    
                    while($row = $query->fetch_assoc()){
                      // Déterminer la classe de progress bar selon le nombre de ventes
                      $progress_class = "progress-bar-green";
                      if($i == 1) $progress_class = "progress-bar-red";
                      if($i == 2) $progress_class = "progress-bar-yellow";
                      
                      echo "
                        <tr>
                          <td>".$i.".</td>
                          <td>".$row['titre']."</td>
                          <td>".$row['prenom'].' '.$row['nom']."</td>
                          <td>".number_format($row['prix'], 2, ',', ' ')." €</td>
                          <td>
                            <div class='progress progress-xs'>
                              <div class='progress-bar ".$progress_class."' style='width: ".($row['nb_ventes'] * 20)."%'></div>
                            </div>
                            <span class='badge bg-red'>".$row['nb_ventes']."</span>
                          </td>
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
        
        <div class="col-md-6">
          <div class="box box-warning">
            <div class="box-header with-border">
              <h3 class="box-title">Activité récente</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div class="table-responsive">
                <table class="table no-margin">
                  <thead>
                    <tr>
                      <th>Type</th>
                      <th>Description</th>
                      <th>Date</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      // Dernières commandes
                      $sql = "SELECT c.idCommande, c.dateCommande, c.statut, 
                              u.prenom, u.nom 
                              FROM commande c 
                              JOIN client cl ON c.idClient = cl.idClient 
                              JOIN utilisateur u ON cl.idClient = u.idUtilisateur 
                              ORDER BY c.dateCommande DESC 
                              LIMIT 3";
                      $query = $conn->query($sql);
                      
                      while($row = $query->fetch_assoc()){
                        echo "
                          <tr>
                            <td><span class='label label-success'>Commande</span></td>
                            <td>".$row['prenom'].' '.$row['nom']." a passé une commande (#".$row['idCommande'].")</td>
                            <td>".date('d/m/Y H:i', strtotime($row['dateCommande']))."</td>
                          </tr>
                        ";
                      }
                      
                                          
                      // Derniers avis
                      $sql = "SELECT ao.idAvis, ao.dateAvisoeuvre, ao.notation, 
                              u.prenom, u.nom, 
                              o.titre 
                              FROM avisoeuvre ao 
                              JOIN client c ON ao.idClient = c.idClient 
                              JOIN utilisateur u ON c.idClient = u.idUtilisateur 
                              JOIN oeuvre o ON ao.idOeuvre = o.idOeuvre 
                              ORDER BY ao.dateAvisoeuvre DESC 
                              LIMIT 3";
                      $query = $conn->query($sql);
                      
                      while($row = $query->fetch_assoc()){
                        $stars = "";
                        for($i = 1; $i <= 5; $i++){
                          if($i <= $row['notation']){
                            $stars .= "<i class='fa fa-star text-yellow'></i>";
                          } else {
                            $stars .= "<i class='fa fa-star-o text-yellow'></i>";
                          }
                        }
                        
                        echo "
                          <tr>
                            <td><span class='label label-warning'>Avis</span></td>
                            <td>".$row['prenom'].' '.$row['nom']." a noté ".$stars." l'œuvre \"".$row['titre']."\"</td>
                            <td>".date('d/m/Y H:i', strtotime($row['dateAvisoeuvre']))."</td>
                          </tr>
                        ";
                      }
                    ?>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="box-footer clearfix">
              <a href="commandes.php" class="btn btn-sm btn-info btn-flat pull-left">Voir toutes les commandes</a>
              <a href="avis.php" class="btn btn-sm btn-default btn-flat pull-right">Voir tous les avis</a>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Événements à venir et carte du monde -->
      <div class="row">
        <div class="col-md-7">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Événements à venir</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
            </div>
            <div class="box-body">
              <div id="calendar"></div>
            </div>
          </div>
        </div>
        
        <div class="col-md-5">
          <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Répartition géographique des clients</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
              </div>
            </div>
            <div class="box-body">
              <?php
                // Requête pour obtenir le nombre de clients par pays
                $sql = "SELECT u.pays, COUNT(*) as count 
                        FROM utilisateur u
                        JOIN client c ON c.idClient = u.idUtilisateur
                        JOIN commande co ON u.idUtilisateur = co.idClient
                        JOIN oeuvre o ON co.idOeuvre = o.idOeuvre
                        WHERE u.pays IS NOT NULL AND u.pays != '' AND o.idArtisan = {$_SESSION['artisan']}
                        GROUP BY u.pays 
                        ORDER BY count DESC";
                $query = $conn->query($sql);
                $pays = [];
                $pays_counts = [];
                
                while($row = $query->fetch_assoc()){
                    $pays[] = $row['pays'];
                    $pays_counts[] = $row['count'];
                }
                
                // Convertir en format JSON pour JavaScript
                $pays_json = json_encode($pays);
                $pays_counts_json = json_encode($pays_counts);
              ?>
              <canvas id="worldMap" style="height:250px"></canvas>
            </div>
          </div>
        </div>
      </div>
      
    </section>
  </div>
  
  <?php include 'includes/footer.php'; ?>

</div>
<?php include 'includes/scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.0/main.min.css" rel="stylesheet" />

<script>
$(function(){
  'use strict';

  // AREA CHART - Évolution des ventes et du CA
  var salesChartCanvas = document.getElementById('salesChart').getContext('2d');
  
  // Récupérer les données pour le graphique des 6 derniers mois
  $.ajax({
    url: 'stat_sales_data.php',
    method: 'GET',
    dataType: 'json',
    success: function(data) {
      var salesChart = new Chart(salesChartCanvas, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [
            {
              label: 'Chiffre d\'affaires (€)',
              backgroundColor: 'rgba(60,141,188,0.3)',
              borderColor: 'rgba(60,141,188,0.8)',
              pointRadius: 3,
              pointColor: '#3b8bba',
              pointStrokeColor: 'rgba(60,141,188,1)',
              pointHighlightFill: '#fff',
              pointHighlightStroke: 'rgba(60,141,188,1)',
              data: data.ca_data,
              yAxisID: 'y'
            },
            {
              label: 'Nombre de ventes',
              backgroundColor: 'rgba(210, 214, 222, 0.3)',
              borderColor: 'rgba(210, 214, 222, 0.8)',
              pointRadius: 3,
              pointColor: 'rgba(210, 214, 222, 1)',
              pointStrokeColor: '#c1c7d1',
              pointHighlightFill: '#fff',
              pointHighlightStroke: 'rgba(220,220,220,1)',
              data: data.sales_data,
              yAxisID: 'y1'
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              title: {
                display: true,
                text: 'Chiffre d\'affaires (€)'
              }
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              title: {
                display: true,
                text: 'Nombre de ventes'
              },
              grid: {
                drawOnChartArea: false
              }
            }
          }
        }
      });
    }
  });

  // PIE CHART - Répartition des commandes par statut
  var pieChartCanvas = document.getElementById('pieChart').getContext('2d');
  var pieChart = new Chart(pieChartCanvas, {
    type: 'pie',
    data: {
      labels: <?php echo $statuts_json; ?>,
      datasets: [
        {
          data: <?php echo $counts_json; ?>,
          backgroundColor: ['#f56954', '#00a65a', '#f39c12', '#00c0ef'],
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false
    }
  });

    // BAR CHART - Répartition géographique
  var worldMapCanvas = document.getElementById('worldMap').getContext('2d');
  var worldMap = new Chart(worldMapCanvas, {
    type: 'bar',
    data: {
      labels: <?php echo $pays_json; ?>,
      datasets: [
        {
          label: 'Nombre d\'utilisateurs',
          data: <?php echo $pays_counts_json; ?>,
          backgroundColor: 'rgba(60,141,188,0.8)',
          borderColor: 'rgba(60,141,188,1)',
          borderWidth: 1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            precision: 0
          }
        }
      }
    }
  });
  
  // CALENDAR - Événements
// CALENDAR - Événements et commandes
var calendarEl = document.getElementById('calendar');
var calendar = new FullCalendar.Calendar(calendarEl, {
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth'
  },
  locale: 'fr',
  initialView: 'dayGridMonth',
  events: function(info, successCallback, failureCallback) {
    // Récupérer les événements normaux
    $.ajax({
      url: 'stat_events_data.php',
      type: 'GET',
      dataType: 'json',
      success: function(eventsData) {
        // Récupérer les dates de commande
        $.ajax({
          url: 'commande_dates.php',
          type: 'GET',
          dataType: 'json',
          success: function(commandeData) {
            // Combiner les deux types d'événements
            successCallback(eventsData.concat(commandeData));
          },
          error: function() {
            failureCallback();
          }
        });
      },
      error: function() {
        failureCallback();
      }
    });
  },
  eventClick: function(info) {
    // Différencier le comportement selon le type d'événement
    if (info.event.extendedProps.type === 'commande') {
      // Afficher une liste des commandes pour cette date
      window.location.href = 'commandes.php?date=' + info.event.startStr;
    } else {
      // Rediriger vers la page de détail de l'événement
      window.location.href = 'evenements.php?view=' + info.event.id;
    }
  },
  eventContent: function(arg) {
    // Personnaliser l'affichage des événements
    if (arg.event.extendedProps.type === 'commande') {
      return {
        html: '<div class="fc-content"><span class="fc-title"><i class="fa fa-shopping-cart"></i> ' + arg.event.title + '</span></div>'
      };
    }
  }
});
calendar.render();
});
</script>
</body>
</html>



