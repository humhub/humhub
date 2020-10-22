<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\xcoin\models\Marketplace;
use kartik\widgets\Select2;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/**
 * @var Marketplace $model
 */
?>

<?php ModalDialog::begin(['header' => Yii::t('AdminModule.views_marketplace_edit', 'Edit Marketplace'), 'closable' => false]) ?>
<?php $form = ActiveForm::begin(['id' => 'marketplace-form']); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <?=
            $form->field($model, 'status')->widget(Select2::class, [
                'data' => [
                    Marketplace::MARKETPLACE_STATUS_DISABLED => 'DISABLED',
                    Marketplace::MARKETPLACE_STATUS_ENABLED => 'ENABLED'
                ],
                'options' => ['placeholder' => '- ' . Yii::t('AdminModule.views_marketplace_edit', 'Select status') . ' - '],
                'theme' => Select2::THEME_BOOTSTRAP,
                'hideSearch' => true,
            ])->label(Yii::t('AdminModule.views_marketplace_edit', 'Status'));
            ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?= ModalButton::submitModal(null, Yii::t('AdminModule.views_marketplace_edit', 'Save')); ?>
    <?= ModalButton::cancel(); ?>
</div>
<?php ActiveForm::end(); ?>
<?php ModalDialog::end() ?>
