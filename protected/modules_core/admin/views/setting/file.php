<h1><?php echo Yii::t('AdminModule.file', 'File - Settings'); ?></h1><br>
<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'file-settings-form',
    'enableAjaxValidation' => false,
));
?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'imageMagickPath'); ?>
    <?php echo $form->textField($model, 'imageMagickPath', array('class' => 'form-control', 'hint' => Yii::t('AdminModule.file', 'e.g. Linux=/usr/bin/convert or  MacOSX = /opt/local/bin/convert or Win = C:\wamp\imagemagick\convert.exe'))); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'maxFileSize'); ?>
    <?php echo $form->textField($model, 'maxFileSize', array('class' => 'form-control')); ?>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'useXSendfile'); ?> <?php echo $model->getAttributeLabel('useXSendfile'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'forbiddenExtensions'); ?>
    <?php echo $form->textField($model, 'forbiddenExtensions', array('class' => 'form-control', 'hint' => Yii::t('AdminModule.file', '(comma separated)'))); ?>
</div>

<hr>

<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>











