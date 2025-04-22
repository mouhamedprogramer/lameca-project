<!-- Add New -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Ajouter une nouvelle commande</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="commandes_add.php">
                    <div class="form-group">
                        <label for="add_client" class="col-sm-3 control-label">Client</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_client" name="client" required>
                                <option value="" selected disabled>- Sélectionner un client -</option>
                                <?php
                                    $sql = "SELECT c.idClient, u.nom, u.prenom FROM client c 
                                            INNER JOIN utilisateur u ON c.idClient = u.idUtilisateur 
                                            ORDER BY u.nom ASC, u.prenom ASC";
                                    $query = $conn->query($sql);
                                    while($crow = $query->fetch_assoc()){
                                        echo "<option value='".$crow['idClient']."'>".$crow['prenom']." ".$crow['nom']."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_oeuvre" class="col-sm-3 control-label">Œuvre</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_oeuvre" name="oeuvre" required>
                                <option value="" selected disabled>- Sélectionner une œuvre -</option>
                                <?php
                                    $sql = "SELECT idOeuvre, titre, prix FROM oeuvre WHERE disponibilite = 1 ORDER BY titre ASC";
                                    $query = $conn->query($sql);
                                    while($orow = $query->fetch_assoc()){
                                        echo "<option value='".$orow['idOeuvre']."'>".$orow['titre']." (".number_format($orow['prix'], 2, ',', ' ')." €)</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_prix" class="col-sm-3 control-label">Prix unitaire (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="add_prix" name="prix" step="0.01" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_quantite" class="col-sm-3 control-label">Quantité</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="add_quantite" name="quantite" min="1" value="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_total" class="col-sm-3 control-label">Total (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="add_total" name="total" step="0.01" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="add_statut" class="col-sm-3 control-label">Statut</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="add_statut" name="statut" required>
                                <option value="En attente" selected>En attente</option>
                                <option value="Confirmée">Confirmée</option>
                                <option value="Expédiée">Expédiée</option>
                                <option value="Livrée">Livrée</option>
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
                <h4 class="modal-title"><b>Modifier la commande</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="commandes_edit.php">
                    <input type="hidden" class="commande-id" name="id">
                    <div class="form-group">
                        <label for="edit_client" class="col-sm-3 control-label">Client</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_client" name="client" required>
                                <?php
                                    $sql = "SELECT c.idClient, u.nom, u.prenom FROM client c 
                                            INNER JOIN utilisateur u ON c.idClient = u.idUtilisateur 
                                            ORDER BY u.nom ASC, u.prenom ASC";
                                    $query = $conn->query($sql);
                                    while($crow = $query->fetch_assoc()){
                                        echo "<option value='".$crow['idClient']."'>".$crow['prenom']." ".$crow['nom']."</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_oeuvre" class="col-sm-3 control-label">Œuvre</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_oeuvre" name="oeuvre" required>
                                <?php
                                    $sql = "SELECT idOeuvre, titre, prix FROM oeuvre ORDER BY titre ASC";
                                    $query = $conn->query($sql);
                                    while($orow = $query->fetch_assoc()){
                                        echo "<option value='".$orow['idOeuvre']."'>".$orow['titre']." (".number_format($orow['prix'], 2, ',', ' ')." €)</option>";
                                    }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_prix" class="col-sm-3 control-label">Prix unitaire (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="edit_prix" name="prix" step="0.01" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_quantite" class="col-sm-3 control-label">Quantité</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="edit_quantite" name="quantite" min="1" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_total" class="col-sm-3 control-label">Total (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="edit_total" name="total" step="0.01" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="edit_statut" class="col-sm-3 control-label">Statut</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="edit_statut" name="statut" required>
                                <option value="En attente">En attente</option>
                                <option value="Confirmée">Confirmée</option>
                                <option value="Expédiée">Expédiée</option>
                                <option value="Livrée">Livrée</option>
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
                <form class="form-horizontal" method="POST" action="commandes_delete.php">
                    <input type="hidden" class="commande-id" name="id">
                    <div class="text-center">
                        <p>SUPPRIMER LA COMMANDE</p>
                        <h2 class="bold commande-info"></h2>
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
                <h4 class="modal-title"><b>Détails de la commande</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="text-primary"><i class="fa fa-user"></i> Informations client</h4>
                        <dl class="dl-horizontal">
                            <dt>Nom :</dt>
                            <dd id="view_client"></dd>
                            <dt>Email :</dt>
                            <dd id="view_email"></dd>
                            <dt>Téléphone :</dt>
                            <dd id="view_telephone"></dd>
                            <dt>Adresse :</dt>
                            <dd id="view_adresse"></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <h4 class="text-primary"><i class="fa fa-shopping-cart"></i> Détails de la commande</h4>
                        <dl class="dl-horizontal">
                            <dt>Date :</dt>
                            <dd id="view_date"></dd>
                            <dt>Statut :</dt>
                            <dd id="view_statut"></dd>
                        </dl>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="text-primary"><i class="fa fa-image"></i> Produit</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Œuvre</th>
                                        <th>Description</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td id="view_oeuvre"></td>
                                        <td id="view_description"></td>
                                        <td id="view_prix_unitaire"></td>
                                        <td id="view_quantite"></td>
                                        <td id="view_total" class="text-bold"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
            </div>
        </div>
    </div>
</div>