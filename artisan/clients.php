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
        Gestion des Clients
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Clients</li>
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
      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-body">
              <table id="example1" class="table table-bordered table-hover">
                <thead>
                  <th class="hidden"></th>
                  <th>Photo</th>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Téléphone</th>
                  <th>Date d'inscription</th>
                  <th>Commandes</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
                    // Requête pour récupérer les informations des clients
                    $sql = "SELECT DISTINCT c.idClient, 
                            u.nom, u.prenom, u.email, u.telephone, u.photo, u.date_naissance,
                            u.idUtilisateur, DATE(u.date_creation) as date_creation
                            FROM client c 
                            LEFT JOIN utilisateur u ON c.idClient = u.idUtilisateur
                            JOIN commande co ON u.idUtilisateur = co.idClient
                            JOIN oeuvre o ON co.idOeuvre = o.idOeuvre
                            WHERE o.idArtisan = {$_SESSION['artisan']}
                            ORDER BY u.nom ASC, u.prenom ASC";
                    $query = $conn->query($sql);

                    while($row = $query->fetch_assoc()){
                      $photo = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg';
                      
                      // Compter le nombre de commandes
                      $sqlCount = "SELECT COUNT(*) as nb_commandes FROM commande WHERE idClient = ?";
                      $stmtCount = $conn->prepare($sqlCount);
                      $stmtCount->bind_param("i", $row['idClient']);
                      $stmtCount->execute();
                      $resultCount = $stmtCount->get_result();
                      $rowCount = $resultCount->fetch_assoc();
                      $nb_commandes = $rowCount['nb_commandes'];
                      
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idClient']."</td>
                          <td><img src='".$photo."' width='30px' height='30px' class='img-circle'></td>
                          <td>".$row['prenom'].' '.$row['nom']."</td>
                          <td>".$row['email']."</td>
                          <td>".$row['telephone']."</td>
                          <td>".date('d/m/Y', strtotime($row['date_creation']))."</td>
                          <td>
                            <span class='badge ".($nb_commandes > 0 ? "bg-green" : "bg-gray")."'>".$nb_commandes."</span>
                            ".($nb_commandes > 0 ? "<a href='#' class='view-orders btn-xs' data-id='".$row['idClient']."'><i class='fa fa-eye'></i></a>" : "")."
                          </td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idClient']."'><i class='fa fa-eye'></i> Voir</button>
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
    </div>

    
  <?php include 'includes/footer.php'; ?>
  <?php include 'includes/clients_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(function(){
  // Vérifier si la table est déjà une instance de DataTable
  if (!$.fn.DataTable.isDataTable('#example1')) {
    var table = $('#example1').DataTable({
      'responsive': true,
      'language': {
        'lengthMenu': 'Afficher _MENU_ clients par page',
        'zeroRecords': 'Aucun client trouvé',
        'info': 'Page _PAGE_ sur _PAGES_',
        'infoEmpty': 'Aucun client disponible',
        'infoFiltered': '(filtré de _MAX_ clients au total)',
        'search': 'Rechercher:',
        'paginate': {
          'first': 'Premier',
          'last': 'Dernier',
          'next': 'Suivant',
          'previous': 'Précédent'
        }
      }
    });
  } else {
    var table = $('#example1').DataTable();
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
  
  $(document).on('click', '.view-orders', function(e){
    e.preventDefault();
    $('#client_orders').modal('show');
    var id = $(this).data('id');
    getClientOrders(id);
  });
  
  // Graphique d'évolution des inscriptions
  $.ajax({
    url: 'clients_chart_data.php',
    method: 'GET',
    dataType: 'json',
    success: function(data) {
      var ctx = document.getElementById('clientChart').getContext('2d');
      var clientChart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Nouveaux clients',
            data: data.data,
            fill: false,
            borderColor: '#00c0ef',
            tension: 0.1
          }]
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
    }
  });
});

// Fonction pour récupérer les données d'un client
function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'clients_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Remplir les champs du formulaire d'édition
      $('.client-id').val(response.idClient);
      $('#edit_nom').val(response.nom);
      $('#edit_prenom').val(response.prenom);
      $('#edit_email').val(response.email);
      $('#edit_telephone').val(response.telephone);
      $('#edit_adresse').val(response.adresse);
      $('#edit_pays').val(response.pays);
      $('#edit_ville').val(response.ville);
      $('#edit_code_postal').val(response.code_postal);
      $('#edit_date_naissance').val(response.date_naissance);
      $('#edit_genre').val(response.genre);
      
      // Remplir les informations pour la vue détaillée
      $('#view_nom').text(response.prenom + ' ' + response.nom);
      $('#view_email').text(response.email);
      $('#view_telephone').text(response.telephone || 'Non renseigné');
      
      var adresse_complete = '';
      if(response.adresse) adresse_complete += response.adresse + '<br>';
      if(response.code_postal) adresse_complete += response.code_postal + ' ';
      if(response.ville) adresse_complete += response.ville + '<br>';
      if(response.pays) adresse_complete += response.pays;
      
      $('#view_adresse').html(adresse_complete || '<em>Non renseignée</em>');
      
      if(response.date_naissance) {
        var dob = new Date(response.date_naissance);
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
          age--;
        }
        $('#view_age').text(age + ' ans');
        $('#view_date_naissance').text(dob.toLocaleDateString());
      } else {
        $('#view_age').text('Non renseigné');
        $('#view_date_naissance').text('Non renseignée');
      }
      
      $('#view_genre').text(response.genre || 'Non renseigné');
      
      // Photo
      var photo = response.photo ? '../images/' + response.photo : '../images/profile.jpg';
      $('#view_photo').attr('src', photo);
      
      // Pour la modal de suppression
      $('.client-nom').html(response.prenom + ' ' + response.nom);
      
      // Statistiques client
      $('#view_date_creation').text(new Date(response.date_creation).toLocaleDateString());
      $('#view_nb_commandes').text(response.nb_commandes);
      $('#view_total_depense').text(parseFloat(response.total_depense).toFixed(2) + ' €');
      $('#view_derniere_commande').text(response.derniere_commande ? new Date(response.derniere_commande).toLocaleDateString() : 'Jamais');
    }
  });
}

// Fonction pour récupérer les commandes d'un client
function getClientOrders(id){
  $.ajax({
    type: 'POST',
    url: 'clients_orders.php',
    data: {id:id},
    dataType: 'html',
    success: function(response){
      $('#orders_list').html(response);
    }
  });
}
</script>
</body>
</html>