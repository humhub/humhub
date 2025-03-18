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

        <?= $form->field($model, 'visibility')->dropDownList($visibilities, [
            'data' => [
                'action-change' => 'space.changeVisibilityOption',
            ]
        ]); ?>

        <?php $joinPolicies = [0 => Yii::t('SpaceModule.base', 'Only by invite'), 1 => Yii::t('SpaceModule.base', 'Invite and request'), 2 => Yii::t('SpaceModule.base', 'Everyone can enter')]; ?>
        <?= $form->field($model, 'join_policy')->dropDownList($joinPolicies, ['disabled' => $model->visibility == Space::VISIBILITY_NONE]); ?>

        <?php $defaultVisibilityLabel = Yii::t('SpaceModule.base', 'Default') . ' (' . ((Yii::$app->getModule('space')->settings->get('defaultContentVisibility') == 1) ? Yii::t('SpaceModule.base', 'Public') : Yii::t('SpaceModule.base', 'Private')) . ')'; ?>
        <?php $contentVisibilities = ['' => $defaultVisibilityLabel, 0 => Yii::t('SpaceModule.base', 'Private'), 1 => Yii::t('SpaceModule.base', 'Public')]; ?>
        <?= $form->field($model, 'default_content_visibility')->dropDownList($contentVisibilities, ['disabled' => $model->visibility == Space::VISIBILITY_NONE]); ?>

        <?= Html::submitButton(
            Yii::t('base', 'Save'),
            [
                'class' => 'btn btn-primary',
                'data' => [
                    'ui-loader' => '',
                    'action-confirm-header' => Yii::t('SpaceModule.base', 'Change visibility'),
                    'confirm-text' => Yii::t('SpaceModule.base', 'Warning: If you change the visibility settings of a Space from public to private, all content within that Space, including posts, comments, attachments etc. will also be set to private. This means that non-members will no longer be able to see, access, or interact with any of the content within that Space.'),
                ],
            ],
        ); ?>

        <?= DataSaved::widget(); ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
