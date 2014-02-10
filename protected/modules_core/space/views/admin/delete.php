<div class="panel panel-default">
    <div class="panel-heading">
        <?php echo Yii::t('SpaceModule.base', 'Delete space'); ?>
    </div>
    <div class="panel-body">
        <p><?php echo Yii::t('SpaceModule.base', 'Are you sure, that you want to delete this space? All published content will be removed!'); ?></p>
        <p><?php echo Yii::t('SpaceModule.base', 'Please provide your password to continue!'); ?></p><br>

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-delete-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php echo $form->passwordField($model, 'currentPassword', array('class' => 'form-control')); ?>

        <hr>
        <?php echo CHtml::submitButton(Yii::t('SpaceModule.base', 'Delete'), array('class' => 'btn btn-danger')); ?>

        <?php $this->endWidget(); ?>
    </div>
</div>




