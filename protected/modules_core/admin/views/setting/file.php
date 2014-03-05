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
    <?php echo $form->textField($model, 'imageMagickPath', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('imageMagickPath', 'file'))); ?>
    <p class="help-block"><?php echo Yii::t('AdminModule.file', 'Current Image Libary: {currentImageLibary}', array('{currentImageLibary}'=>$currentImageLibary)); ?></p>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'maxFileSize'); ?>
    <?php echo $form->textField($model, 'maxFileSize', array('class' => 'form-control', 'readonly' => HSetting::IsFixed('maxFileSize', 'file'))); ?>
    <p class="help-block"><?php echo Yii::t('AdminModule.file', 'PHP reported a maximum of {maxUploadSize} MB', array('{maxUploadSize}'=>$maxUploadSize)); ?></p>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'useXSendfile', array('disabled' => HSetting::IsFixed('useXSendfile', 'file'))); ?> 
            <?php echo $model->getAttributeLabel('useXSendfile'); ?>
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











