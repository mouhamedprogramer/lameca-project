<header class="main-header">
  <!-- Logo -->
  <a href="profil.php" class="logo" style="background: rgb(0,120,150) !important;">
    <!-- mini logo for sidebar mini 50x50 pixels -->
    <span class="logo-mini"><b>V</b>TS</span>
    <!-- logo for regular state and mobile devices -->
    <span class="logo-lg"><b>Lameca</b></span>
  </a>
  <!-- Header Navbar: style can be found in header.less -->
  <nav class="navbar navbar-static-top" style="background: rgb(0,120,150) !important;">
    <!-- Sidebar toggle button-->
    <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
      <span class="sr-only">Toggle navigation</span>
    </a>

    <div class="navbar-custom-menu">
      <ul class="nav navbar-nav">
        <!-- User Account: style can be found in dropdown.less -->
        <li class="dropdown user user-menu">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <img src="<?php echo (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/profile.jpg'; ?>" class="user-image" alt="User Image">
            <span class="hidden-xs"><?php echo $user['prenom'].' '.$user['nom']; ?></span>
          </a>
          <ul class="dropdown-menu">
            <!-- User image -->
            <li class="user-header">
              <img src="<?php echo (!empty($user['photo'])) ? '../images/'.$user['photo'] : '../images/profile.jpg'; ?>" class="img-circle" alt="User Image">

              <p>
                <?php echo $user['prenom'].' '.$user['nom']; ?>
                <small>Date :  <?php echo date('M. Y', time()); ?></small>
              </p>
            </li>
            <li class="user-footer">
              <div class="pull-left">
                <a href="#profile" data-toggle="modal" class="btn btn-default btn-flat" id="admin_profile">Mise à jour</a>
              </div>
              <div class="pull-right">
                <a href="logout.php" class="btn btn-default btn-flat">Se déconnecter</a>
              </div>
            </li>
          </ul>
        </li>
        
        <!-- Élément "À propos"
        <li><a href="a-propos.php">À propos</a></li>
        
         Élément "Formations" 
        <li><a href="formations.php">Formations</a></li>
        
        Élément "Contacts"
        <li><a href="contacts.php">Contacts</a></li>  -->
        
      </ul>
    </div>
  </nav>
</header>
<?php include 'includes/profile_modal.php'; ?>