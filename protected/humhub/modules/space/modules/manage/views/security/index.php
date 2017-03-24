<?php

use humhub\modules\space\models\Space;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use humhub\modules\space\modules\manage\widgets\SecurityTabMenu;
?>

<div class="panel panel-default">
    <div>
        <div class="panel-heading">
            <?php echo Yii::t('SpaceModule.views_settings', '<strong>Security</strong> settings'); ?>
        </div>
    </div>

    <?= SecurityTabMenu::widget(['space' => $model]); ?>

    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>

        <?php $joinPolicies = array(0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'Everyone can enter')); ?>
        <?php echo $form->field($model, 'join_policy')->dropDownList($joinPolicies); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose the kind of membership you want to provide for this workspace.'); ?></p>
        <br>

        <?php
        $visibilities = [
            Space::VISIBILITY_NONE => Yii::t('SpaceModule.base', 'Private (Invisible)'),
            Space::VISIBILITY_REGISTERED_ONLY => Yii::t('SpaceModule.base', 'Public (Registered users only)')
        ];
        if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess') == 1) {
            $visibilities[Space::VISIBILITY_ALL] = Yii::t('SpaceModule.base', 'Visible for all (members and guests)');
        }
        ?>
        <?php echo $form->field($model, 'visibility')->dropDownList($visibilities); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose the security level for this workspace to define the visibleness.'); ?></p>

        <br>
        <?php $defaultVisibilityLabel = Yii::t('SpaceModule.base', 'Default') . ' (' . ((Yii::$app->getModule('space')->settings->get('defaultContentVisibility') == 1) ? Yii::t('SpaceModule.base', 'Public') : Yii::t('SpaceModule.base', 'Private')) . ')'; ?>
        <?php $contentVisibilities = array('' => $defaultVisibilityLabel, 0 => Yii::t('SpaceModule.base', 'Private'), 1 => Yii::t('SpaceModule.base', 'Public')); ?>
        <?php echo $form->field($model, 'default_content_visibility')->dropDownList($contentVisibilities); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose if new content should be public or private by default'); ?></p>

        <?php echo Html::submitButton(Yii::t('base', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>

        <?php ActiveForm::end(); ?>
    </div>

</div>


