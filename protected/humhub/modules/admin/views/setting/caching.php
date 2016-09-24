<?php

use humhub\compat\CActiveForm;
use humhub\compat\CHtml;
use humhub\models\Setting;
?>

<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = CActiveForm::begin(); ?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'type'); ?>
    <?php echo $form->dropDownList($model, 'type', $cacheTypes, array('class' => 'form-control', 'readonly' => Setting::IsFixed('cache.class'))); ?>
    <br>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'expireTime'); ?>
    <?php echo $form->textField($model, 'expireTime', array('class' => 'form-control', 'readonly' => Setting::IsFixed('cache.expireTime'))); ?>
    <br>
</div>

<hr>
<?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_caching', 'Save & Flush Caches'), array('class' => 'btn btn-primary', 'data-ui-loader' => "")); ?>

<?php echo \humhub\widgets\DataSaved::widget(); ?>
<?php CActiveForm::end(); ?>

<?php $this->endContent(); ?>
