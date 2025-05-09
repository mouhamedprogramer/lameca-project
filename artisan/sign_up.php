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
                <form class="form-horizontal" method="POST" action="sign_up_register.php" enctype="multipart/form-data" id="form_resgister">
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="nom" placeholder="Nom *" required>
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="prenom" placeholder="Prénom *" required>
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="email" placeholder="Mail *" required>
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="telephone" placeholder="Telephone *" required>
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <select class="form-control" id="genre" name="genre">
                            <option value="" disabled selected>Choisir un genre</option>
                            <option value="Homme">Homme</option>
                            <option value="Femme">Femme</option>
                            <option value="Autre">Autre</option>
                        </select>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="text" class="form-control" name="specialite" placeholder="Spécialité *" required>
                        <span class="glyphicon glyphicon-user form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe *" required
                        pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!#%*?&])[A-Za-z\d@$!#%*?&]{8,}"
                        title="Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial parmi $,!,#,%,*,?,& .">
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="form-group has-feedback">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirmez le mot de passe *" required>
                        <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                    </div>
                    <div class="form-group">
                        <label for="photo" class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" id="photo" name="photo">
                        </div>
                    </div>
                    <!-- Cases à cocher pour les CGU et la politique de confidentialité -->
                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="accept_cgu" id="cgu_case" required> J'accepte les 
                            <a href="#" data-toggle="modal" data-target="#cgu">Conditions Générales d'Utilisation *</a>
                        </label>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block" name="inscription" disabled id="submitButton">
                            <i class="fa fa-sign-in"></i> S'inscrire
                        </button>
                    </div>
                    <div class="sign-up">
                        <a href="index.php"> <span>Revenir vers la page connexion</span></a>
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
    <?php include 'includes/cgu_modal.php'; ?>

<script>
    //Vérification des mots de passe entre eux
    document.getElementById('form_resgister').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        if (password !== confirm) {
            e.preventDefault(); // Empêche l'envoi du formulaire
            alert('Les mots de passe ne correspondent pas.');
        }
    });

    //Case à cocher vs modal CGU 
    const cguCheckbox = document.getElementById('cgu_case');
    const submitButton = document.getElementById('submitButton');

    if(localStorage.getItem('cguAccepted') === 'true') {
        cguCheckbox.checked = true;
        submitButton.disabled = false;
        localStorage.removeItem('cguAccepted')}

        cguCheckbox.addEventListener('change', function () {
        submitButton.disabled = !this.checked;
    });



</script>

</body>
</html>