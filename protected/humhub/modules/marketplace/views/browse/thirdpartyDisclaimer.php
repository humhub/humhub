<div class="modal-dialog modal-dialog-normal animated fadeIn">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title" id="myModalLabel">
                <?= Yii::t('MarketplaceModule.base', 'Third-party disclaimer'); ?>
            </h4>
        </div>
        <div class="modal-body">
            <p>
                <?= Yii::t('MarketplaceModule.base', 'This Module was developed by a third-party.'); ?>
                <?= Yii::t('MarketplaceModule.base', 'The HumHub project does not guarantee the functionality, quality or the continuous development of this Module.'); ?>
            </p>

            <p>
                <?= Yii::t('MarketplaceModule.base', 'Third-party Modules are not covered by Professional Edition agreements.'); ?>
            </p>

            <p>
                <?= Yii::t('MarketplaceModule.base', 'If this Module is additionally marked as <strong>"Community"</strong> it is neither tested nor monitored by the HumHub project team.'); ?>
            </p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-dismiss="modal">
                <?= Yii::t('MarketplaceModule.base', 'Ok'); ?>
            </button>
        </div>
    </div>
</div>

