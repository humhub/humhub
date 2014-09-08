<?php
/**
 * Form to create or edit a group
 *
 * @property Group $group the group object
 *
 * @todo Also add a picker for default space
 * @package humhub.modules_core.admin
 * @since 0.5
 */
?>

<div class="panel panel-default">
    <?php if (!$group->isNewRecord) : ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_group_edit', '<strong>Edit</strong> group'); ?></div>
    <?php else: ?>
        <div class="panel-heading"><?php echo Yii::t('AdminModule.views_group_edit', '<strong>Create</strong> new group'); ?></div>
    <?php endif; ?>
    <div class="panel-body">


        <?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'admin-group-form',
            'enableAjaxValidation' => false,
        ));
        ?>

        <?php echo $form->errorSummary($group); ?>

        <div class="form-group">
            <?php echo $form->labelEx($group, 'name'); ?>
            <?php echo $form->textField($group, 'name', array('class' => 'form-control', 'placeholder' => Yii::t('AdminModule.views_group_edit', 'Group name'))); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($group, 'description'); ?>
            <?php echo $form->textArea($group, 'description', array('class' => 'form-control', 'rows' => '5', 'placeholder' => Yii::t('AdminModule.views_group_edit', 'Description'))); ?>
        </div>

        <?php echo $form->labelEx($group, 'defaultSpaceGuid'); ?>
        <?php echo $form->textField($group, 'defaultSpaceGuid', array('class' => 'form-control', 'id' => 'space_select')); ?>

        <?php
        $this->widget('application.modules_core.space.widgets.SpacePickerWidget', array(
            'inputId' => 'space_select',
            'maxSpaces' => 1,
            'model' => $group,
            'attribute' => 'defaultSpaceGuid'
        ));
        ?>

        <?php echo $form->labelEx($group, 'adminGuids'); ?>
        <?php echo $form->textArea($group, 'adminGuids', array('class' => 'span12', 'id' => 'user_select')); ?>
        <?php
        $this->widget('application.modules_core.user.widgets.UserPickerWidget', array(
            'inputId' => 'user_select',
            'maxUsers' => 2,
            'model' => $group,
            'attribute' => 'adminGuids',
            'placeholderText' => 'Add a user'
        ));
        ?>

        <?php if (HSetting::Get('enabled', 'authentication_ldap')): ?>
            <div class="form-group">
                <?php echo $form->labelEx($group, 'ldap_dn'); ?>
                <?php echo $form->textField($group, 'ldap_dn', array('class' => 'form-control', 'placeholder' => Yii::t('AdminModule.views_group_edit', 'Ldap DN'))); ?>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($group, 'can_create_public_spaces'); ?>
                    <?php echo $group->getAttributeLabel('can_create_public_spaces'); ?>
                </label>
            </div>
        </div>

        <div class="form-group">
            <div class="checkbox">
                <label>
                    <?php echo $form->checkBox($group, 'can_create_private_spaces'); ?>
                    <?php echo $group->getAttributeLabel('can_create_private_spaces'); ?>
                </label>
            </div>
        </div>

        <?php echo CHtml::submitButton(Yii::t('AdminModule.views_group_edit', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php
        if (!$group->isNewRecord) {
            echo CHtml::link(Yii::t('AdminModule.views_group_edit', 'Delete'), $this->createUrl('//admin/group/delete', array('id' => $group->id)), array('class' => 'btn btn-danger'));
        }
        ?>


        <?php $this->endWidget(); ?>

    </div>
</div>