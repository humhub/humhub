<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p><?= Yii::t('AdminModule.settings', 'You can add a statistic code snippet (HTML) - which will be added to all rendered pages.')?></p>
<br>

<?php $form = CActiveForm::begin(); ?>

<?= $form->errorSummary($model); ?>

<div class="form-group">
    <?= $form->labelEx($model, 'trackingHtmlCode'); ?>
    <?= $form->textArea($model, 'trackingHtmlCode', ['class' => 'form-control', 'rows' => '8']); ?>
</div>
<hr>

<?= CHtml::submitButton(Yii::t('AdminModule.settings', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>

<?= \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>
