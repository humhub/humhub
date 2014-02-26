<ul class="nav nav-pills">
    <li class="active"><a href="<?php echo $this->createUrl('authentication'); ?>"><?php echo Yii::t('AdminModule.authentication', 'Basic'); ?></a></li>
    <li><a href="<?php echo $this->createUrl('authenticationLdap'); ?>"><?php echo Yii::t('AdminModule.authentication', 'LDAP'); ?></a></li>
</ul>


<h1><?php echo Yii::t('AdminModule.authentication', 'Authentication - Basic'); ?></h1><br />
   
<?php $form = $this->beginWidget('CActiveForm', array(
    'id' => 'authentication-settings-form',
    'id' => 'file-settings-form',
    'enableAjaxValidation' => false,
)); ?>

<?php echo $form->errorSummary($model); ?>

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

<hr />

<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<?php $this->endWidget(); ?>





