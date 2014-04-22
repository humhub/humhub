<ul class="nav nav-pills">
    <li class="active"><a href="<?php echo $this->createUrl('authentication'); ?>"><?php echo Yii::t('AdminModule.authentication', 'Basic'); ?></a></li>
    <li><a href="<?php echo $this->createUrl('authenticationLdap'); ?>"><?php echo Yii::t('AdminModule.authentication', 'LDAP'); ?></a></li>
</ul>


<h1><?php echo Yii::t('AdminModule.authentication', 'Authentication - Basic'); ?></h1><br />

<?php
$form = $this->beginWidget('CActiveForm', array(
    'id' => 'authentication-settings-form',
    'id' => 'file-settings-form',
    'enableAjaxValidation' => false,
        ));
?>

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
            <?php echo $form->checkBox($model, 'internalUsersCanInvite'); ?> <?php echo $model->getAttributeLabel('internalUsersCanInvite'); ?>
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

<div class="form-group">
    <?php echo $form->labelEx($model, 'defaultUserGroup'); ?>
    <?php echo $form->dropDownList($model, 'defaultUserGroup', $groups, array('class' => 'form-control', 'readonly' => HSetting::IsFixed('defaultUserGroup', 'authentication_internal'))); ?>
</div>

<hr />

<?php echo CHtml::submitButton(Yii::t('AdminModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

<!-- show flash message after saving -->
<?php $this->widget('application.widgets.DataSavedWidget'); ?>

<?php $this->endWidget(); ?>





