<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/* @var $model humhub\modules\activity\models\MailSummaryForm */
/* @var $form humhub\widgets\ActiveForm */

use humhub\libs\Html;
use humhub\widgets\ActiveForm;
use humhub\modules\space\widgets\SpacePickerField;
?>

<?php $form = ActiveForm::begin(); ?>
<?= $form->field($model, 'interval')->dropDownList($model->getIntervals()); ?>
<?= $form->field($model, 'limitSpacesMode')->radioList($model->getLimitSpaceModes()); ?>
<?= $form->field($model, 'limitSpaces')->widget(SpacePickerField::className(), [])->label(false); ?>
<?= $form->field($model, 'activities')->checkboxList($model->getActivitiesArray(), [
	'labelOptions' => [
		'encode' => false
	], 'encode' => true]); ?>

<br>
<?= Html::saveButton(); ?>
<?php if ($model->userSettingsLoaded): ?>
    <?= Html::a(Yii::t('NotificationModule.base', 'Reset to defaults'), ['reset'], ['class' => 'btn btn-default pull-right', 'data-ui-loader' => '', 'data-method' => 'POST']); ?>
<?php endif; ?>

<?php ActiveForm::end(); ?>