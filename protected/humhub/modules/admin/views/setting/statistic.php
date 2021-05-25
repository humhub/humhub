<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\modules\admin\models\forms\StatisticSettingsForm;
use humhub\modules\ui\form\widgets\CodeMirrorInputWidget;

/* @var $model StatisticSettingsForm */
?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p><?= Yii::t('AdminModule.settings', 'You can add a statistic code snippet (HTML) - which will be added to all rendered pages.')?></p>
<br>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <?= $form->field($model, 'trackingHtmlCode')->widget(CodeMirrorInputWidget::class); ?>
</div>
<hr>

<?= CHtml::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>
