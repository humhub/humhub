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

<?php $form = ActiveForm::begin() ?>

<?php Modal::beginDialog([
    'title' => Yii::t('MarketplaceModule.base', '<strong>General</strong> Settings'),
    'footer' => ModalButton::cancel() . ' ' . ModalButton::save(),
]) ?>

<?= $form->field($settings, 'includeBetaUpdates')->checkbox() ?>

<?php Modal::endDialog() ?>

<?php ActiveForm::end() ?>
