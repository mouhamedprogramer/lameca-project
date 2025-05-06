<?php include 'includes/session.php'; ?>
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
        Gestion des Signalements
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Signalements</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">

          <!-- Section des statistiques -->
<div class="row">
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour les signalements en attente -->
    <div class="small-box bg-yellow">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM signalement WHERE statut = 'En attente'";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Signalements en attente</p>
      </div>
      <div class="icon">
        <i class="fa fa-clock-o"></i>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour les signalements résolus -->
    <div class="small-box bg-green">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM signalement WHERE statut = 'Résolu'";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Signalements résolus</p>
      </div>
      <div class="icon">
        <i class="fa fa-check"></i>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour les signalements rejetés -->
    <div class="small-box bg-red">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM signalement WHERE statut = 'Rejeté'";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Signalements rejetés</p>
      </div>
      <div class="icon">
        <i class="fa fa-times"></i>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour le total des signalements -->
    <div class="small-box bg-blue">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM signalement";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Total des signalements</p>
      </div>
      <div class="icon">
        <i class="fa fa-flag"></i>
      </div>
    </div>
  </div>
</div>
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
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-header with-border">
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat" style="max-width: 170px;"><i class="fa fa-plus"></i> Nouveau Signalement</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Date</th>
                  <th>Signaleur</th>
                  <th>Type Cible</th>
                  <th>Cible</th>
                  <th>Motif</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
                    // Requête pour récupérer les informations des signalements
                    $sql = "SELECT s.*, 
                            us.nom as signaleur_nom, us.prenom as signaleur_prenom,
                            uc.nom as cible_nom, uc.prenom as cible_prenom,
                            o.titre as oeuvre_titre
                            FROM signalement s 
                            LEFT JOIN utilisateur us ON s.idSignaleur = us.idUtilisateur
                            LEFT JOIN utilisateur uc ON s.idCible = uc.idUtilisateur AND s.typeCible = 'Utilisateur'
                            LEFT JOIN oeuvre o ON s.idCible = o.idOeuvre AND s.typeCible = 'Oeuvre'
                            ORDER BY s.dateSignalement DESC";
                    $query = $conn->query($sql);
                    
                    while($row = $query->fetch_assoc()){
                      // Format de la date
                      $date = date('d/m/Y H:i', strtotime($row['dateSignalement']));
                      
                      // Nom du signaleur
                      $signaleur = $row['signaleur_prenom'].' '.$row['signaleur_nom'];
                      
                      // Type et nom de la cible
                      $typeCible = $row['typeCible'];
                      $cible = '';
                      
                      if($typeCible == 'Utilisateur' && !empty($row['cible_nom'])){
                        $cible = $row['cible_prenom'].' '.$row['cible_nom'];
                      } else if($typeCible == 'Oeuvre' && !empty($row['oeuvre_titre'])){
                        $cible = $row['oeuvre_titre'];
                      } else {
                        $cible = 'ID: '.$row['idCible'];
                      }
                      
                      // Motif (tronqué si trop long)
                      $motif = strlen($row['motif']) > 50 ? substr($row['motif'], 0, 50)."..." : $row['motif'];
                      
                      // Statut avec label de couleur
                      switch($row['statut']){
                        case 'En attente':
                          $statut_label = '<span class="label label-warning">'.$row['statut'].'</span>';
                          break;
                        case 'Résolu':
                          $statut_label = '<span class="label label-success">'.$row['statut'].'</span>';
                          break;
                        case 'Rejeté':
                          $statut_label = '<span class="label label-danger">'.$row['statut'].'</span>';
                          break;
                        default:
                          $statut_label = '<span class="label label-default">'.$row['statut'].'</span>';
                      }
                      
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idSignalement']."</td>
                          <td>".$date."</td>
                          <td>".$signaleur."</td>
                          <td>".$typeCible."</td>
                          <td>".$cible."</td>
                          <td>".$motif."</td>
                          <td>".$statut_label."</td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idSignalement']."'><i class='fa fa-eye'></i> Voir</button>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['idSignalement']."'><i class='fa fa-edit'></i> Modifier</button>
                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['idSignalement']."'><i class='fa fa-trash'></i> Supprimer</button>
                          </td>
                        </tr>
                      ";
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
  <?php include 'includes/signalements_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  // Initialiser le DataTable
  if (!$.fn.DataTable.isDataTable('#example1')) {
    $('#example1').DataTable({
      responsive: true,
      "order": [[ 1, "desc" ]] // Trier par date (descendant)
    });
  } else {
    var table = $('#example1').DataTable();
    table.order([1, 'desc']).draw();
  }
  
  // Actions des boutons
  $(document).on('click', '.edit', function(e){
    e.preventDefault();
    $('#edit').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $(document).on('click', '.delete', function(e){
    e.preventDefault();
    $('#delete').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });
  
  $(document).on('click', '.view', function(e){
    e.preventDefault();
    $('#view').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  // Afficher les champs en fonction du type de cible sélectionné
  $('#type_cible, #edit_type_cible').change(function(){
    var type = $(this).val();
    if(type == 'Utilisateur'){
      $('.oeuvre-select').hide();
      $('.utilisateur-select').show();
    } else if(type == 'Oeuvre'){
      $('.utilisateur-select').hide();
      $('.oeuvre-select').show();
    }
  });
});

// Fonction pour récupérer les données d'un signalement
function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'signalements_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Remplir les champs du formulaire d'édition
      $('.signalement-id').val(response.idSignalement);
      $('#edit_type_cible').val(response.typeCible);
      $('#edit_motif').val(response.motif);
      $('#edit_statut').val(response.statut);
      
      // Mettre à jour les champs de sélection en fonction du type de cible
      if(response.typeCible == 'Utilisateur'){
        $('.oeuvre-select').hide();
        $('.utilisateur-select').show();
        $('#edit_utilisateur').val(response.idCible);
      } else if(response.typeCible == 'Oeuvre'){
        $('.utilisateur-select').hide();
        $('.oeuvre-select').show();
        $('#edit_oeuvre').val(response.idCible);
      }
      
      // Remplir les informations pour la vue détaillée
      var dateFormat = new Date(response.dateSignalement).toLocaleString('fr-FR');
      $('#view_date').text(dateFormat);
      $('#view_signaleur').text(response.signaleur_nom);
      $('#view_type_cible').text(response.typeCible);
      
      if(response.typeCible == 'Utilisateur'){
        $('#view_cible').text(response.cible_nom);
      } else if(response.typeCible == 'Oeuvre'){
        $('#view_cible').text(response.oeuvre_titre);
      }
      
      $('#view_motif').text(response.motif);
      
      // Statut avec label de couleur
      switch(response.statut){
        case 'En attente':
          $('#view_statut').html('<span class="label label-warning">'+response.statut+'</span>');
          break;
        case 'Résolu':
          $('#view_statut').html('<span class="label label-success">'+response.statut+'</span>');
          break;
        case 'Rejeté':
          $('#view_statut').html('<span class="label label-danger">'+response.statut+'</span>');
          break;
        default:
          $('#view_statut').html('<span class="label label-default">'+response.statut+'</span>');
      }
      
      // Pour la modal de suppression
      $('.signalement-date').html(dateFormat);
    }
  });
}




$(function(){
  // Récupération des données pour les graphiques
  $.ajax({
    url: 'signalements_chart_data.php',
    method: 'GET',
    dataType: 'json',
    success: function(data){
      // Graphique par type de cible (Pie Chart)
      var pieChartCanvas = $("#pieChart").get(0).getContext("2d");
      var pieChart = new Chart(pieChartCanvas, {
        type: 'pie',
        data: {
          labels: data.typeCible.labels,
          datasets: [{
            data: data.typeCible.data,
            backgroundColor: ['#f56954', '#00a65a']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            position: 'bottom'
          }
        }
      });
      
      // Graphique par statut (Donut Chart)
      var donutChartCanvas = $("#donutChart").get(0).getContext("2d");
      var donutChart = new Chart(donutChartCanvas, {
        type: 'doughnut',
        data: {
          labels: data.statut.labels,
          datasets: [{
            data: data.statut.data,
            backgroundColor: ['#f39c12', '#00c0ef', '#dd4b39']
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          legend: {
            position: 'bottom'
          }
        }
      });
      
      // Graphique d'évolution temporelle (Line Chart)
      var lineChartCanvas = $("#lineChart").get(0).getContext("2d");
      var lineChart = new Chart(lineChartCanvas, {
        type: 'line',
        data: {
          labels: data.timeline.labels,
          datasets: [{
            label: 'Nombre de signalements',
            data: data.timeline.data,
            fill: false,
            borderColor: '#3c8dbc',
            tension: 0.1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            xAxes: [{
              gridLines: {
                display: false
              }
            }],
            yAxes: [{
              ticks: {
                beginAtZero: true,
                stepSize: 1
              }
            }]
          }
        }
      });
      
      // Graphique des motifs fréquents (Bar Chart)
      var barChartCanvas = $("#barChart").get(0).getContext("2d");
      var barChart = new Chart(barChartCanvas, {
        type: 'horizontalBar',
        data: {
          labels: data.motifs.labels,
          datasets: [{
            label: 'Occurrences',
            data: data.motifs.data,
            backgroundColor: '#f39c12'
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            xAxes: [{
              ticks: {
                beginAtZero: true,
                stepSize: 1
              }
            }]
          },
          legend: {
            display: false
          }
        }
      });
    },
    error: function(err){
      console.log('Erreur lors du chargement des données:', err);
    }
  });
});
</script>
</body>
</html>