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
        Gestion des Artisans
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Artisans</li>
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
            <div class="box-header with-border">
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Nouvel Artisan</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Photo</th>
                  <th>Nom</th>
                  <th>Email</th>
                  <th>Spécialité</th>
                  <th>Téléphone</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
                    // Requête pour récupérer les informations des artisans
                    $sql = "SELECT a.idArtisan, a.specialite, a.statut_verification, 
                            u.nom, u.prenom, u.email, u.telephone, u.photo 
                            FROM artisan a 
                            LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                            ORDER BY u.nom ASC, u.prenom ASC";
                    $query = $conn->query($sql);
                    
                    while($row = $query->fetch_assoc()){
                      $photo = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg';
                      $status = $row['statut_verification'] ? 
                                '<span class="label label-success">Vérifié</span>' : 
                                '<span class="label label-warning">En attente</span>';
                      
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idArtisan']."</td>
                          <td><img src='".$photo."' width='30px' height='30px' class='img-circle'></td>
                          <td>".$row['prenom'].' '.$row['nom']."</td>
                          <td>".$row['email']."</td>
                          <td>".$row['specialite']."</td>
                          <td>".$row['telephone']."</td>
                          <td>".$status."</td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idArtisan']."'><i class='fa fa-eye'></i> Voir</button>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['idArtisan']."'><i class='fa fa-edit'></i> Modifier</button>
                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['idArtisan']."'><i class='fa fa-trash'></i> Supprimer</button>
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
  <?php include 'includes/artisans_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  // Initialiser le DataTable
  if (!$.fn.DataTable.isDataTable('#example1')) {
    // Initialiser le DataTable seulement si ce n'est pas déjà fait
    $('#example1').DataTable({
      responsive: true,
      "order": [[ 2, "asc" ]] // Trier par nom
    });
  } else {
    // Si la table est déjà initialisée, on peut mettre à jour certaines options
    var table = $('#example1').DataTable();
    table.order([2, 'asc']).draw();
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
});

// Fonction pour récupérer les données d'un artisan
function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'artisans_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Remplir les champs du formulaire d'édition
      $('.artisan-id').val(response.idArtisan);
      $('#edit_specialite').val(response.specialite);
      $('#edit_certification').val(response.certification);
      $('#edit_portfolio').val(response.portfolio);
      $('#edit_statut').prop('checked', response.statut_verification == 1);
      
      // Pour le formulaire utilisateur associé
      $('#edit_nom').val(response.nom);
      $('#edit_prenom').val(response.prenom);
      $('#edit_email').val(response.email);
      $('#edit_telephone').val(response.telephone);
      $('#edit_adresse').val(response.adresse);
      $('#edit_pays').val(response.pays);
      $('#edit_ville').val(response.ville);
      $('#edit_code_postal').val(response.code_postal);
      $('#edit_genre').val(response.genre);
      
      // Remplir les informations pour la vue détaillée
      $('#view_nom').text(response.prenom + ' ' + response.nom);
      $('#view_email').text(response.email);
      $('#view_telephone').text(response.telephone || 'Non renseigné');
      $('#view_specialite').text(response.specialite || 'Non spécifiée');
      $('#view_certification').html(response.certification || '<em>Aucune certification</em>');
      $('#view_portfolio').html(response.portfolio || '<em>Aucun portfolio</em>');
      
      var adresse_complete = '';
      if(response.adresse) adresse_complete += response.adresse + '<br>';
      if(response.code_postal) adresse_complete += response.code_postal + ' ';
      if(response.ville) adresse_complete += response.ville + '<br>';
      if(response.pays) adresse_complete += response.pays;
      
      $('#view_adresse').html(adresse_complete || '<em>Non renseignée</em>');
      
      // Statut de vérification
      if(response.statut_verification == 1) {
        $('#view_statut').html('<span class="label label-success">Vérifié</span>');
      } else {
        $('#view_statut').html('<span class="label label-warning">En attente de vérification</span>');
      }
      
      // Photo
      var photo = response.photo ? '../images/' + response.photo : '../images/profile.jpg';
      $('#view_photo').attr('src', photo);
      
      // Pour la modal de suppression
      $('.artisan-nom').html(response.prenom + ' ' + response.nom);
    }
  });
}
</script>
</body>
</html>