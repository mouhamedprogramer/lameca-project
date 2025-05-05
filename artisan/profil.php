<?php include 'includes/session.php';?>
<?php include 'includes/header.php';?>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <?php include 'includes/navbar.php'; ?>
    <?php include 'includes/menubar.php'; ?>
    <section class="content-header">
        <h1>
            Votre Profil Artisan
        </h1>
        <ol class="breadcrumb">
            <li><a href="profil.php"><i class="fa fa-dashboard"></i>Accueil</a></li>
            <li class="active">Mon espace Artisan</li>
        </ol>
    </section>
    <!--Main content-->
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
                      
                      echo "
                        <tr>
                          <td class='hidden'>".$row['idCommande']."</td>
                          <td>".date('d/m/Y à H:i', strtotime($row['dateCommande']))."</td>
                          <td>".$row['prenom_client'].' '.$row['nom_client']."</td>
                          <td>".$row['titre_oeuvre']."</td>
                          <td>".number_format($row['prix_oeuvre'], 2, ',', ' ')." €</td>
                          <td>".$row['nombreArticles']."</td>
                          <td>".number_format($prix_total, 2, ',', ' ')." €</td>
                          <td><span class='label ".$status_class."'>".$row['statut']."</span></td>
                          <td>
                            <button class='btn btn-info btn-sm view btn-flat' data-id='".$row['idCommande']."'><i class='fa fa-eye'></i> Voir</button>
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
    <section class="content">
        <?php
            if(isset($_SESSION['error'])){
                echo "
                <div class='alert alert-danger alert-dismissible'>
                    <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                    <h4><i class='icon fa fa-warning'></i> Erreur!</h4>
                    ".$_SESSION['error']."
                </div>";
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

        <!--Boîtes de profil principale-->
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">Votre profil Artisan</h3>
                    </div>
                    <div class="box-body col-xs-12">
                        <div class="table-responsive">
                            <?php
                                // Récupérer les informations de l'Artisan
                                $sql = "SELECT idArtisan, specialite, nom, prenom, email, telephone, adresse, pays, ville, code_postal, date_naissance, photo
                                        FROM artisan
                                        JOIN utilisateur ON idArtisan=idUtilisateur
                                        WHERE idArtisan={$_SESSION['artisan']}";
                                $stmt = $conn->prepare($sql);
                                $stmt->execute();
                                $query = $stmt->get_result();
                                $row = $query->fetch_assoc();

                                echo "
                                    <tr>
                                    </tr>"
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                  
    </section>

    <?php include 'includes/footer.php'; ?>
  
</div>
<?php include 'includes/scripts.php'; ?>
</body>
</html>