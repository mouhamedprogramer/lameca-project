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
        Gestion des FAQ
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active">FAQ</li>
      </ol>
    </section>
    <!-- Main content -->
    <section class="content">

      <!-- Section des statistiques -->
      <div class="row">
        <div class="col-lg-6 col-xs-12">
          <!-- Petit widget pour le total des FAQ -->
          <div class="small-box bg-blue">
            <div class="inner">
              <?php
                $sql = "SELECT COUNT(*) as count FROM faq";
                $query = $conn->query($sql);
                $row = $query->fetch_assoc();
                echo "<h3>".$row['count']."</h3>";
              ?>
              <p>Total des Questions FAQ</p>
            </div>
            <div class="icon">
              <i class="fa fa-question-circle"></i>
            </div>
          </div>
        </div>
        
        <div class="col-lg-6 col-xs-12">
          <!-- Petit widget pour les FAQ récentes -->
          <div class="small-box bg-green">
            <div class="inner">
              <?php
                $sql = "SELECT COUNT(*) as count FROM faq ORDER BY idFaq DESC LIMIT 5";
                $query = $conn->query($sql);
                $recent_count = $query->num_rows;
                echo "<h3>".$recent_count."</h3>";
              ?>
              <p>FAQ Récentes (5 dernières)</p>
            </div>
            <div class="icon">
              <i class="fa fa-clock-o"></i>
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
              <a href="#addnew" data-toggle="modal" class="btn btn-primary btn-sm btn-flat" style="max-width: 170px;"><i class="fa fa-plus"></i> Nouvelle FAQ</a>
            </div>
            <div class="box-body">
              <table id="example1" class="table table-bordered">
                <thead>
                  <th class="hidden"></th>
      
                  <th>Question</th>
                  <th>Réponse</th>
                  <th>Actions</th>
                </thead>
                <tbody>
                  <?php
                    $sql = "SELECT * FROM faq ORDER BY idFaq DESC";
                    $query = $conn->query($sql);
                    
                    while($row = $query->fetch_assoc()){
                      // Tronquer la question et la réponse si trop longues
                      $question = strlen($row['question']) > 80 ? substr($row['question'], 0, 80)."..." : $row['question'];
                      $reponse = strlen($row['reponse']) > 100 ? substr($row['reponse'], 0, 100)."..." : $row['reponse'];
                      
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idFaq']."</td>
                          
                          <td>".htmlspecialchars($question)."</td>
                          <td>".htmlspecialchars($reponse)."</td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idFaq']."'><i class='fa fa-eye'></i> Voir</button>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$row['idFaq']."'><i class='fa fa-edit'></i> Modifier</button>
                            <button class='btn btn-danger btn-sm delete btn-flat' data-id='".$row['idFaq']."'><i class='fa fa-trash'></i> Supprimer</button>
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
  <?php include 'includes/faq_modal.php'; ?>
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(function(){
  // Initialiser le DataTable
  if (!$.fn.DataTable.isDataTable('#example1')) {
    $('#example1').DataTable({
      responsive: true,
      "order": [[ 1, "desc" ]] // Trier par ID (descendant)
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
});

// Fonction pour récupérer les données d'une FAQ
function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'faq_row.php',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      // Remplir les champs du formulaire d'édition
      $('.faq-id').val(response.idFaq);
      $('#edit_question').val(response.question);
      $('#edit_reponse').val(response.reponse);
      
      // Remplir les informations pour la vue détaillée
      $('#view_id').text(response.idFaq);
      $('#view_question').text(response.question);
      $('#view_reponse').text(response.reponse);
      
      // Pour la modal de suppression
      $('.faq-question').html(response.question);
    }
  });
}
</script>
</body>