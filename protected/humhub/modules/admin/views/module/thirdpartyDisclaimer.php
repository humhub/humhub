<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('AdminModule.modules', 'Third-party disclaimer'); ?>
            </h4>
        </div>
        <div class="modal-body">
            <p>
                <?= Yii::t('AdminModule.modules', 'The HumHub developers provide no support for third-party modules and neighter give any guarantee about the suitability, functionality or security of this module.'); ?>
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('AdminModule.modules', 'Ok'); ?>
            </button>
        </div>
    </div>
</div>

