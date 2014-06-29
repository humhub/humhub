<?php
/** @var $this AdminController */
/** @var $model Space */
?>

<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('SpaceModule.base', '<strong>General</strong> space settings'); ?></div>
    <div class="panel-body">
        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'space-edit-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php //echo $form->errorSummary($model); ?>


        <div class="form-group">
            <?php echo $form->labelEx($model, 'name'); ?>
            <?php echo $form->textField($model, 'name', array('class' => 'form-control', 'maxlength' => 45)); ?>
            <?php echo $form->error($model, 'name'); ?>
        </div>


        <div class="form-group">
            <?php echo $form->labelEx($model, 'description'); ?>
            <?php echo $form->textArea($model, 'description', array('class' => 'form-control', 'rows' => '6')); ?>
            <?php echo $form->error($model, 'description'); ?>
        </div>


        <div class="form-group">
            <?php echo $form->labelEx($model, 'website'); ?>
            <?php echo $form->textField($model, 'website', array('class' => 'form-control', 'maxlength' => 45)); ?>
            <?php echo $form->error($model, 'website'); ?>
        </div>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'tags'); ?>
            <?php echo $form->textField($model, 'tags', array('class' => 'form-control', 'maxlength' => 200)); ?>
            <?php echo $form->error($model, 'tags'); ?>
        </div>
        <hr>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'join_policy'); ?>
            <?php $joinPolicies = array(0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'For everyone')); ?>
            <?php echo $form->dropDownList($model, 'join_policy', $joinPolicies, array('class' => 'form-control', 'id' => 'join_policy_dropdown', 'hint' => Yii::t('SpaceModule.base', 'Choose the kind of membership you want to provide for this workspace.'))); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'visibility'); ?>
            <?php $visibilities = array(0 => Yii::t('SpaceModule.base', 'Invisible'), 1 => Yii::t('SpaceModule.base', 'Visible for the community'), 2 => Yii::t('SpaceModule.base', 'Visible for all')); ?>
            <?php echo $form->dropDownList($model, 'visibility', $visibilities, array('class' => 'form-control', 'id' => 'join_visibility_dropdown', 'hint' => Yii::t('SpaceModule.base', 'Choose the security level for this workspace to define the visibleness.'))); ?>
        </div>
        <hr>

        <?php if (Yii::app()->user->isAdmin() && HSetting::Get('enabled', 'authentication_ldap')): ?>
            <div class="form-group">
                <?php echo $form->labelEx($model, 'ldap_dn'); ?>
                <?php echo $form->textField($model, 'ldap_dn', array('class' => 'form-control', 'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'ldap_dn'); ?>
            </div>
            <hr>
        <?php endif; ?>

        <?php echo CHtml::submitButton(Yii::t('SpaceModule.base', 'Save'), array('class' => 'btn btn-primary')); ?>

        <!-- show flash message after saving -->
        <?php $this->widget('application.widgets.DataSavedWidget'); ?>

        <div class="pull-right">
            <?php if ($space->status == Space::STATUS_ENABLED) { ?>
                <a href="<?php echo $this->createUrl('//space/admin/archive', array('sguid' => $space->guid)); ?>"
                   class="btn btn-warning"><?php echo Yii::t('SpaceModule.base', 'Archive'); ?></a>
            <?php } elseif ($space->status == Space::STATUS_ARCHIVED) { ?>
                <a href="<?php echo $this->createUrl('//space/admin/unarchive', array('sguid' => $space->guid)); ?>"
                   class="btn btn-warning"><?php echo Yii::t('SpaceModule.base', 'Unarchive'); ?></a>
            <?php } ?>
            <a href="<?php echo $this->createUrl('//space/admin/delete', array('sguid' => $space->guid)); ?>"
               class="btn btn-danger"><?php echo Yii::t('SpaceModule.base', 'Delete'); ?></a>
        </div>


    </div>

</div>
<?php $this->endWidget(); ?>

