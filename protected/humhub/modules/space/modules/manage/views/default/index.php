<?php

use \humhub\compat\CActiveForm;
use \humhub\compat\CHtml;
use yii\helpers\Html;
use \humhub\models\Setting;
use humhub\modules\space\models\Space;
?>


<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('SpaceModule.views_admin_edit', '<strong>General</strong> space settings'); ?></div>
    <div class="panel-body">
        <?php $form = CActiveForm::begin(); ?>

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
        <div class="form-group">
            <?php echo $form->labelEx($model, 'space_type_id'); ?>
            <?php echo $form->dropDownList($model, 'space_type_id', $types, array('class' => 'form-control')); ?>
            <?php echo $form->error($model, 'space_type_id'); ?>
        </div>
        <hr>
        <div class="form-group">
            <?php echo $form->labelEx($model, 'join_policy'); ?>
            <?php $joinPolicies = array(0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'Everyone can enter')); ?>
            <?php echo $form->dropDownList($model, 'join_policy', $joinPolicies, array('class' => 'form-control', 'id' => 'join_policy_dropdown', 'hint' => Yii::t('SpaceModule.views_admin_edit', 'Choose the kind of membership you want to provide for this workspace.'))); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'visibility'); ?>
            <?php
            $visibilities = array(
                0 => Yii::t('SpaceModule.base', 'Private (Invisible)'),
                1 => Yii::t('SpaceModule.base', 'Public (Registered users only)')
            );
            if (Setting::Get('allowGuestAccess', 'authentication_internal') == 1) {
                $visibilities[2] = Yii::t('SpaceModule.base', 'Visible for all (members and guests)');
            }
            ?>
            <?php echo $form->dropDownList($model, 'visibility', $visibilities, array('class' => 'form-control', 'id' => 'join_visibility_dropdown', 'hint' => Yii::t('SpaceModule.views_admin_edit', 'Choose the security level for this workspace to define the visibleness.'))); ?>
            <?php echo $form->error($model, 'visibility'); ?>
        </div>

        <div class="form-group">
            <?php echo $form->labelEx($model, 'default_content_visibility'); ?>
            <?php $defaultVisibilityLabel = Yii::t('SpaceModule.base', 'Default') . ' (' . ((\humhub\models\Setting::Get('defaultContentVisibility', 'space') == 1) ? Yii::t('SpaceModule.base', 'Public') : Yii::t('SpaceModule.base', 'Private')) . ')'; ?>
            <?php $contentVisibilities = array('' => $defaultVisibilityLabel, 0 => Yii::t('SpaceModule.base', 'Private'), 1 => Yii::t('SpaceModule.base', 'Public')); ?>
            <?php echo $form->dropDownList($model, 'default_content_visibility', $contentVisibilities, array('class' => 'form-control')); ?>
        </div>

        <hr>

        <?php if (Yii::$app->user->isAdmin() && Setting::Get('enabled', 'authentication_ldap')): ?>
            <div class="form-group">
                <?php echo $form->labelEx($model, 'ldap_dn'); ?>
                <?php echo $form->textField($model, 'ldap_dn', array('class' => 'form-control', 'maxlength' => 255)); ?>
                <?php echo $form->error($model, 'ldap_dn'); ?>
            </div>
            <hr>
        <?php endif; ?>

        <?php echo CHtml::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>

        <div class="pull-right">
            <?php if ($model->status == Space::STATUS_ENABLED) { ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Archive'), $model->createUrl('/space/manage/default/archive'), array('class' => 'btn btn-warning', 'data-post' => 'POST')); ?>
            <?php } elseif ($model->status == Space::STATUS_ARCHIVED) { ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Unarchive'), $model->createUrl('/space/manage/default/unarchive'), array('class' => 'btn btn-warning', 'data-post' => 'POST')); ?>
            <?php } ?>
            <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Delete'), $model->createUrl('/space/manage/default/delete'), array('class' => 'btn btn-danger')); ?>

        </div>

        <?php CActiveForm::end(); ?>
    </div>

</div>


