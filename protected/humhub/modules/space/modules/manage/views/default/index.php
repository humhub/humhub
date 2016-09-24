<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
?>

<div class="panel panel-default">
    <div>
        <div class="panel-heading">
            <?php echo Yii::t('SpaceModule.views_settings', '<strong>Space</strong> settings'); ?>
        </div>
    </div>

    <?= DefaultMenu::widget(['space' => $model]); ?>
    <div class="panel-body">

        <?php $form = ActiveForm::begin(['options' => ['id' => 'spaceIndexForm'], 'enableClientValidation' => false]); ?>
        
        <?= humhub\modules\space\widgets\SpaceNameColorInput::widget(['form' => $form, 'model' => $model])?>

        <?php echo $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <?php echo $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>


        <?php echo Html::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>

        <div class="pull-right">
            <?php if ($model->isSpaceOwner()) : ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Delete'), $model->createUrl('delete'), array('class' => 'btn btn-danger', 'data-post' => 'POST')); ?>
            <?php endif; ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>