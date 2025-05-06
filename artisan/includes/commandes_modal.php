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