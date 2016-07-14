<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use \humhub\models\Setting;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
?>

<?= DefaultMenu::widget(['space' => $model]); ?>
<br/>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('SpaceModule.manage', '<strong>Security</strong> settings'); ?></div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>

        <?php $joinPolicies = array(0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'Everyone can enter')); ?>
        <?php echo $form->field($model, 'join_policy')->dropdownList($joinPolicies); ?>
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
        <?php echo $form->field($model, 'visibility')->dropdownList($visibilities); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose the security level for this workspace to define the visibleness.'); ?></p>

        <br>
        <?php $defaultVisibilityLabel = Yii::t('SpaceModule.base', 'Default') . ' (' . ((\humhub\models\Setting::Get('defaultContentVisibility', 'space') == 1) ? Yii::t('SpaceModule.base', 'Public') : Yii::t('SpaceModule.base', 'Private')) . ')'; ?>
        <?php $contentVisibilities = array('' => $defaultVisibilityLabel, 0 => Yii::t('SpaceModule.base', 'Private'), 1 => Yii::t('SpaceModule.base', 'Public')); ?>
        <?php echo $form->field($model, 'default_content_visibility')->dropdownList($contentVisibilities); ?>
        <p class="help-block"><?php echo Yii::t('SpaceModule.views_admin_edit', 'Choose if new content should be public or private by default'); ?></p>

        <?php echo Html::submitButton(Yii::t('base', 'Save'), array('class' => 'btn btn-primary')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>

        <?php ActiveForm::end(); ?>
    </div>

</div>


