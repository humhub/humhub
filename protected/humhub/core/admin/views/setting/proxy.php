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

        <?php if (defined('CURLOPT_PROXYUSERNAME')) { ?>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'user'); ?>
            <?php echo $form->textField($model, 'user', array('class' => 'form-control')); ?>
        </div>
        <?php } ?>

        <?php if (defined('CURLOPT_PROXYPASSWORD')) { ?>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'password'); ?>
            <?php echo $form->textField($model, 'password', array('class' => 'form-control')); ?>
        </div>
        <?php } ?>

        <?php if (defined('CURLOPT_NOPROXY')) { ?>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'noproxy'); ?>
            <?php echo $form->textArea($model, 'noproxy', array('class' => 'form-control', 'rows' => '4')); ?>
        </div>
        <?php } ?>

        <hr>
        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_setting_proxy', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <?php $this->endWidget(); ?>

    </div>
</div>




