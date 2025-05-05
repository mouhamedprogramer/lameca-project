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
        Gestion des Œuvres
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Œuvres</li>
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
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat" style="max-width: 170px;"><i class="fa fa-plus"></i> Nouvelle Œuvre</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Image</th>
                  <th>Titre</th>
                  <th>Prix</th>
                  <th>Date de publication</th>
                  <th>Disponibilité</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
                    // Requête pour récupérer les œuvres avec les informations de l'artisan
                    $sql = "SELECT o.idOeuvre, o.titre, o.prix, o.datePublication, o.disponibilite, 
                            u.nom AS nom_artisan, u.prenom AS prenom_artisan 
                            FROM oeuvre o 
                            LEFT JOIN artisan a ON o.idArtisan = a.idArtisan 
                            LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
                            WHERE o.idArtisan = {$_SESSION['artisan']}
                            ORDER BY o.datePublication DESC";
                    $query = $conn->query($sql);
                    
                    while($row = $query->fetch_assoc()){
                      $disponibilite = $row['disponibilite'] ? 
                        '<span class="label label-success">Disponible</span>' : 
                        '<span class="label label-danger">Indisponible</span>';
                      
                      // Récupérer la première photo de l'œuvre
                      $sql = "SELECT url FROM photooeuvre WHERE idOeuvre = ? LIMIT 1";
                      $stmt = $conn->prepare($sql);
                      $stmt->bind_param("i", $row['idOeuvre']);
                      $stmt->execute();
                      $result = $stmt->get_result();
                      
                      $img_url = '';
                      if($result->num_rows > 0) {
                        $photo = $result->fetch_assoc();
                        $img_url = '../' . $photo['url'];
                      } else {
                        $img_url = '../images/oeuvre_default.jpg';
                      }
                      
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idOeuvre']."</td>
                          <td><img src='".$img_url."' width='50px' height='50px' class='img-thumbnail'></td>
                          <td>".$row['titre']."</td>
                          <td>".number_format($row['prix'], 2, ',', ' ')." €</td>
                          <td>".date('d/m/Y', strtotime($row['datePublication']))."</td>
                          <td>".$disponibilite."</td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idOeuvre']."'><i class='fa fa-eye'></i> Voir</button>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['idOeuvre']."'><i class='fa fa-edit'></i> Modifier</button>
                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['idOeuvre']."'><i class='fa fa-trash'></i> Supprimer</button>
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
  <?php include 'includes/produits_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
    
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
  
  $(document).on('click', '.delete-photo', function(e){
    e.preventDefault();
    $('#delete_photo').modal('show');
    var photoId = $(this).data('photo-id');
    var oeuvreId = $(this).data('oeuvre-id');
    var photoUrl = $(this).data('photo-url');
    
    $('#photo_id').val(photoId);
    $('#oeuvre_id').val(oeuvreId);
    $('#photo_to_delete').attr('src', '../' + photoUrl);
  });
});

// Fonction pour récupérer les données d'une œuvre
function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'produits_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Remplir les champs du formulaire d'édition
      $('.oeuvre-id').val(response.idOeuvre);
      $('#edit_titre').val(response.titre);
      $('#edit_description').val(response.description);
      $('#edit_prix').val(response.prix);
      $('#edit_caracteristiques').val(response.caracteristiques);
      $('#edit_disponibilite').prop('checked', response.disponibilite == 1);
      
      // Afficher les photos existantes dans le formulaire d'édition
      var photoContainer = $('#photo_container');
      photoContainer.empty();
      
      if(response.photos && response.photos.length > 0) {
        var photoHtml = '<div class="row">';
        $.each(response.photos, function(i, photo) {
          photoHtml += '<div class="col-sm-4" style="margin-bottom: 10px;">';
          photoHtml += '<div class="thumbnail">';
          photoHtml += '<img src="../' + photo.url + '" alt="Photo" style="height: 100px; width: 100%; object-fit: cover;">';
          photoHtml += '<div class="caption">';
          photoHtml += '<button type="button" class="btn btn-danger btn-xs delete-photo" data-photo-id="' + photo.idPhoto + '" data-oeuvre-id="' + response.idOeuvre + '" data-photo-url="' + photo.url + '"><i class="fa fa-trash"></i> Supprimer</button>';
          photoHtml += '</div></div></div>';
        });
        photoHtml += '</div>';
        photoContainer.html(photoHtml);
      } else {
        photoContainer.html('<div class="alert alert-info">Aucune photo disponible</div>');
      }
      
      // Remplir les informations pour la vue détaillée
      $('#view_titre').text(response.titre);
      $('#view_description').html(response.description || '<em>Aucune description disponible</em>');
      $('#view_prix').text(parseFloat(response.prix).toFixed(2) + ' €');
      $('#view_caracteristiques').html(response.caracteristiques || '<em>Aucune caractéristique spécifiée</em>');
      $('#view_date').text(new Date(response.datePublication).toLocaleDateString());
      
      // Statut de disponibilité
      if(response.disponibilite == 1) {
        $('#view_disponibilite').html('<span class="label label-success">Disponible</span>');
      } else {
        $('#view_disponibilite').html('<span class="label label-danger">Indisponible</span>');
      }
      
      // Gérer le carousel des photos
      var carouselIndicators = $('#carousel-indicators');
      var carouselInner = $('#carousel-inner');
      
      carouselIndicators.empty();
      carouselInner.empty();
      
      if(response.photos && response.photos.length > 0) {
        $.each(response.photos, function(i, photo) {
          // Ajouter l'indicateur
          carouselIndicators.append('<li data-target="#carouselPhotos" data-slide-to="' + i + '"' + (i === 0 ? ' class="active"' : '') + '></li>');
          
          // Ajouter la diapositive
          carouselInner.append(
            '<div class="item' + (i === 0 ? ' active' : '') + '">' +
            '<img src="../' + photo.url + '" alt="Photo ' + (i+1) + '" style="width:100%; max-height:300px; object-fit:contain;">' +
            '</div>'
          );
        });
      } else {
        // Pas de photos disponibles
        carouselInner.html(
          '<div class="item active">' +
          '<img src="../images/oeuvre_default.jpg" alt="Aucune photo" style="width:100%; max-height:300px; object-fit:contain;">' +
          '</div>'
        );
      }
      
      // Pour la modal de suppression
      $('.oeuvre-titre').html(response.titre);
    }
  });
}
</script>
</body>
</html>