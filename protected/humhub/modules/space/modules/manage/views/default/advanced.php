<?php

use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\widgets\Button;
use humhub\modules\ui\form\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this \humhub\components\View
 * @var $model \humhub\modules\space\modules\manage\models\AdvancedSettingsSpace
 * @var $indexModuleSection array
 */

?>

<div class="panel panel-default">
    <div>
        <div class="panel-heading">
            <?= Yii::t('SpaceModule.manage', '<strong>Space</strong> settings'); ?>
        </div>
    </div>

    <?= DefaultMenu::widget(['space' => $model]); ?>

    <div class="panel-body">

        <?php $form = ActiveForm::begin(['options' => ['id' => 'spaceIndexForm'], 'enableClientValidation' => false]); ?>
        <?php if (Yii::$app->urlManager->enablePrettyUrl) : ?>
            <?= $form->field($model, 'url')->hint(Yii::t('SpaceModule.manage', 'e.g. example for {baseUrl}/s/example', ['baseUrl' => Url::base(true)])); ?>
        <?php endif; ?>
        <?= $form->field($model, 'indexUrl')->dropDownList($indexModuleSelection)->hint(Yii::t('SpaceModule.manage', 'the default start page of this space for members')) ?>
        <?= $form->field($model, 'indexGuestUrl')->dropDownList($indexModuleSelection)->hint(Yii::t('SpaceModule.manage', 'the default start page of this space for visitors')) ?>

        <?= Button::save()->submit() ?>
        <?= Button::danger(Yii::t('base', 'Delete'))->right()->link($model->createUrl('delete'))->visible($model->canDelete()) ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
