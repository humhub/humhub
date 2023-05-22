<?php

use humhub\modules\space\models\AdvancedSettings;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\widgets\Button;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $model AdvancedSettings */
/* @var $indexModuleSelection array */
/* @var $space Space */
?>

<div class="panel panel-default">
    <div>
        <div class="panel-heading">
            <?= Yii::t('SpaceModule.manage', '<strong>Space</strong> settings'); ?>
        </div>
    </div>

    <?= DefaultMenu::widget(['space' => $space]); ?>

    <div class="panel-body">

        <?php $form = ActiveForm::begin(['options' => ['id' => 'spaceIndexForm'], 'enableClientValidation' => false, 'acknowledge' => true]); ?>
        <?php if (Yii::$app->urlManager->enablePrettyUrl) : ?>
            <?= $form->field($model, 'url')->hint(Yii::t('SpaceModule.manage', 'e.g. example for {baseUrl}/s/example', ['baseUrl' => Url::base(true)])); ?>
        <?php endif; ?>
        <?= $form->field($model, 'hideMembers')->checkbox(); ?>
        <?= $form->field($model, 'hideAbout')->checkbox(); ?>
        <?= $form->field($model, 'hideActivities')->checkbox(); ?>
        <?= $form->field($model, 'hideFollowers')->checkbox(); ?>
        <?= $form->field($model, 'indexUrl')->dropDownList($indexModuleSelection) ?>
        <?= $form->field($model, 'indexGuestUrl')->dropDownList($indexModuleSelection) ?>

        <?= Button::save()->submit() ?>
        <?= Button::danger(Yii::t('base', 'Delete'))->right()->link($space->createUrl('delete'))->visible($space->canDelete()) ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
