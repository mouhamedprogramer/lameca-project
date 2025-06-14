<!-- Config -->
<div class="modal fade" id="config_desc">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
              <h4 class="modal-title"><b>Configurer</b></h4>
            </div>
            <div class="modal-body">
              <div class="text-center">
                <?php
                  $parse = parse_ini_file('config_desc.ini', FALSE, INI_SCANNER_RAW);
                  $title = $parse['election_title'];
                ?>
                <form class="form-horizontal" method="POST" action="config_save_desc.php?return=<?php echo basename($_SERVER['PHP_SELF']); ?>">
                  <div class="form-group">
                    <label for="title" class="col-sm-3 control-label">Description</label>

                    <div class="col-sm-9">
                      <input type="text" class="form-control" id="title" name="title" value="<?php echo $title; ?>">
                    </div>
                  </div>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Fermer</button>
              <button type="submit" class="btn btn-success btn-flat" name="save"><i class="fa fa-save"></i> Sauvegarder</button>
              </form>
            </div>
        </div>
    </div>
</div>