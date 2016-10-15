<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;


?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<p><?php echo Yii::t('AdminModule.views_setting_statistic', 'You can add an statistics HTML code snippet - which will added to all rendered pags.')?></p>
<br />

<?php $form = CActiveForm::begin(); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'trackingHtmlCode'); ?>
    <?php echo $form->textArea($model, 'trackingHtmlCode', array('class' => 'form-control', 'rows' => '8')); ?>
</div>
<hr>

<?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_statistic', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?php echo \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>
