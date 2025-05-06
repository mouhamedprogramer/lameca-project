<!-- Add -->
<div class="modal fade" id="addnew">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"><b>Ajouter un Signalement</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="signalements_add.php">
          <div class="form-group">
            <label for="signaleur" class="col-sm-3 control-label">Signaleur *</label>
            <div class="col-sm-9">
              <select class="form-control" id="signaleur" name="signaleur" required>
                <option value="">- Sélectionnez un utilisateur -</option>
                <?php
                  $sql = "SELECT idUtilisateur, nom, prenom FROM utilisateur ORDER BY nom ASC, prenom ASC";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                    echo "
                      <option value='".$row['idUtilisateur']."'>".$row['prenom'].' '.$row['nom']."</option>
                    ";
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="type_cible" class="col-sm-3 control-label">Type Cible *</label>
            <div class="col-sm-9">
              <select class="form-control" id="type_cible" name="type_cible" required>
                <option value="">- Sélectionnez un type -</option>
                <option value="Utilisateur">Utilisateur</option>
                <option value="Oeuvre">Oeuvre</option>
              </select>
            </div>
          </div>
          <div class="form-group utilisateur-select" style="display:none;">
            <label for="utilisateur" class="col-sm-3 control-label">Utilisateur *</label>
            <div class="col-sm-9">
              <select class="form-control" id="utilisateur" name="utilisateur">
                <option value="">- Sélectionnez un utilisateur -</option>
                <?php
                  $sql = "SELECT idUtilisateur, nom, prenom FROM utilisateur ORDER BY nom ASC, prenom ASC";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                    echo "
                      <option value='".$row['idUtilisateur']."'>".$row['prenom'].' '.$row['nom']."</option>
                    ";
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group oeuvre-select" style="display:none;">
            <label for="oeuvre" class="col-sm-3 control-label">Oeuvre *</label>
            <div class="col-sm-9">
              <select class="form-control" id="oeuvre" name="oeuvre">
                <option value="">- Sélectionnez une oeuvre -</option>
                <?php
                  $sql = "SELECT idOeuvre, titre FROM oeuvre ORDER BY titre ASC";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                    echo "
                      <option value='".$row['idOeuvre']."'>".$row['titre']."</option>
                    ";
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="motif" class="col-sm-3 control-label">Motif *</label>
            <div class="col-sm-9">
              <textarea class="form-control" id="motif" name="motif" rows="4" required></textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="statut" class="col-sm-3 control-label">Statut</label>
            <div class="col-sm-9">
              <select class="form-control" id="statut" name="statut">
                <option value="En attente">En attente</option>
                <option value="Résolu">Résolu</option>
                <option value="Rejeté">Rejeté</option>
              </select>
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
        <h4 class="modal-title"><b>Modifier le Signalement</b></h4>
      </div>
      <div class="modal-body">
        <form class="form-horizontal" method="POST" action="signalements_edit.php">
          <input type="hidden" class="signalement-id" name="id">
          <div class="form-group">
            <label for="edit_type_cible" class="col-sm-3 control-label">Type Cible *</label>
            <div class="col-sm-9">
              <select class="form-control" id="edit_type_cible" name="type_cible" required>
                <option value="Utilisateur">Utilisateur</option>
                <option value="Oeuvre">Oeuvre</option>
              </select>
            </div>
          </div>
          <div class="form-group utilisateur-select" style="display:none;">
            <label for="edit_utilisateur" class="col-sm-3 control-label">Utilisateur *</label>
            <div class="col-sm-9">
              <select class="form-control" id="edit_utilisateur" name="utilisateur">
                <option value="">- Sélectionnez un utilisateur -</option>
                <?php
                  $sql = "SELECT idUtilisateur, nom, prenom FROM utilisateur ORDER BY nom ASC, prenom ASC";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                    echo "
                      <option value='".$row['idUtilisateur']."'>".$row['prenom'].' '.$row['nom']."</option>
                    ";
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group oeuvre-select" style="display:none;">
            <label for="edit_oeuvre" class="col-sm-3 control-label">Oeuvre *</label>
            <div class="col-sm-9">
              <select class="form-control" id="edit_oeuvre" name="oeuvre">
                <option value="">- Sélectionnez une oeuvre -</option>
                <?php
                  $sql = "SELECT idOeuvre, titre FROM oeuvre ORDER BY titre ASC";
                  $query = $conn->query($sql);
                  while($row = $query->fetch_assoc()){
                    echo "
                      <option value='".$row['idOeuvre']."'>".$row['titre']."</option>
                    ";
                  }
                ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_motif" class="col-sm-3 control-label">Motif *</label>
            <div class="col-sm-9">
              <textarea class="form-control" id="edit_motif" name="motif" rows="4" required></textarea>
            </div>
          </div>
          <div class="form-group">
            <label for="edit_statut" class="col-sm-3 control-label">Statut</label>
            <div class="col-sm-9">
              <select class="form-control" id="edit_statut" name="statut">
                <option value="En attente">En attente</option>
                <option value="Résolu">Résolu</option>
                <option value="Rejeté">Rejeté</option>
              </select>
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
        <form class="form-horizontal" method="POST" action="signalements_delete.php">
          <input type="hidden" class="signalement-id" name="id">
          <div class="text-center">
            <p>SUPPRIMER LE SIGNALEMENT</p>
            <h2 class="bold signalement-date"></h2>
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
        <h4 class="modal-title"><b>Détails du Signalement</b></h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-xs-12">
            <h4 class="text-center">Signalement du <span id="view_date" class="bold"></span></h4>
          </div>
        </div>
        <hr>
        <div class="row">
          <div class="col-xs-4"><b>Signaleur:</b></div>
          <div class="col-xs-8" id="view_signaleur"></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><b>Type de cible:</b></div>
          <div class="col-xs-8" id="view_type_cible"></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><b>Cible:</b></div>
          <div class="col-xs-8" id="view_cible"></div>
        </div>
        <div class="row">
          <div class="col-xs-4"><b>Statut:</b></div>
          <div class="col-xs-8" id="view_statut"></div>
        </div>
        <hr>
        <div class="row">
          <div class="col-xs-12">
            <b>Motif du signalement:</b>
            <p id="view_motif" class="well well-sm"></p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
      </div>
    </div>
  </div>
</div>