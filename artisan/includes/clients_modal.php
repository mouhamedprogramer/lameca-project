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
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-primary btn-block" id="create_order"><i class="fa fa-shopping-cart"></i> Créer une commande</a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-info btn-block" id="send_email"><i class="fa fa-envelope"></i> Envoyer un email</a>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-warning btn-block" id="export_data"><i class="fa fa-file-excel-o"></i> Exporter les données</a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="#" class="btn btn-success btn-block" id="view_orders"><i class="fa fa-list"></i> Voir les commandes</a>
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

<!-- Send Email -->
<div class="modal fade" id="send_email_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><b>Envoyer un email au client</b></h4>
            </div>
            <div class="modal-body">
                <form class="form-horizontal" method="POST" action="clients_send_email.php">
                    <input type="hidden" class="client-id" name="id">
                    <div class="form-group">
                        <label for="email_to" class="col-sm-3 control-label">À</label>
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
                                <option value="">Aucun (email personnalisé)</option>
                                <option value="welcome">Message de bienvenue</option>
                                <option value="promo">Promotion spéciale</option>
                                <option value="reminder">Rappel de panier abandonné</option>
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
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
                <button type="submit" class="btn btn-primary btn-flat" name="send_email"><i class="fa fa-envelope"></i> Envoyer</button>
                </form>
            </div>
        </div>
    </div>
</div>