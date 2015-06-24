<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_setting_statistic', '<strong>Statistic</strong> settings'); ?></div>
    <div class="panel-body">

        <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'statistic-settings-form',
            'enableAjaxValidation' => false,
        )); ?>

        <?php echo $form->errorSummary($model); ?>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'trackingHtmlCode'); ?>
            <?php echo $form->textArea($model, 'trackingHtmlCode', array('class' => 'form-control', 'rows' => '8')); ?>
        </div>
        <hr>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_statistic', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>