<h1><?php echo Yii::t('AdminModule.setting', 'Basic'); ?></h1><br>
<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'basic-settings-form',
    'enableAjaxValidation' => false,
));
?>

<?php echo $form->errorSummary($model); ?>

<div class="form-group">
    <?php echo $form->labelEx($model, 'name'); ?>
    <?php echo $form->textField($model, 'name', array('class' => 'form-control', 'readonly'=>  HSetting::IsFixed('name'))); ?>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'baseUrl'); ?>
    <?php echo $form->textField($model, 'baseUrl', array('class' => 'form-control', 'readonly'=>  HSetting::IsFixed('baseUrl'))); ?>
    <p class="help-block"><?php echo Yii::t('AdminModule.setting', 'E.g. http://example.com/humhub'); ?></p>
</div>

<div class="form-group">
    <?php echo $form->labelEx($model, 'defaultLanguage'); ?>
    <?php echo $form->dropDownList($model, 'defaultLanguage', Yii::app()->getLanguages(), array('class' => 'form-control', 'readonly'=>  HSetting::IsFixed('defaultLanguage'))); ?>
</div>


<?php echo $form->labelEx($model, 'defaultSpaceGuid'); ?>
<?php echo $form->textField($model, 'defaultSpaceGuid', array('class' => 'form-control', 'id' => 'space_select')); ?>
<?php
$this->widget('application.modules_core.space.widgets.SpacePickerWidget', array(
    'inputId' => 'space_select',
    'model' => $model,
    'attribute' => 'defaultSpaceGuid'
));
?>
<p class="help-block"><?php echo Yii::t('AdminModule.settings', 'New users will automatically added to these space(s).'); ?></p>

<br />
<hr>

<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<!-- show flash message after saving -->
<?php $this->widget('application.widgets.DataSavedWidget'); ?>

<?php $this->endWidget(); ?>


