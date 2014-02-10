<h1><?php echo Yii::t('AdminModule.base', 'Authentication - Settings'); ?></h1><br>
<?php $form = $this->beginWidget('CActiveForm', array(
    'id' => 'authentication-settings-form',
    'id' => 'file-settings-form',
    'enableAjaxValidation' => false,
)); ?>

<?php echo $form->errorSummary($model); ?>

<h2><?php echo Yii::t('AdminModule.base', 'Build-In authentication'); ?></h2>
<br>
<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'authInternal', array('hint' => Yii::t('AdminModule.base', 'Admin users can always login!'))); ?> <?php echo $model->getAttributeLabel('authInternal'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'internalAllowAnonymousRegistration'); ?> <?php echo $model->getAttributeLabel('internalAllowAnonymousRegistration'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'internalUsersCanInvite', array('hint' => Yii::t('AdminModule.base', 'Possible in space member invite!'))); ?> <?php echo $model->getAttributeLabel('internalUsersCanInvite'); ?>
        </label>
    </div>
</div>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'internalRequireApprovalAfterRegistration'); ?> <?php echo $model->getAttributeLabel('internalRequireApprovalAfterRegistration'); ?>
        </label>
    </div>
</div>

<hr>

<h2><?php echo Yii::t('AdminModule.base', 'LDAP Authentication'); ?></h2>
<?php echo Yii::t('AdminModule.base', 'Experimental yet, please see ldap documentation for more details!'); ?><br>

<div class="form-group">
    <div class="checkbox">
        <label>
            <?php echo $form->checkBox($model, 'authLdap'); ?>  <?php echo $model->getAttributeLabel('authLdap'); ?>
        </label>
    </div>
</div>

<hr>

<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>





