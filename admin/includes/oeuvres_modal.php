<!-- Add New -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Ajouter une nouvelle œuvre</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="oeuvres_add.php" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titre" class="col-sm-3 control-label">Titre</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="titre" name="titre" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description" class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="prix" class="col-sm-3 control-label">Prix (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="prix" name="prix" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="caracteristiques" class="col-sm-3 control-label">Caractéristiques</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="caracteristiques" name="caracteristiques" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="artisan" class="col-sm-3 control-label">Artisan</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="artisan" name="artisan" required>
                                <option value="" selected disabled>- Sélectionner un artisan -</option>
                                <?php
                                    $sql = "SELECT a.idArtisan, u.nom, u.prenom FROM artisan a 
                                            INNER JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                                            ORDER BY u.nom ASC, u.prenom ASC";
                                    $query = $conn->query($sql);
                                    while($row = $query->fetch_assoc()){
                                        echo "<option value='".$row['idArtisan']."'>".$row['prenom']." ".$row['nom']."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="photos" class="col-sm-3 control-label">Photos (multiple)</label>
                        <div class="col-sm-9">
                            <input type="file" id="photos" name="photos[]" multiple accept="image/*">
                            <small class="text-muted">Vous pouvez sélectionner plusieurs photos</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="disponibilite" name="disponibilite" checked> Disponible
                                </label>
                            </div>
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
                <h4 class="modal-title"><b>Modifier l'œuvre</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="oeuvres_edit.php" enctype="multipart/form-data">
                    <input type="hidden" class="oeuvre-id" name="id">
                    <div class="form-group">
                        <label for="edit_titre" class="col-sm-3 control-label">Titre</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="edit_titre" name="titre" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_description" class="col-sm-3 control-label">Description</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_prix" class="col-sm-3 control-label">Prix (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="edit_prix" name="prix" step="0.01" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_caracteristiques" class="col-sm-3 control-label">Caractéristiques</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="edit_caracteristiques" name="caracteristiques" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_artisan" class="col-sm-3 control-label">Artisan</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_artisan" name="artisan" required>
                                <?php
                                    $sql = "SELECT a.idArtisan, u.nom, u.prenom FROM artisan a 
                                            INNER JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                                            ORDER BY u.nom ASC, u.prenom ASC";
                                    $query = $conn->query($sql);
                                    while($row = $query->fetch_assoc()){
                                        echo "<option value='".$row['idArtisan']."'>".$row['prenom']." ".$row['nom']."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_photos" class="col-sm-3 control-label">Ajouter photos</label>
                        <div class="col-sm-9">
                            <input type="file" id="edit_photos" name="photos[]" multiple accept="image/*">
                            <small class="text-muted">Vous pouvez sélectionner plusieurs photos</small>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <div class="checkbox">
                                <label>
                                    <input type="checkbox" id="edit_disponibilite" name="disponibilite"> Disponible
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="current_photos" class="form-group">
                        <label class="col-sm-3 control-label">Photos actuelles</label>
                        <div class="col-sm-9" id="photo_container">
                            <!-- Les photos existantes seront chargées ici via JavaScript -->
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
                <form class="form-horizontal" method="POST" action="oeuvres_delete.php">
                    <input type="hidden" class="oeuvre-id" name="id">
                    <div class="text-center">
                        <p>SUPPRIMER L'ŒUVRE</p>
                        <h2 class="bold oeuvre-titre"></h2>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Détails de l'œuvre</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h2 id="view_titre" class="text-center"></h2>
                        <p class="text-muted text-center">
                            Par <span id="view_artisan"></span> | Publiée le <span id="view_date"></span> | 
                            Statut: <span id="view_disponibilite"></span>
                        </p>
                        <hr>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <div id="carouselPhotos" class="carousel slide" data-ride="carousel">
                            <!-- Indicators -->
                            <ol class="carousel-indicators" id="carousel-indicators">
                                <!-- Les indicateurs seront ajoutés ici par JavaScript -->
                            </ol>

                            <!-- Wrapper for slides -->
                            <div class="carousel-inner" id="carousel-inner">
                                <!-- Les photos seront ajoutées ici par JavaScript -->
                            </div>

                            <!-- Controls -->
                            <a class="left carousel-control" href="#carouselPhotos" data-slide="prev">
                                <span class="glyphicon glyphicon-chevron-left"></span>
                                <span class="sr-only">Précédent</span>
                            </a>
                            <a class="right carousel-control" href="#carouselPhotos" data-slide="next">
                                <span class="glyphicon glyphicon-chevron-right"></span>
                                <span class="sr-only">Suivant</span>
                            </a>
                        </div>
                        <h3 class="text-center text-primary" id="view_prix"></h3>
                    </div>
                    <div class="col-md-7">
                        <h4>Description</h4>
                        <div id="view_description" class="well well-sm"></div>
                        
                        <h4>Caractéristiques</h4>
                        <div id="view_caracteristiques" class="well well-sm"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Photo -->
<div class="modal fade" id="delete_photo">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Suppression de photo</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="oeuvres_delete_photo.php">
                    <input type="hidden" id="photo_id" name="photo_id">
                    <input type="hidden" id="oeuvre_id" name="oeuvre_id">
                    <div class="text-center">
                        <p>SUPPRIMER CETTE PHOTO?</p>
                        <div class="image-container">
                            <img id="photo_to_delete" src="" alt="Photo à supprimer" style="max-width: 100%; max-height: 200px;">
                        </div>
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