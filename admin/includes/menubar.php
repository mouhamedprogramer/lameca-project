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
      <li class="header">RAPPORTS</li>
      <li class=""><a href="home.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
      <li class="header">GERER</li>
      <li class=""><a href="utilisateurs.php"><span class="glyphicon glyphicon-lock"></span> <span>Utilisateurs</span></a></li>
      <li class=""><a href="commandes.php"><i class="fa fa-users"></i> <span>Commandes</span></a></li>
      <li class=""><a href="artisants.php"><i class="fa fa-tasks"></i> <span>Artisans</span></a></li>
      <li class=""><a href="clients.php"><i class="fa fa-black-tie"></i> <span>Clients</span></a></li>

    </ul>
  </section>
  <!-- /.sidebar -->
</aside>
<?php include 'config_modal.php'; ?>
<?php include 'config_modal_desc.php'; ?>