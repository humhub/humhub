<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\SecurityTabMenu;
use humhub\widgets\DataSaved;
use yii\bootstrap\ActiveForm;
use humhub\libs\Html;

/* @var $model Space */
/* @var $visibilities array */
?>

<div class="panel panel-default">
    <div>
        <div class="panel-heading">
            <?= Yii::t('SpaceModule.manage', '<strong>Security</strong> settings'); ?>
        </div>
    </div>

    <?= SecurityTabMenu::widget(['space' => $model]); ?>

    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'visibility')->dropDownList($visibilities); ?>

        <?php $joinPolicies = [0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'Everyone can enter')]; ?>
        <?= $form->field($model, 'join_policy')->dropDownList($joinPolicies, ['disabled' => $model->visibility == Space::VISIBILITY_NONE]); ?>

        <?php $defaultVisibilityLabel = Yii::t('SpaceModule.base', 'Default') . ' (' . ((Yii::$app->getModule('space')->settings->get('defaultContentVisibility') == 1) ? Yii::t('SpaceModule.base', 'Public') : Yii::t('SpaceModule.base', 'Private')) . ')'; ?>
        <?php $contentVisibilities = ['' => $defaultVisibilityLabel, 0 => Yii::t('SpaceModule.base', 'Private'), 1 => Yii::t('SpaceModule.base', 'Public')]; ?>
        <?= $form->field($model, 'default_content_visibility')->dropDownList($contentVisibilities, ['disabled' => $model->visibility == Space::VISIBILITY_NONE]); ?>

        <?= Html::submitButton(Yii::t('base', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?= DataSaved::widget(); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<script <?= Html::nonce() ?>>
    $('#space-visibility').on('change', function() {
        if (this.value == 0) {
            $('#space-join_policy, #space-default_content_visibility').val('0').prop('disabled', true);
        } else {
            $('#space-join_policy, #space-default_content_visibility').val('0').prop('disabled', false);
        }
    });
</script>
