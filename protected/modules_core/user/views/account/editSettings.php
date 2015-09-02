<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_editSettings', '<strong>User</strong> settings'); ?>
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

    <?php if (HSetting::Get('allowGuestAccess', 'authentication_internal')): ?>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'visibility'); ?>
            <?php
            echo $form->dropDownList($model, 'visibility', array(
                1 => Yii::t('UserModule.views_account_editSettings', 'Registered users only'),
                2 => Yii::t('UserModule.views_account_editSettings', 'Visible for all (also unregistered users)'),
                    ), array('class' => 'form-control'));
            ?>
            <?php echo $form->error($model, 'visibility'); ?>
        </div>
    <?php endif; ?>

    <strong><?php echo Yii::t('UserModule.views_account_editSettings', 'Getting Started'); ?></strong>
    <div class="form-group">
        <div class="checkbox">
            <label>
                <?php echo $form->checkBox($model, 'show_introduction_tour'); ?> <?php echo $model->getAttributeLabel('show_introduction_tour'); ?>
            </label>
        </div>
    </div>

    <hr>

    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_editSettings', 'Save'), array('class' => 'btn btn-primary')); ?>

    <!-- show flash message after saving -->
    <?php $this->widget('application.widgets.DataSavedWidget'); ?>

    <?php $this->endWidget(); ?>
</div>



