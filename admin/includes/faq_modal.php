<!-- Modal Ajouter FAQ -->
<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Ajouter une FAQ</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="faq_add.php">
                <div class="form-group">
                    <label for="question" class="col-sm-3 control-label">Question</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" id="question" name="question" placeholder="Entrez la question" rows="3" required></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="reponse" class="col-sm-3 control-label">Réponse</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" id="reponse" name="reponse" placeholder="Entrez la réponse" rows="5" required></textarea>
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

<!-- Modal Modifier FAQ -->
<div class="modal fade" id="edit">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Modifier la FAQ</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="faq_edit.php">
                <input type="hidden" class="faq-id" name="id">
                <div class="form-group">
                    <label for="edit_question" class="col-sm-3 control-label">Question</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" id="edit_question" name="question" rows="3" required></textarea>
                    </div>
                </div>
                <div class="form-group">
                    <label for="edit_reponse" class="col-sm-3 control-label">Réponse</label>
                    <div class="col-sm-9">
                      <textarea class="form-control" id="edit_reponse" name="reponse" rows="5" required></textarea>
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

<!-- Modal Supprimer FAQ -->
<div class="modal fade" id="delete">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Supprimer la FAQ</b></h4>
            </div>
            <div class="modal-body">
              <form class="form-horizontal" method="POST" action="faq_delete.php">
                <input type="hidden" class="faq-id" name="id">
                <div class="text-center">
                    <p>Êtes-vous sûr de vouloir supprimer cette FAQ ?</p>
                    <h4 class="faq-question text-bold"></h4>
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

<!-- Modal Voir FAQ -->
<div class="modal fade" id="view">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Détails de la FAQ</b></h4>
            </div>
            <div class="modal-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="box box-solid">
                    <div class="box-body">
                      <dl class="dl-horizontal">
                        <dt>ID:</dt>
                        <dd id="view_id"></dd>
                        <dt>Question:</dt>
                        <dd id="view_question" class="text-justify"></dd>
                        <dt>Réponse:</dt>
                        <dd id="view_reponse" class="text-justify"></dd>
                      </dl>
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