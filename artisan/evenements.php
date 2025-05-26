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
        Gestion des Événements
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Événements</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">

        <!-- Section des statistiques -->
<div class="row">
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour les événements à venir -->
    <div class="small-box bg-green">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM evenement WHERE dateDebut >= CURDATE()";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Événements à venir</p>
      </div>
      <div class="icon">
        <i class="fa fa-calendar-plus-o"></i>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour les événements passés -->
    <div class="small-box bg-gray">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM evenement WHERE dateDebut < CURDATE()";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Événements passés</p>
      </div>
      <div class="icon">
        <i class="fa fa-calendar-check-o"></i>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour les événements mis en avant -->
    <div class="small-box bg-yellow">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM evenement WHERE mis_en_avant = 1";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Événements mis en avant</p>
      </div>
      <div class="icon">
        <i class="fa fa-star"></i>
      </div>
    </div>
  </div>
  
  <div class="col-lg-3 col-xs-6">
    <!-- Petit widget pour le total des événements -->
    <div class="small-box bg-blue">
      <div class="inner">
        <?php
          $sql = "SELECT COUNT(*) as count FROM evenement";
          $query = $conn->query($sql);
          $row = $query->fetch_assoc();
          echo "<h3>".$row['count']."</h3>";
        ?>
        <p>Total des événements</p>
      </div>
      <div class="icon">
        <i class="fa fa-calendar"></i>
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
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat" style="max-width: 170px;"><i class="fa fa-plus"></i> Nouvel Événement</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Nom</th>
                  <th>Description</th>
                  <th>Début</th>
                  <th>Fin</th>
                  <th>Lieu</th>
                  <th>Artisan</th>
                  <th>Mis en avant</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
// Requête pour récupérer les informations des événements
$sql = "SELECT e.idArtisan AS idArtisanEvenement, e.*, u.nom, u.prenom
        FROM evenement e 
        JOIN artisan a ON e.idArtisan = a.idArtisan
        JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
        ORDER BY e.dateDebut DESC";

$query = $conn->query($sql);

while ($row = $query->fetch_assoc()) {
    $mis_en_avant = $row['mis_en_avant'] ? 
        '<span class="label label-success">Oui</span>' : 
        '<span class="label label-default">Non</span>';

    $dateDebut = date('d/m/Y', strtotime($row['dateDebut']));
    $dateFin = !empty($row['dateFin']) ? date('d/m/Y', strtotime($row['dateFin'])) : 'Non définie';
    $artisan = !empty($row['prenom']) ? $row['prenom'] . ' ' . $row['nom'] : 'Non assigné';

    $description = !empty($row['description']) ? 
        (strlen($row['description']) > 50 ? substr($row['description'], 0, 50) . "..." : $row['description']) : 
        'Aucune description';

    echo "<tr>
        <td class='hidden'>" . $row['idEvenement'] . "</td>
        <td>" . htmlspecialchars($row['nomEvenement']) . "</td>
        <td>" . htmlspecialchars($description) . "</td>
        <td>" . $dateDebut . "</td>
        <td>" . $dateFin . "</td>
        <td>" . htmlspecialchars($row['lieu']) . "</td>
        <td>" . htmlspecialchars($artisan) . "</td>
        <td>" . $mis_en_avant . "</td>
        <td>";

    // Bouton Voir
    echo "<button class='btn btn-info btn-sm view btn-flat' style='margin-right:5px;' data-id='" . htmlspecialchars($row['idEvenement'], ENT_QUOTES, 'UTF-8') . "'>
            <i class='fa fa-eye'></i> Voir
          </button>";

    // Affichage conditionnel des boutons Modifier / Supprimer
    if (isset($_SESSION['artisan']) && isset($row['idArtisanEvenement']) && (int)$row['idArtisanEvenement'] === (int)$_SESSION['artisan']) {
        echo "<button class='btn btn-success btn-sm edit btn-flat' style='margin-right:5px;' data-id='" . htmlspecialchars($row['idEvenement'], ENT_QUOTES, 'UTF-8') . "'>
                <i class='fa fa-edit'></i> Modifier
              </button>
              <button class='btn btn-danger btn-sm delete btn-flat' data-id='" . htmlspecialchars($row['idEvenement'], ENT_QUOTES, 'UTF-8') . "'>
                <i class='fa fa-trash'></i> Supprimer
              </button>";
    }

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
  <?php include 'includes/evenements_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  // Initialiser le DataTable
  if (!$.fn.DataTable.isDataTable('#example1')) {
    $('#example1').DataTable({
      responsive: true,
      "order": [[ 3, "desc" ]] // Trier par date de début (descendant)
    });
  } else {
    var table = $('#example1').DataTable();
    table.order([3, 'desc']).draw();
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

  // Ajouter un datepicker aux champs de date
  $('.datepicker').datepicker({
    autoclose: true,
    format: 'yyyy-mm-dd',
    todayHighlight: true
  });
});


// Fonction pour récupérer les données d'un événement
function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'evenements_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Remplir les champs du formulaire d'édition
      $('.evenement-id').val(response.idEvenement);
      $('#edit_nom').val(response.nomEvenement);
      $('#edit_description').val(response.description);
      $('#edit_debut').val(response.dateDebut);
      $('#edit_fin').val(response.dateFin);
      $('#edit_lieu').val(response.lieu);
      $('#edit_artisan').val(response.idArtisan);
      $('#edit_mis_en_avant').prop('checked', response.mis_en_avant == 1);
      
      // Remplir les informations pour la vue détaillée
      $('#view_nom').text(response.nomEvenement);
      $('#view_description').html(response.description || '<em>Aucune description</em>');
      $('#view_debut').text(response.dateDebut ? new Date(response.dateDebut).toLocaleDateString('fr-FR') : 'Non définie');
      $('#view_fin').text(response.dateFin ? new Date(response.dateFin).toLocaleDateString('fr-FR') : 'Non définie');
      $('#view_lieu').text(response.lieu || 'Non défini');
      $('#view_artisan').text(response.artisan_nom || 'Non assigné');
      
      // Statut mis en avant
      if(response.mis_en_avant == 1) {
        $('#view_mis_en_avant').html('<span class="label label-success">Oui</span>');
      } else {
        $('#view_mis_en_avant').html('<span class="label label-default">Non</span>');
      }
      
      // Pour la modal de suppression
      $('.evenement-nom').html(response.nomEvenement);
    }
  });
}
</script>
</body>
</html>