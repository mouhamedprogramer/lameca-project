<!-- Add New -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Ajouter un nouveau client</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="clients_add.php" enctype="multipart/form-data">
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
                                <option value="">Sélectionner</option>
                                <option value="Homme">Homme</option>
                                <option value="Femme">Femme</option>
                                <option value="Autre">Autre</option>
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
                <form class="form-horizontal" method="POST" action="clients_delete.php">
                    <input type="hidden" class="client-id" name="id">
                    <div class="text-center">
                        <p>SUPPRIMER LE CLIENT</p>
                        <h2 class="bold client-nom"></h2>
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
                <h4 class="modal-title"><b>Détails du client</b></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <img id="view_photo" src="../images/profile.jpg" alt="Photo du client" class="img-responsive img-rounded">
                        <h3 id="view_nom" class="text-center"></h3>
                    </div>
                    <div class="col-md-8">
                        <div class="nav-tabs-custom">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#personal" data-toggle="tab">Informations personnelles</a></li>
                                <li><a href="#stats" data-toggle="tab">Statistiques</a></li>
                                <li><a href="#actions" data-toggle="tab">Actions rapides</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="active tab-pane" id="personal">
                                    <dl class="dl-horizontal">
                                        <dt>Email:</dt>
                                        <dd id="view_email"></dd>
                                        <dt>Téléphone:</dt>
                                        <dd id="view_telephone"></dd>
                                        <dt>Adresse:</dt>
                                        <dd id="view_adresse"></dd>
                                        <dt>Date de naissance:</dt>
                                        <dd id="view_date_naissance"></dd>
                                        <dt>Âge:</dt>
                                        <dd id="view_age"></dd>
                                        <dt>Genre:</dt>
                                        <dd id="view_genre"></dd>
                                    </dl>
                                </div>
                                <div class="tab-pane" id="stats">
                                    <dl class="dl-horizontal">
                                        <dt>Client depuis:</dt>
                                        <dd id="view_date_creation"></dd>
                                        <dt>Nombre de commandes:</dt>
                                        <dd id="view_nb_commandes"></dd>
                                        <dt>Total dépensé:</dt>
                                        <dd id="view_total_depense"></dd>
                                        <dt>Dernière commande:</dt>
                                        <dd id="view_derniere_commande"></dd>
                                    </dl>
                                </div>
                                <div class="tab-pane" id="actions">
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-info btn-block col-md-offset-6" id="send_email"><i class="fa fa-envelope"></i> Envoyer un email</a>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-warning btn-block col-md-offset-6" id="export_data"><i class="fa fa-file-excel-o"></i> Exporter les données</a>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-success btn-block col-md-offset-6" id="view_orders"><i class="fa fa-list"></i> Voir les commandes</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

<!-- Client Orders -->
<div class="modal fade" id="client_orders">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Commandes du client</b></h4>
            </div>
            <div class="modal-body">
                <div id="orders_list">
                    <!-- Le contenu sera chargé dynamiquement -->
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p>Chargement des commandes...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
            </div>
        </div>
    </div>
</div>



<!-- Client Orders Modal -->
<div class="modal fade" id="client_orders">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Commandes du client</b></h4>
            </div>
            <div class="modal-body">
                <div id="orders_list">
                    <!-- Le contenu sera chargé dynamiquement -->
                    <div class="text-center">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p>Chargement des commandes...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
            </div>
        </div>
    </div>
</div>

<!-- Create Order Modal -->
<div class="modal fade" id="client_order_create">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Créer une nouvelle commande</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="clients_create_order.php">
                    <input type="hidden" id="new_order_client_id" name="client_id">
                    
                    <div class="form-group">
                        <label for="new_order_oeuvre" class="col-sm-3 control-label">Œuvre</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="new_order_oeuvre" name="oeuvre_id" required>
                                <option value="" selected disabled>- Sélectionner une œuvre -</option>
                                <!-- Options chargées dynamiquement -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_order_prix" class="col-sm-3 control-label">Prix unitaire (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="new_order_prix" name="prix" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_order_quantite" class="col-sm-3 control-label">Quantité</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="new_order_quantite" name="quantite" value="1" min="1" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_order_total" class="col-sm-3 control-label">Total (€)</label>
                        <div class="col-sm-9">
                            <input type="number" class="form-control" id="new_order_total" name="total" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_order_statut" class="col-sm-3 control-label">Statut</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="new_order_statut" name="statut" required>
                                <option value="En attente" selected>En attente</option>
                                <option value="Confirmée">Confirmée</option>
                                <option value="Expédiée">Expédiée</option>
                                <option value="Livrée">Livrée</option>
                            </select>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Annuler</button>
                <button type="submit" class="btn btn-primary btn-flat" name="create_order"><i class="fa fa-save"></i> Créer la commande</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Send Email Modal -->
<div class="modal fade" id="email_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Envoyer un email au client</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="clients_send_email.php">
                    <input type="hidden" id="email_client_id" name="client_id">
                    <input type="hidden" id="email_to_name" name="client_name">
                    
                    <div class="form-group">
                        <label for="email_to" class="col-sm-3 control-label">Destinataire</label>
                        <div class="col-sm-9">
                            <input type="email" class="form-control" id="email_to" name="email_to" readonly>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email_subject" class="col-sm-3 control-label">Sujet</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" id="email_subject" name="email_subject" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email_template" class="col-sm-3 control-label">Modèle</label>
                        <div class="col-sm-9">
                            <select class="form-control" id="email_template" name="email_template">
                                <option value="">Message personnalisé</option>
                                <option value="welcome">Message de bienvenue</option>
                                <option value="promo">Promotion spéciale</option>
                                <option value="reminder">Rappel d'achat</option>
                                <option value="feedback">Demande d'avis</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email_message" class="col-sm-3 control-label">Message</label>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="email_message" name="email_message" rows="10" required></textarea>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Annuler</button>
                <button type="submit" class="btn btn-primary btn-flat" name="send_email"><i class="fa fa-envelope"></i> Envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>


