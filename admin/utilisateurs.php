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
        Utilisateurs
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Utilisateurs</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">
      <?php
        if(isset($_SESSION['error'])){
          echo "
            <div class='alert alert-danger alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-warning'></i> Error!</h4>
              ".$_SESSION['error']."
            </div>
          ";
          unset($_SESSION['error']);
        }
        if(isset($_SESSION['success'])){
          echo "
            <div class='alert alert-success alert-dismissible'>
              <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
              <h4><i class='icon fa fa-check'></i> Success!</h4>
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
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> Nouvel utilisateur</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Photo</th>
                  <th>Nom</th>
                  <th>Prénom</th>
                  <th>Email</th>
                  <th>Téléphone</th>
                  <th>Rôle</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT * FROM utilisateur ORDER BY nom ASC";
                    $query = $conn->query($sql);
                    while($row = $query->fetch_assoc()){
                      $photo = (!empty($row['photo'])) ? '../images/'.$row['photo'] : '../images/profile.jpg';
                      
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idUtilisateur']."</td>
                          <td><img src='".$photo."' width='30px' height='30px' class='img-circle'></td>
                          <td>".$row['nom']."</td>
                          <td>".$row['prenom']."</td>
                          <td>".$row['email']."</td>
                          <td>".$row['telephone']."</td>
                          <td>".$row['role']."</td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idUtilisateur']."'><i class='fa fa-eye'></i> Voir</button>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['idUtilisateur']."'><i class='fa fa-edit'></i> Modifier</button>
                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['idUtilisateur']."'><i class='fa fa-trash'></i> Supprimer</button>
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
  <?php include 'includes/utilisateurs_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
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

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'utilisateurs_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('.userid').val(response.idUtilisateur);
      $('.fullname').html(response.prenom + ' ' + response.nom);
      
      $('#edit_nom').val(response.nom);
      $('#edit_prenom').val(response.prenom);
      $('#edit_email').val(response.email);
      $('#edit_telephone').val(response.telephone);
      $('#edit_adresse').val(response.adresse);
      $('#edit_ville').val(response.ville);
      $('#edit_pays').val(response.pays);
      $('#edit_code_postal').val(response.code_postal);
      $('#edit_date_naissance').val(response.date_naissance);
      $('#edit_genre').val(response.genre);
      $('#edit_role').val(response.role);
      
      // Pour la vue détaillée
      $('#view_fullname').html(response.prenom + ' ' + response.nom);
      $('#view_email').html(response.email);
      $('#view_telephone').html(response.telephone || 'Non renseigné');
      $('#view_role').html(response.role);
      $('#view_genre').html(response.genre || 'Non renseigné');
      $('#view_date_naissance').html(response.date_naissance ? new Date(response.date_naissance).toLocaleDateString() : 'Non renseignée');
      $('#view_adresse').html(response.adresse || 'Non renseignée');
      $('#view_ville').html(response.ville || 'Non renseignée');
      $('#view_code_postal').html(response.code_postal || 'Non renseigné');
      $('#view_pays').html(response.pays || 'Non renseigné');
      
      var photo = response.photo ? '../images/' + response.photo : '../images/profile.jpg';
      $('#view_photo').attr('src', photo);
    }
  });
}
</script>
</body>
</html>