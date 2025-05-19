<div class="modal fade" id="modifierModal" tabindex="-1" role="dialog" aria-labelledby="modifierModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="portfolioForm" method="POST" action="portfolio_update.php">
        <div class="modal-header">
          <h3 class="modal-title" id="modifierModalLabel">Modifier votre texte de présentation</h3>
          <button type="button" class="close" data-dismiss="modal" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <textarea name="portfolio" id="portfolioText" class="form-control" rows="5" required></textarea>
          <input type="hidden" name="idArtisan" id="idArtisan" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>