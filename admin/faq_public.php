<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

  <!-- Main Header -->
  <header class="main-header">
    <nav class="navbar navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <a href="index.php" class="navbar-brand"><b>FAQ</b></a>
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse">
            <i class="fa fa-bars"></i>
          </button>
        </div>

        <!-- Collect the nav links, forms, and other content for toggling -->
        <div class="collapse navbar-collapse pull-left" id="navbar-collapse">
          <ul class="nav navbar-nav">
            <li><a href="index.php">Accueil</a></li>
            <li class="active"><a href="faq_public.php">FAQ</a></li>
          </ul>
        </div>
        <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
    </nav>
  </header>
  
  <!-- Full Width Column -->
  <div class="content-wrapper">
    <div class="container">
      <!-- Content Header (Page header) -->
      <section class="content-header">
        <h1>
          Foire Aux Questions (FAQ)
          <small>Trouvez les réponses à vos questions</small>
        </h1>
        <ol class="breadcrumb">
          <li><a href="index.php"><i class="fa fa-home"></i> Accueil</a></li>
          <li class="active">FAQ</li>
        </ol>
      </section>

      <!-- Main content -->
      <section class="content">
        <div class="row">
          <div class="col-md-12">
            
            <!-- Search Box -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Rechercher dans la FAQ</h3>
              </div>
              <div class="box-body">
                <div class="input-group">
                  <input type="text" id="search-faq" class="form-control" placeholder="Tapez votre recherche...">
                  <span class="input-group-addon"><i class="fa fa-search"></i></span>
                </div>
              </div>
            </div>

            <!-- FAQ List -->
            <div class="box box-primary">
              <div class="box-header with-border">
                <h3 class="box-title">Questions Fréquemment Posées</h3>
              </div>
              <div class="box-body">
                <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
                  <?php
                    include 'includes/conn.php';
                    $sql = "SELECT * FROM faq ORDER BY idFaq ASC";
                    $query = $conn->query($sql);
                    
                    $counter = 1;
                    while($row = $query->fetch_assoc()){
                      $panelId = "panel".$counter;
                      $collapseId = "collapse".$counter;
                      $isFirst = ($counter == 1) ? "in" : "";
                      
                      echo '
                      <div class="panel panel-default faq-item">
                        <div class="panel-heading" role="tab" id="'.$panelId.'">
                          <h4 class="panel-title">
                            <a role="button" data-toggle="collapse" data-parent="#accordion" href="#'.$collapseId.'" aria-expanded="true" aria-controls="'.$collapseId.'">
                              <i class="fa fa-question-circle text-primary"></i> '.htmlspecialchars($row['question']).'
                            </a>
                          </h4>
                        </div>
                        <div id="'.$collapseId.'" class="panel-collapse collapse '.$isFirst.'" role="tabpanel" aria-labelledby="'.$panelId.'">
                          <div class="panel-body">
                            <i class="fa fa-comment-o text-success"></i> '.nl2br(htmlspecialchars($row['reponse'])).'
                          </div>
                        </div>
                      </div>';
                      
                      $counter++;
                    }
                    
                    if($counter == 1){
                      echo '<div class="alert alert-info">
                              <i class="fa fa-info-circle"></i> Aucune FAQ disponible pour le moment.
                            </div>';
                    }
                  ?>
                </div>
              </div>
            </div>

            <!-- Contact Box -->
            <div class="box box-success">
              <div class="box-header with-border">
                <h3 class="box-title">Question non trouvée ?</h3>
              </div>
              <div class="box-body">
                <p><i class="fa fa-envelope"></i> Si vous ne trouvez pas la réponse à votre question, n'hésitez pas à nous contacter.</p>
                <a href="contact.php" class="btn btn-success btn-flat">
                  <i class="fa fa-paper-plane"></i> Nous contacter
                </a>
              </div>
            </div>

          </div>
        </div>
      </section>
      <!-- /.content -->
    </div>
    <!-- /.container -->
  </div>
  <!-- /.content-wrapper -->
  
  <!-- Footer -->
  <footer class="main-footer">
    <div class="container">
      <div class="pull-right hidden-xs">
        <b>Version</b> 1.0
      </div>
      <strong>Copyright &copy; <?php echo date('Y'); ?> Votre Entreprise.</strong> Tous droits réservés.
    </div>
  </footer>
</div>
<!-- ./wrapper -->

<?php include 'includes/scripts.php'; ?>
<script>
$(document).ready(function(){
  // Fonction de recherche dans la FAQ
  $('#search-faq').on('keyup', function(){
    var searchText = $(this).val().toLowerCase();
    
    $('.faq-item').each(function(){
      var question = $(this).find('.panel-title a').text().toLowerCase();
      var answer = $(this).find('.panel-body').text().toLowerCase();
      
      if(question.indexOf(searchText) > -1 || answer.indexOf(searchText) > -1){
        $(this).show();
      } else {
        $(this).hide();
      }
    });
    
    // Si la recherche est vide, afficher tous les éléments
    if(searchText === ''){
      $('.faq-item').show();
    }
  });

  // Fermer les autres panneaux quand on en ouvre un
  $('.panel-collapse').on('show.bs.collapse', function () {
    $('.panel-collapse.in').collapse('hide');
  });
});
</script>
</body>
</html>