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
        Gestion des Commandes
      </h1>
      <ol class="breadcrumb">
        <li><a href="profil.php"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">Commandes</li>
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
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
                  <th>Date</th>
                  <th>Client</th>
                  <th>Œuvre</th>
                  <th>Prix</th>
                  <th>Quantité</th>
                  <th>Total</th>
                  <th>Statut</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
                    // Requête pour récupérer les commandes avec les informations associées
                    $sql = "SELECT c.idCommande, c.dateCommande, c.nombreArticles, c.statut, o.idArtisan,
                            u.nom AS nom_client, u.prenom AS prenom_client, 
                            o.titre AS titre_oeuvre, o.prix AS prix_oeuvre 
                            FROM commande c 
                            LEFT JOIN client cl ON c.idClient = cl.idClient 
                            LEFT JOIN utilisateur u ON cl.idClient = u.idUtilisateur 
                            LEFT JOIN oeuvre o ON c.idOeuvre = o.idOeuvre
                            WHERE o.idArtisan = {$_SESSION['artisan']}
                            ORDER BY c.dateCommande DESC";
                    $query = $conn->query($sql);
                    
                    while($row = $query->fetch_assoc()){
                      $prix_total = $row['nombreArticles'] * $row['prix_oeuvre'];
                      $status_class = '';
                      
                                            
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idCommande']."</td>
                          <td>".date('d/m/Y à H:i', strtotime($row['dateCommande']))."</td>
                          <td>".$row['prenom_client'].' '.$row['nom_client']."</td>
                          <td>".$row['titre_oeuvre']."</td>
                          <td>".number_format($row['prix_oeuvre'], 2, ',', ' ')." €</td>
                          <td>".$row['nombreArticles']."</td>
                          <td>".number_format($prix_total, 2, ',', ' ')." €</td>
                          <td>
                            <select class='form-control statut-select' data-id='".$row['idCommande']."'>
                              <option value='En attente' ".($row['statut']=='En attente'?'selected':'').">En attente</option>
                              <option value='Confirmée' ".($row['statut']=='Confirmée'?'selected':'').">Confirmée</option>
                              <option value='Expédiée' ".($row['statut']=='Expédiée'?'selected':'').">Expédiée</option>
                              <option value='Livrée' ".($row['statut']=='Livrée'?'selected':'').">Livrée</option>
                            </select>
                          </td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idCommande']."'><i class='fa fa-eye'></i> Voir</button>
                          </td>
                        </tr>
                      ";

                      // Définir la classe CSS en fonction du statut
                      switch($row['statut']) {
                        case 'En attente':
                          $status_class = 'label-warning';
                          break;
                        case 'Confirmée':
                          $status_class = 'label-primary';
                          break;
                        case 'Expédiée':
                          $status_class = 'label-info';
                          break;
                        case 'Livrée':
                          $status_class = 'label-success';
                          break;
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
  <?php include 'includes/commandes_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  // Initialiser le DataTable
  $('#example1').DataTable({
    responsive: true,
    "order": [[ 1, "desc" ]] // Trier par date (décroissant)
  });
  
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
  
  // Au changement de client dans le formulaire d'ajout
  $('#add_client').change(function(){
    getOeuvresDisponibles();
  });
  
  // Au changement d'oeuvre, afficher son prix
  $('#add_oeuvre').change(function(){
    var oeuvreId = $(this).val();
    getPrixOeuvre(oeuvreId);
  });
  
  $('#edit_oeuvre').change(function(){
    var oeuvreId = $(this).val();
    getPrixOeuvre(oeuvreId, 'edit');
  });
  
  // Calcul du total lors du changement de quantité
  $('#add_quantite').on('input', function(){
    calculerTotal('add');
  });
  
  $('#edit_quantite').on('input', function(){
    calculerTotal('edit');
  });


  //Envoie d'une requête AJAX pour mettre à jour le statut de la commande dans la BDD
  $(document).on('change', '.statut-select', function() {
  var idCommande = $(this).data('id');
  var nouveauStatut = $(this).val();

  $.ajax({
    type: 'POST',
    url: 'commandes_update_statut.php',
    data: {
      idCommande: idCommande,
      statut: nouveauStatut
    },
    success: function(response){
      // Afficher une alerte si succès
      console.log('Statut mis à jour avec succès.');
    },
    error: function(){
      // Afficher une alerte si erreur
      alert("Erreur lors de la mise à jour du statut.");
    }
  });
});
});

// Fonction pour récupérer les œuvres disponibles pour un client
function getOeuvresDisponibles() {
  var clientId = $('#add_client').val();
  $.ajax({
    type: 'POST',
    url: 'commandes_oeuvres.php',
    data: {clientId: clientId},
    dataType: 'json',
    success: function(response){
      $('#add_oeuvre').html(response);
      // Après chargement des œuvres, mettre à jour le prix
      var oeuvreId = $('#add_oeuvre').val();
      if(oeuvreId) {
        getPrixOeuvre(oeuvreId);
      }
    }
  });
}

// Fonction pour récupérer le prix d'une œuvre
function getPrixOeuvre(oeuvreId, type = 'add') {
  if(!oeuvreId) return;
  
  $.ajax({
    type: 'POST',
    url: 'commandes_prix.php',
    data: {oeuvreId: oeuvreId},
    dataType: 'json',
    success: function(response){
      if(type === 'add') {
        $('#add_prix').val(response.prix);
        calculerTotal('add');
      } else {
        $('#edit_prix').val(response.prix);
        calculerTotal('edit');
      }
    }
  });
}

// Fonction pour calculer le total
function calculerTotal(type) {
  var prix = parseFloat(type === 'add' ? $('#add_prix').val() : $('#edit_prix').val()) || 0;
  var quantite = parseInt(type === 'add' ? $('#add_quantite').val() : $('#edit_quantite').val()) || 0;
  var total = prix * quantite;
  
  if(type === 'add') {
    $('#add_total').val(total.toFixed(2));
  } else {
    $('#edit_total').val(total.toFixed(2));
  }
}

// Fonction pour récupérer les données d'une commande
function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'commandes_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Remplir les champs du formulaire d'édition
      $('.commande-id').val(response.idCommande);
      $('#edit_client').val(response.idClient);
      $('#edit_oeuvre').val(response.idOeuvre);
      $('#edit_prix').val(response.prix_oeuvre);
      $('#edit_quantite').val(response.nombreArticles);
      $('#edit_statut').val(response.statut);
      
      // Calculer le total
      calculerTotal('edit');
      
      // Remplir les informations de commande pour la modal de visualisation
      $('#view_date').html(new Date(response.dateCommande).toLocaleString());
      $('#view_client').html(response.prenom_client + ' ' + response.nom_client);
      $('#view_email').html(response.email_client);
      $('#view_telephone').html(response.telephone_client || 'Non renseigné');
      $('#view_adresse').html(response.adresse_client || 'Non renseignée');
      
      $('#view_oeuvre').html(response.titre_oeuvre);
      $('#view_description').html(response.description_oeuvre || 'Aucune description');
      $('#view_prix_unitaire').html(parseFloat(response.prix_oeuvre).toFixed(2) + ' €');
      $('#view_quantite').html(response.nombreArticles);
      $('#view_total').html((parseFloat(response.prix_oeuvre) * parseInt(response.nombreArticles)).toFixed(2) + ' €');
      
      var statusClass = '';
      switch(response.statut) {
        case 'En attente':
          statusClass = 'label-warning';
          break;
        case 'Confirmée':
          statusClass = 'label-primary';
          break;
        case 'Expédiée':
          statusClass = 'label-info';
          break;
        case 'Livrée':
          statusClass = 'label-success';
          break;
      }
      
      $('#view_statut').html('<span class="label ' + statusClass + '">' + response.statut + '</span>');
      
      // Pour la modal de suppression
      $('.commande-info').html('Commande #' + response.idCommande + ' - ' + response.prenom_client + ' ' + response.nom_client);
    }
  });
}
</script>
</body>
</html>