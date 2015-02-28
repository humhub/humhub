<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_design', '<strong>Design</strong> settings'); ?></div>
    <div class="panel-body">

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'design-settings-form',
            'enableAjaxValidation' => true,
            'clientOptions' => array(
                'validateOnType' => false,
                'validateOnChange' => false,
                'validateOnSubmit' => true
            )
        ));
        ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'theme'); ?>
            <?php echo $form->dropDownList($model, 'theme', $themes, array('class' => 'form-control')); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'paginationSize'); ?>
            <?php echo $form->textField($model, 'paginationSize', array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'paginationSize'); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'displayName'); ?>
            <?php echo $form->dropDownList($model, 'displayName', array('{username}' => Yii::t('AdminModule.views_setting_design', 'Username (e.g. john)'), '{profile.firstname} {profile.lastname}' => Yii::t('AdminModule.views_setting_design', 'Firstname Lastname (e.g. John Doe)')), array('class' => 'form-control')); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'spaceOrder'); ?>
            <?php echo $form->dropDownList($model, 'spaceOrder', array('0' => Yii::t('AdminModule.views_setting_design', 'Alphabetical'), '1' => Yii::t('AdminModule.views_setting_design', 'Last visit')), array('class' => 'form-control')); ?>
        </div>

        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_design', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>




