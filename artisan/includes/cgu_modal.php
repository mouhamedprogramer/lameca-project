<!-- CGU -->
<div class="modal fade" id="cgu">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Conditions Générales d'Utilisation </b></h4>
            </div>
            <div class="modal-body">
            <body>
                <h1>Conditions Générales d'Utilisation</h1>
                <p>Dernière mise à jour : 9 mai 2025</p>

                <h2>1. Objet</h2>
                <p>Les présentes Conditions Générales d'Utilisation (ci-après « CGU ») ont pour objet de définir les modalités et conditions d'utilisation du site internet [Nom du Site] accessible à l'adresse [URL].</p>

                <h2>2. Acceptation des CGU</h2>
                <p>L'accès et l'utilisation du site impliquent l'acceptation sans réserve des présentes CGU par l'utilisateur.</p>

                <h2>3. Accès au site</h2>
                <p>Le site est accessible gratuitement à tout utilisateur disposant d’un accès à Internet. Tous les frais liés à l’accès au site sont à la charge de l’utilisateur.</p>

                <h2>4. Propriété intellectuelle</h2>
                <p>Le contenu du site (textes, images, graphismes, logo, icônes, etc.) est la propriété de [Nom de l'entreprise ou du propriétaire] ou de ses partenaires, et est protégé par les lois en vigueur sur la propriété intellectuelle.</p>

                <h2>5. Responsabilités</h2>
                <p>Le site décline toute responsabilité en cas de bug, inexactitude ou omission portant sur des informations disponibles sur le site.</p>

                <h2>6. Données personnelles</h2>
                <p>Les informations collectées sont traitées conformément à notre <a href="politique-de-confidentialite.html">Politique de confidentialité</a>.</p>

                <h2>7. Modifications des CGU</h2>
                <p>Les CGU peuvent être modifiées à tout moment. Les utilisateurs sont invités à les consulter régulièrement.</p>

                <h2>8. Droit applicable</h2>
                <p>Les présentes CGU sont régies par le droit français. En cas de litige, les tribunaux français seront seuls compétents.</p>

                <button type="button" class="btn btn-primary btn-block" name="accept" id="acceptButton"> Accepter </button>

            </body>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('acceptButton').addEventListener('click', function () {
        localStorage.setItem('cguAccepted', 'true')
        window.location.href = 'sign_up.php'
    });
</script>