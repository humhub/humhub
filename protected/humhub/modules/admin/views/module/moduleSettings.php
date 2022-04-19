<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\models\forms\GeneralModuleSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\ModalButton;
use humhub\widgets\ModalDialog;

/* @var GeneralModuleSettingsForm $settings */
?>
<?php ModalDialog::begin(['header' => Yii::t('AdminModule.modules', '<strong>General</strong> Settings')]) ?>

    <?php $form = ActiveForm::begin() ?>

    <div class="modal-body">
        <?= $form->field($settings, 'includeBetaUpdates')->checkbox() ?>
    </div>

    <div class="modal-footer">
        <?= ModalButton::submitModal()?>
        <?= ModalButton::cancel()?>
    </div>

    <?php ActiveForm::end() ?>

<?php ModalDialog::end() ?>
