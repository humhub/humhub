<?php

use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\modules\space\widgets\SpaceNameColorInput;
use humhub\widgets\DataSaved;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
?>

<div class="panel panel-default">
    <div>
        <div class="panel-heading">
            <?= Yii::t('SpaceModule.views_settings', '<strong>Space</strong> settings'); ?>
        </div>
    </div>

    <?= DefaultMenu::widget(['space' => $model]); ?>
    <div class="panel-body">

        <?php $form = ActiveForm::begin(['options' => ['id' => 'spaceIndexForm'], 'enableClientValidation' => false]); ?>
        
        <?= SpaceNameColorInput::widget(['form' => $form, 'model' => $model])?>

        <?= $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <?= $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>

        <?= Html::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?= DataSaved::widget(); ?>

        <div class="pull-right">
            <?php if ($model->isSpaceOwner()) : ?>
                <?= Html::a(Yii::t('SpaceModule.views_admin_edit', 'Delete'), $model->createUrl('delete'), ['class' => 'btn btn-danger', 'data-post' => 'POST']); ?>
            <?php endif; ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>
