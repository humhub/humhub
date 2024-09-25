<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\marketplace\models\forms\GeneralModuleSettingsForm;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var GeneralModuleSettingsForm $settings */
?>
<?php Modal::beginDialog(['header' => Yii::t('MarketplaceModule.base', '<strong>General</strong> Settings')]) ?>

<?php $form = ActiveForm::begin() ?>

<div class="modal-body">
    <?= $form->field($settings, 'includeBetaUpdates')->checkbox() ?>
</div>

<div class="modal-footer">
    <?= ModalButton::cancel() ?>
    <?= ModalButton::submitModal() ?>
</div>

<?php ActiveForm::end() ?>

<?php Modal::endDialog() ?>
