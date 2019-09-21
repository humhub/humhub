<?php

use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\modules\space\widgets\SpaceNameColorInput;
use humhub\widgets\DataSaved;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use humhub\widgets\Button;
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
        
        <?= SpaceNameColorInput::widget(['form' => $form, 'model' => $model])?>

        <?= $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <?= $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>

        <?= Html::submitButton(Yii::t('SpaceModule.manage', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?= DataSaved::widget(); ?>

        <?= Button::danger(Yii::t('SpaceModule.manage', 'Delete'))->right()->link($model->createUrl('delete'))->visible($model->canDelete()) ?>

        <?php ActiveForm::end(); ?>
    </div>

</div>
