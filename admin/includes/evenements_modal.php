<!-- Add -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Ajouter un Événement</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="evenements_add.php">
          <div class="form-group">
            <label for="nom" class="col-sm-3 control-label">Nom *</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="nom" name="nom" required>
            </div>
          </div>
          <div class="form-group">
            <label for="description" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <textarea class="form-control" id="description" name="description" rows="4"></textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="debut" class="col-sm-3 control-label">Date de début *</label>
            <div class="col-sm-9">
              <input type="date" class="form-control datepicker" id="debut" name="debut" required>
            </div>
          </div>
          <div class="form-group">
            <label for="fin" class="col-sm-3 control-label">Date de fin</label>
            <div class="col-sm-9">
              <input type="date" class="form-control datepicker" id="fin" name="fin">
            </div>
          </div>
          <div class="form-group">
            <label for="lieu" class="col-sm-3 control-label">Lieu</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="lieu" name="lieu">
            </div>
          </div>
          <div class="form-group">
            <label for="artisan" class="col-sm-3 control-label">Artisan</label>
            <div class="col-sm-9">
              <select class="form-control" id="artisan" name="artisan">
                <option value="">- Sélectionnez un artisan -</option>
                <?php
                  $sql = "SELECT a.idArtisan, u.nom, u.prenom 
                          FROM artisan a 
                          LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                          ORDER BY u.nom ASC, u.prenom ASC";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                    echo "
                      <option value='".$row['idArtisan']."'>".$row['prenom'].' '.$row['nom']."</option>
                    ";
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="mis_en_avant" class="col-sm-3 control-label">Mis en avant</label>
            <div class="col-sm-9">
              <div class="checkbox">
                <label>
                  <input type="checkbox" id="mis_en_avant" name="mis_en_avant"> Oui
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
        <h4 class="modal-title"><b>Modifier l'Événement</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="evenements_edit.php">
          <input type="hidden" class="evenement-id" name="id">
          <div class="form-group">
            <label for="edit_nom" class="col-sm-3 control-label">Nom *</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_nom" name="nom" required>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_description" class="col-sm-3 control-label">Description</label>
            <div class="col-sm-9">
              <textarea class="form-control" id="edit_description" name="description" rows="4"></textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_debut" class="col-sm-3 control-label">Date de début *</label>
            <div class="col-sm-9">
              <input type="date" class="form-control datepicker" id="edit_debut" name="debut" required>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_fin" class="col-sm-3 control-label">Date de fin</label>
            <div class="col-sm-9">
              <input type="date" class="form-control datepicker" id="edit_fin" name="fin">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_lieu" class="col-sm-3 control-label">Lieu</label>
            <div class="col-sm-9">
              <input type="text" class="form-control" id="edit_lieu" name="lieu">
            </div>
          </div>
          <div class="form-group">
            <label for="edit_artisan" class="col-sm-3 control-label">Artisan</label>
            <div class="col-sm-9">
              <select class="form-control" id="edit_artisan" name="artisan">
                <option value="">- Sélectionnez un artisan -</option>
                <?php
                  $sql = "SELECT a.idArtisan, u.nom, u.prenom 
                          FROM artisan a 
                          LEFT JOIN utilisateur u ON a.idArtisan = u.idUtilisateur 
                          ORDER BY u.nom ASC, u.prenom ASC";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                    echo "
                      <option value='".$row['idArtisan']."'>".$row['prenom'].' '.$row['nom']."</option>
                    ";
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_mis_en_avant" class="col-sm-3 control-label">Mis en avant</label>
            <div class="col-sm-9">
              <div class="checkbox">
                <label>
                  <input type="checkbox" id="edit_mis_en_avant" name="mis_en_avant"> Oui
                </label>
              </div>
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
        <form class="form-horizontal" method="POST" action="evenements_delete.php">
          <input type="hidden" class="evenement-id" name="id">
          <div class="text-center">
            <p>SUPPRIMER L'ÉVÉNEMENT</p>
            <h2 class="bold evenement-nom"></h2>
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
        <h4 class="modal-title"><b>Détails de l'Événement</b></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12">
            <h3 id="view_nom" class="text-center bold"></h3>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-xs-4"><b>Date de début:</b></div>
          <div class="col-xs-8" id="view_debut"></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><b>Date de fin:</b></div>
          <div class="col-xs-8" id="view_fin"></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><b>Lieu:</b></div>
          <div class="col-xs-8" id="view_lieu"></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><b>Artisan:</b></div>
          <div class="col-xs-8" id="view_artisan"></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><b>Mis en avant:</b></div>
          <div class="col-xs-8" id="view_mis_en_avant"></div>
        </div>
        <hr>
        <div class="row">
          <div class="col-xs-12">
            <b>Description:</b>
            <p id="view_description" class="well well-sm"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
      </div>
    </div>
  </div>
</div>