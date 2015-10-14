<?php

use yii\bootstrap\ActiveForm;
use \humhub\compat\CHtml;
use yii\helpers\Html;
use \humhub\models\Setting;
use humhub\modules\space\models\Space;
?>


<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('SpaceModule.views_admin_edit', '<strong>General</strong> space settings'); ?></div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>

        <?php echo $form->field($model, 'name')->textInput(['maxlength' => 45]); ?>

        <?php echo $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <?php echo $form->field($model, 'website')->textInput(['maxlength' => 45]); ?>

        <?php echo $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>

        <hr>

        <?php $joinPolicies = array(0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'Everyone can enter')); ?>
        <?php echo $form->field($model, 'join_policy')->dropdownList($joinPolicies, ['id' => 'join_policy_dropdown']); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose the kind of membership you want to provide for this workspace.'); ?></p>
        <br>

        <?php
        $visibilities = array(
            0 => Yii::t('SpaceModule.base', 'Private (Invisible)'),
            1 => Yii::t('SpaceModule.base', 'Public (Registered users only)')
        );
        if (Setting::Get('allowGuestAccess', 'authentication_internal') == 1) {
            $visibilities[2] = Yii::t('SpaceModule.base', 'Visible for all (members and guests)');
        }
        ?>
        <?php echo $form->field($model, 'visibility')->dropdownList($visibilities, ['id' => 'join_visibility_dropdown']); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose the security level for this workspace to define the visibleness.'); ?></p>

<br>
        <?php $defaultVisibilityLabel = Yii::t('SpaceModule.base', 'Default') . ' (' . ((\humhub\models\Setting::Get('defaultContentVisibility', 'space') == 1) ? Yii::t('SpaceModule.base', 'Public') : Yii::t('SpaceModule.base', 'Private')) . ')'; ?>
        <?php $contentVisibilities = array('' => $defaultVisibilityLabel, 0 => Yii::t('SpaceModule.base', 'Private'), 1 => Yii::t('SpaceModule.base', 'Public')); ?>
        <?php echo $form->field($model, 'default_content_visibility')->dropdownList($contentVisibilities); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose if new content should be public or private by default'); ?></p>


        <hr>

        <?php if (Yii::$app->user->isAdmin() && Setting::Get('enabled', 'authentication_ldap')): ?>
            <?php echo $form->field($model, 'ldap_dn')->textInput(['maxlength' => 255]); ?>
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

        <?php ActiveForm::end(); ?>
    </div>

</div>


