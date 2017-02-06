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
    <p class="help-block"><?php echo Yii::t('AdminModule.views_setting_caching', 'PHP 7.0 and above use APCu instead of APC!') ?></p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'useApcu', array('disabled' => Setting::IsFixed('cache.useApcu'))); ?>
            <?php echo $model->getAttributeLabel('useApcu'); ?>
        </label>
    </div>
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
