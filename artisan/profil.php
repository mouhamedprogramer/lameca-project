<?php include 'includes/session.php';?>
<?php include 'includes/header.php';?>
<style>

.circular-img {
  width: 200px;
  height: 200px;
  object-fit: cover;
  border-radius: 50%;
}

.premier-element {
  border-collapse: separate !important;
  border-spacing: 90px 90px;
}

.premier-element td, 
.premier-element th {
  padding: 15px 40px;
}

.nom {
  color: #3c8dbc;
  font-weight: bold;
  font-size:50px;
}

.specialite {
  color: gray;
  font-style: italic;
  font-size:25px;
}

.infos {
  color: gray;
  font-size:15px;
}

.align-gauche {
  text-align: left !important;
}

</style>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

  <?php include 'includes/navbar.php'; ?>
  <?php include 'includes/menubar.php'; ?>
  <?php include 'includes/portfolio_modal.php'; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        Votre profil artisan
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
            <div style="display: flex; justify-content: left;">
              <table class="table" style="width: auto;">
              <tbody>
                  <?php
                    // Requête pour récupérer les œuvres avec les informations de l'artisan
                    $sql = "SELECT u.telephone AS tel, u.email AS mail, u.date_naissance AS date_naissance, o.idArtisan,
                            u.nom AS nom_artisan, u.prenom AS prenom_artisan, a.specialite AS specialite
                            FROM oeuvre o 
                            LEFT JOIN artisan a ON o.idArtisan = a.idArtisan 
                            LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
                            WHERE o.idArtisan = {$_SESSION['artisan']}
                            ORDER BY o.datePublication DESC";
                    $query = $conn->query($sql);
                    
                    $row = $query->fetch_assoc();
                      
                    // Requête pour récupérer la photo de l'artisan
                    $sql = "SELECT u.photo AS photo_profil FROM utilisateur u 
                            JOIN artisan a ON u.idUtilisateur = a.idArtisan 
                            WHERE a.idArtisan = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $row['idArtisan']);  // Utilisez idArtisan ici
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $img_url = '';
                    if ($result->num_rows > 0) {
                      $photo = $result->fetch_assoc();
                    // Construire l'URL de l'image en supposant que la photo est dans le répertoire images/
                      $img_url = '../images/' . $photo['photo_profil'];  // Exemple : ../images/ma_photo.jpg
                    } else {
                      $img_url = '../images/oeuvre_default.jpg';  // Image par défaut
                    }
                      
                    echo "
                      <tr>
                        <td><img src='".$img_url."' width='200px' height='200px' class='circular-img'></td>
                        <td>
                          <span class='nom'>".$row['nom_artisan']." ".$row['prenom_artisan']."</span><br>
                          <span class='specialite'>".$row['specialite']."</span><br><br>
                          <span class='infos'><i class='fa fa-phone'></i> Numéro de téléphone : ".$row['tel']."</span><br>
                          <span class='infos'><i class='fa fa-envelope'></i> Mail : ".$row['mail']."</span><br>
                        </td>
                        
                      </tr>
                    ";
                  ?>
                </tbody>
              </table>
                                    
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-xs-12">
          <div class="box box-info box-header with-border">
            <h4 class="box-title"> Description de votre profil Artisan</h4>
            <div style="justify-content: left;">
              <table class="table" style="width: auto;">
                <tbody>
                  <?php
                    //Requête pour récupérer le portefolio de l'Artisan
                    $sql = "SELECT portfolio, idArtisan FROM artisan WHERE idArtisan = {$_SESSION['artisan']}";
                    $result = $conn->query($sql);
                    if ($result && $portfolio = $result->fetch_assoc()) {
                      echo "
                        <tr>
                          <td>
                            " . $portfolio['portfolio'] . "
                          </td>
                          <td class='align-gauche'>
                            <button class='btn btn-success btn-sm edit btn-flat' data-id='".$portfolio['idArtisan']."' data-portfolio='".htmlspecialchars($portfolio['portfolio'], ENT_QUOTES)."'data-toggle='modal' data-target='#modifierModal'>Modifier</button>
                          </td>
                        </tr>
                      ";
                    } else {
                      echo "
                        <tr>
                          <td>
                            Aucun portefolio trouvé.
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
</div>
<?php include 'includes/scripts.php'; ?>
<script>
$(document).ready(function(){
  $('#modifierModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Bouton cliqué
    var portfolio = button.data('portfolio');
    var id = button.data('id');

    // Injecter les données dans la modale
    $('#portfolioText').val(portfolio);
    $('#idArtisan').val(id);
  });
});
</script>
</body>
</html>