<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_proxy', '<strong>Proxy</strong> settings'); ?></div>
    <div class="panel-body">

        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'proxy-settings-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($model, 'enabled', array('readonly' => HSetting::IsFixed('enabled', 'proxy'))); ?> <?php echo $model->getAttributeLabel('enabled'); ?>
                </label>
            </div>
        </div>

        <hr>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'server'); ?>
            <?php echo $form->textField($model, 'server', array('class' => 'form-control')); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'port'); ?>
            <?php echo $form->textField($model, 'port', array('class' => 'form-control')); ?>
        </div>

        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_proxy', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>




