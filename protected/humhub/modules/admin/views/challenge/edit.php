<?php

use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\xcoin\models\Challenge;
use kartik\widgets\Select2;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/**
 * @var Challenge $model
 */

?>

<?php ModalDialog::begin(['header' => Yii::t('AdminModule.views_challenge_edit', 'Edit Challenge'), 'closable' => false]) ?>
<?php $form = ActiveForm::begin(['id' => 'challenge-form']); ?>

<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <?=
            $form->field($model, 'status')->widget(Select2::class, [
                'data' => [
                    Challenge::CHALLENGE_STATUS_DISABLED => 'DISABLED',
                    Challenge::CHALLENGE_STATUS_ENABLED => 'ENABLED'
                ],
                'options' => ['placeholder' => '- ' . Yii::t('AdminModule.views_challenge_edit', 'Select status') . ' - '],
                'theme' => Select2::THEME_BOOTSTRAP,
                'hideSearch' => true,
            ])->label(Yii::t('AdminModule.views_challenge_edit', 'Status'));
            ?>
        </div>
    </div>
</div>

<div class="modal-footer">
    <?= ModalButton::submitModal(null, Yii::t('AdminModule.views_category_create', 'Save')); ?>
    <?= ModalButton::cancel(); ?>
</div>
<?php ActiveForm::end(); ?>
<?php ModalDialog::end() ?>
