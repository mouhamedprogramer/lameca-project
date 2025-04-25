<?php include 'includes/header.php'; ?>

<style>
       .sign-up a {
           color: #3c8dbc;
           text-decoration: underline;
           font-size: 1.1em;
       }

       .sign-up {
        text-align: center;
       }

       .sign-up a:hover {
           color: #367fa9;
       }
</style>

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
                
                <p class="login-box-msg">Inscrivez-vous pour pouvoir créer votre espace Artisan !</p>
                
                <div class="modal-body">
                <form class="form-horizontal" method="POST" action="sign_up_register.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="nom" class="col-sm-3 control-label">Nom</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="nom" name="nom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="prenom" class="col-sm-3 control-label">Prénom</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="prenom" name="prenom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="col-sm-3 control-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password" class="col-sm-3 control-label">Mot de passe</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="telephone" class="col-sm-3 control-label">Téléphone</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="telephone" name="telephone">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="adresse" class="col-sm-3 control-label">Adresse</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="adresse" name="adresse"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="pays" class="col-sm-3 control-label">Pays</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="pays" name="pays">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="ville" class="col-sm-3 control-label">Ville</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="ville" name="ville">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="code_postal" class="col-sm-3 control-label">Code Postal</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="code_postal" name="code_postal">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="genre" class="col-sm-3 control-label">Genre</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="genre" name="genre">
                                <option value="Homme">Homme</option>
                                <option value="Femme">Femme</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="specialite" class="col-sm-3 control-label">Spécialité</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="specialite" name="specialite">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="certification" class="col-sm-3 control-label">Certification</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="certification" name="certification"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="portfolio" class="col-sm-3 control-label">Portfolio</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="portfolio" name="portfolio"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="photo" class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" id="photo" name="photo">
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block" name="inscription">
                            <i class="fa fa-sign-in"></i> S'inscrire
                        </button>
                    </div>
                    <div class="sign-up">
                        <a href="index.php"> <span>Revenir vers la page connexion</span></a>
                    </div>
            </div>
            
                
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