<?php
  	session_start();
  	if(isset($_SESSION['artisan'])){
    	header('location:home.php');
  	}
?>
<style>
    .sign-up a {
        color: #3c8dbc ;
        text-decoration: underline;
        font-size: 1.1em;
    }

    .sign-up {
        text-align: center;
    }

    .sign-up a:hover {
        color: #367fa9 ;
    }
</style>
<?php include 'includes/header.php'; ?>
<body>
    <div class="login-container">
        <!-- Section image à gauche -->
        <div class="login-image">
            <div class="login-image-overlay">
                <h1>LAMECA</h1>
                <p>Découvrez l'art authentique et soutenez les artisans indépendants. Chaque pièce raconte une histoire unique.</p>
            </div>
        </div>
        
        <!-- Section formulaire à droite -->
        <div class="login-form">
            <div class="login-box">
                <div class="login-logo">
                    <b>Lameca</b>
                </div>
                
                <p class="login-box-msg">Connectez-vous pour démarrer votre session</p>
                
                <form action="login.php" method="POST">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="username" placeholder="Nom d'utilisateur" required>
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" name="password" placeholder="Mot de passe" required>
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block" name="login">
                            <i class="fa fa-sign-in"></i> Se connecter
                        </button>
                    </div>
                    <div class="sign-up">
                        <a href="sign_up.php"> <span>S'inscrire</span></a>
                    </div>
                </form>
                
                <div class="or-divider">
                    <span>OU</span>
                </div>
                
                <div class="social-login">
                    <a href="#" class="social-btn btn-google">
                        <i class="fa fa-google"></i>
                    </a>
                    <a href="#" class="social-btn btn-apple">
                        <i class="fa fa-apple"></i>
                    </a>
                    <a href="#" class="social-btn btn-instagram">
                        <i class="fa fa-instagram"></i>
                    </a>
                </div>
                
                <?php
                    if(isset($_SESSION['error'])){
                        echo "
                            <div class='callout callout-danger text-center'>
                                <p>".$_SESSION['error']."</p> 
                            </div>
                        ";
                        unset($_SESSION['error']);
                    }
                ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/scripts.php' ?>
</body>
</html>