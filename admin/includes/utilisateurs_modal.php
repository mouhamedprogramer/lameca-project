<!-- Add New -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Ajouter un nouvel utilisateur</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="utilisateurs_add.php" enctype="multipart/form-data">
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
                        <label for="date_naissance" class="col-sm-3 control-label">Date de naissance</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="date_naissance" name="date_naissance">
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
                        <label for="role" class="col-sm-3 control-label">Rôle</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="role" name="role" required>
                                <option value="Admin">Admin</option>
                                <option value="Artisan">Artisan</option>
                                <option value="Client">Client</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="photo" class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" id="photo" name="photo">
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
                <button type="submit" class="btn btn-primary btn-flat" name="add"><i class="fa fa-save"></i> Enregistrer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit -->
<div class="modal fade" id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Modifier l'utilisateur</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="utilisateurs_edit.php" enctype="multipart/form-data">
                    <input type="hidden" class="userid" name="id">
                    <div class="form-group">
                        <label for="edit_nom" class="col-sm-3 control-label">Nom</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_nom" name="nom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_prenom" class="col-sm-3 control-label">Prénom</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_prenom" name="prenom" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_email" class="col-sm-3 control-label">Email</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="edit_email" name="email" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_password" class="col-sm-3 control-label">Mot de passe</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="edit_password" name="password">
                            <small><i>Laissez vide si vous ne voulez pas changer le mot de passe</i></small>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_telephone" class="col-sm-3 control-label">Téléphone</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_telephone" name="telephone">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_adresse" class="col-sm-3 control-label">Adresse</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="edit_adresse" name="adresse"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_pays" class="col-sm-3 control-label">Pays</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_pays" name="pays">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_ville" class="col-sm-3 control-label">Ville</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_ville" name="ville">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_code_postal" class="col-sm-3 control-label">Code Postal</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_code_postal" name="code_postal">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_date_naissance" class="col-sm-3 control-label">Date de naissance</label>
                        <div class="col-sm-9">
                            <input type="date" class="form-control" id="edit_date_naissance" name="date_naissance">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_genre" class="col-sm-3 control-label">Genre</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_genre" name="genre">
                                <option value="Homme">Homme</option>
                                <option value="Femme">Femme</option>
                                <option value="Autre">Autre</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_role" class="col-sm-3 control-label">Rôle</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_role" name="role" required>
                                <option value="Admin">Admin</option>
                                <option value="Artisan">Artisan</option>
                                <option value="Client">Client</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_photo" class="col-sm-3 control-label">Photo</label>
                        <div class="col-sm-9">
                            <input type="file" id="edit_photo" name="photo">
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
                <button type="submit" class="btn btn-success btn-flat" name="edit"><i class="fa fa-check-square-o"></i> Mettre à jour</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Suppression...</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="utilisateurs_delete.php">
                    <input type="hidden" class="userid" name="id">
                    <div class="text-center">
                        <p>SUPPRIMER L'UTILISATEUR</p>
                        <h2 class="bold fullname"></h2>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
                <button type="submit" class="btn btn-danger btn-flat" name="delete"><i class="fa fa-trash"></i> Supprimer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- View -->
<div class="modal fade" id="view">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Détails de l'utilisateur</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-4">
                        <img id="view_photo" src="" alt="Photo de profil" class="img-responsive" style="max-width: 100%;">
                    </div>
                    <div class="col-sm-8">
                        <h3 id="view_fullname" class="bold"></h3>
                        <p><b>Email:</b> <span id="view_email"></span></p>
                        <p><b>Téléphone:</b> <span id="view_telephone"></span></p>
                        <p><b>Rôle:</b> <span id="view_role"></span></p>
                        <p><b>Genre:</b> <span id="view_genre"></span></p>
                        <p><b>Date de naissance:</b> <span id="view_date_naissance"></span></p>
                        <p><b>Adresse:</b> <span id="view_adresse"></span></p>
                        <p><b>Ville:</b> <span id="view_ville"></span></p>
                        <p><b>Code Postal:</b> <span id="view_code_postal"></span></p>
                        <p><b>Pays:</b> <span id="view_pays"></span></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
            </div>
        </div>
    </div>
</div>