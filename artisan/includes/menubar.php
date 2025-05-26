<aside class="main-sidebar" >
  <!-- sidebar: style can be found in sidebar.less -->
  <section class="sidebar" >
    <!-- Sidebar user panel -->
    <div class="user-panel" >
      <div class="pull-left image">
        <img src="<?php echo (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/profile.jpg'; ?>" class="img-circle" alt="User Image">
      </div>
      <div class="pull-left info">
        <p><?php echo $user['prenom'].' '.$user['nom']; ?></p>
        <a><i class="fa fa-circle text-success"></i> En ligne</a>
      </div>
    </div>
    <!-- sidebar menu: : style can be found in sidebar.less -->
    <ul class="sidebar-menu" data-widget="tree">
      <li class="header">GERER</li>
      <li class=""><a href="profil.php"><span class="glyphicon glyphicon-lock"></span> <span>Mon espace Artisan</span></a></li>
      <li class=""><a href="commandes.php"><i class="glyphicon glyphicon-credit-card"></i> <span>Commandes</span></a></li>
      <li class=""><a href="produits.php"><i class="fa fa-shopping-bag" aria-hidden="true"></i> <span>Mes oeuvres</span></a></li>
      <li class=""><a href="clients.php"><i class="fa fa-users"></i> <span>Clients</span></a></li>
      <li class=""><a href="statistiques.php"><i class="fa fa-tasks"></i> <span>Statistiques</span></a></li>
      <li class=""><a href="evenements.php"><i class="fa fa-calendar"></i> <span>Événements</span></a></li>
      <li class=""><a href="messages.php"><i class="fa fa-calendar"></i> <span>Messagerie</span></a></li>

    </ul>
  </section>
  <!-- /.sidebar -->
</aside>
<?php include 'config_modal.php'; ?>
<?php include 'config_modal_desc.php'; ?>