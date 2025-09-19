<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $model humhub\modules\activity\models\MailSummaryForm */

/* @var $form \humhub\widgets\form\ActiveForm */

use humhub\helpers\Html;
use humhub\modules\space\widgets\SpacePickerField;
use humhub\widgets\form\ActiveForm;

?>

<?php $form = ActiveForm::begin(['enableClientValidation' => false, 'acknowledge' => true]); ?>

<?= $form->field($model, 'interval')->dropDownList($model->getIntervals()) ?>
<?= $form->field($model, 'limitSpacesMode')->radioList($model->getLimitSpaceModes()) ?>
<?= $form->field($model, 'limitSpaces')->widget(SpacePickerField::class, [])->label(false) ?>
<?= $form->field($model, 'activities')->checkboxList($model->getActivitiesArray(), [
    'labelOptions' => [
        'encode' => false
    ], 'encode' => true]) ?>

<br>
<?= Html::saveButton() ?>
<?php if ($model->canResetAllUsers()): ?>
    <?= Html::a(Yii::t('NotificationModule.base', 'Reset for all users'), ['reset-all-users'], [
        'data-confirm' => Yii::t('NotificationModule.base', 'Do you want to reset the settings concerning email summaries for all users?'),
        'class' => 'btn btn-danger float-end',
        'data-method' => 'POST',
    ]) ?>
<?php endif; ?>
<?php if ($model->userSettingsLoaded): ?>
    <?= Html::a(Yii::t('NotificationModule.base', 'Reset to defaults'), ['reset'], ['class' => 'btn btn-light float-end', 'data-ui-loader' => '', 'data-method' => 'POST']) ?>
<?php endif; ?>

<?php ActiveForm::end(); ?>
