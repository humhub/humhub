<?php

use Yii;
use \humhub\compat\CActiveForm;
use \humhub\compat\CHtml;
use yii\helpers\Html;
use \humhub\models\Setting;
use humhub\core\space\models\Space;
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
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Archive'), $model->createUrl('//space/admin/archive'), array('class' => 'btn btn-warning', 'data-method' => 'POST')); ?>
            <?php } elseif ($model->status == Space::STATUS_ARCHIVED) { ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Unarchive'), $model->createUrl('//space/admin/unarchive'), array('class' => 'btn btn-warning', 'data-method' => 'POST')); ?>
            <?php } ?>
            <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Delete'), $model->createUrl('//space/admin/delete'), array('class' => 'btn btn-danger', 'data-method' => 'POST')); ?>

        </div>

        <?php CActiveForm::end(); ?>
    </div>

</div>


