<div class="panel-heading">
    <?php echo Yii::t('UserModule.base', 'User settings'); ?>
</div>
<div class="panel-body">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'user-form',
        'enableAjaxValidation' => false,
    ));
    ?>

    <?php //echo $form->errorSummary($model); ?>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'tags'); ?>
        <?php echo $form->textField($model, 'tags', array('class' => 'form-control')); ?>
        <?php echo $form->error($model, 'tags'); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($model, 'language'); ?>
        <?php echo $form->dropDownList($model, 'language', Yii::app()->params['availableLanguages'], array('class' => 'form-control')); ?>
        <?php echo $form->error($model, 'language'); ?>
    </div>
    <hr>

    <?php echo CHtml::submitButton(Yii::t('UserModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

    <?php $this->endWidget(); ?>
</div>



