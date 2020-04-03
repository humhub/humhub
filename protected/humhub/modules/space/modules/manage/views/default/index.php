<?php

use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\modules\space\widgets\SpaceNameColorInput;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\widgets\Button;

/* @var $this \humhub\components\View
 * @var $model \humhub\modules\space\models\Space
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

        <?= SpaceNameColorInput::widget(['form' => $form, 'model' => $model]) ?>
        <?= $form->field($model, 'description')->textarea(['rows' => 6]); ?>
        <?= $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>

        <?= Button::save()->submit() ?>

        <div class="pull-right">
            <?= Button::warning(Yii::t('SpaceModule.manage', 'Archive'))
                ->action('space.archive', $model->createUrl('/space/manage/default/archive'))
                ->cssClass('archive')->style(($model->status == Space::STATUS_ENABLED) ? 'display:inline' : 'display:none') ?>

            <?= Button::warning(Yii::t('SpaceModule.manage', 'Unarchive'))
                ->action('space.unarchive', $model->createUrl('/space/manage/default/unarchive'))
                ->cssClass('unarchive')->style(($model->status == Space::STATUS_ARCHIVED) ? 'display:inline' : 'display:none') ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
