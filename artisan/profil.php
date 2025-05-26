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

.full-box-btn {
  display: flex;                /* Flex pour centrer contenu */
  justify-content: center;      /* Centrage horizontal */
  align-items: center;          /* Centrage vertical */
  height: 100px;                /* Hauteur du rectangle */
  width: 100%;                  /* Prend toute la largeur du parent */
  background: linear-gradient(135deg, #28a745, #1e7e34);
  color: white;
  font-weight: 700;
  font-size: 1.25em;
  border-radius: 12px;
  text-decoration: none;        /* Enlève le soulignement */
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
  transition: 0.3s ease, box-shadow 0.3s ease;
}

.btn-blue {
  background: linear-gradient(135deg, #00C0EF,rgb(3, 137, 171));
}

.btn-blue:hover {
  box-shadow: 0 20px 20px rgba(0,0,0,0.3);
  background: linear-gradient(135deg,rgb(21, 147, 179),rgb(3, 107, 133));
  cursor: pointer;
  color: white;
}

.btn-green {
  background: linear-gradient(135deg, #00A65A,rgb(1, 108, 60));
}

.btn-green:hover {
  box-shadow: 0 20px 20px rgba(0,0,0,0.3);
  background: linear-gradient(135deg,rgb(12, 141, 83),rgb(1, 97, 54));
  cursor: pointer;
  color: white;
}

.btn-orange {
  background: linear-gradient(135deg, #F39C12,rgb(169, 115, 29));
}

.btn-orange:hover {
  box-shadow: 0 20px 20px rgba(0,0,0,0.3);
  background: linear-gradient(135deg,rgb(196, 126, 13),rgb(174, 112, 11));
  cursor: pointer;
  color: white;
}

.btn-red {
  background: linear-gradient(135deg, #DD4B39,rgb(172, 42, 25));
}

.btn-red:hover {
  box-shadow: 0 20px 20px rgba(0,0,0,0.3);
  background: linear-gradient(135deg,rgb(191, 55, 37),rgb(165, 30, 13));
  cursor: pointer;
  color: white;
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
                    $sql = "SELECT u.telephone AS tel, u.email AS mail, u.date_naissance AS date_naissance, a.idArtisan,
                            u.nom AS nom_artisan, u.prenom AS prenom_artisan, a.specialite AS specialite
                            FROM artisan a
                            LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur
                            WHERE a.idArtisan = {$_SESSION['artisan']}";
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

     <!--Section du portfolio--> 
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

      <!--Section de navigation rapide-->

      <div class="col-lg-3 col-xs-6">
        <a href="produits.php" class="full-box-btn btn-blue"><i class="fa fa-paint-brush"></i>&nbsp;Mes Oeuvres</a>
      </div>
      <div class="col-lg-3 col-xs-6">
        <a href="commandes.php" class="full-box-btn btn-green"><i class="fa fa-shopping-cart"></i>&nbsp;Mes commandes</a>
      </div>
      <div class="col-lg-3 col-xs-6">
        <a href="evenement.php" class="full-box-btn btn-orange"><i class="fa fa-calendar-plus-o"></i>&nbsp;Créer un événement</a>
      </div>
      <div class="col-lg-3 col-xs-6">
        <a href="statistiques.php" class="full-box-btn btn-red"><i class="fa fa-line-chart"></i>&nbsp;Mes statistiques</a>
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