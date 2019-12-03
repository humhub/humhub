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
            <p class='help-block' style='margin-top:-30px;margin-bottom:20px'>
<?= Yii::t('SpaceModule.settings', 'If this space is public, its name is also public and can be used to find it.<br/>
If this space is using the default space image, you can choose the image color here.'); ?></p>

        <?= $form->field($model, 'description')->textarea(['rows' => 6]); ?>
            <p class='help-block' style='margin-top:-30px;margin-bottom:20px'>
<?= Yii::t('SpaceModule.settings', 'If this space is public, its description is also public and can be used to find it.<br/>
This description will be visible below the space name everywhere it appears.'); ?></p>

        <?= $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>
           <p class='help-block' style='margin-top:-30px;margin-bottom:20px'>
<?= Yii::t('SpaceModule.settings', 'If this space is public, its tags are also public and can be used to find it.<br/>These tags will be visible in the Space Directory.'); ?></p><br/>

        <?= Html::submitButton(Yii::t('SpaceModule.manage', 'Save'), ['class' => 'btn btn-primary', 'data-ui-loader' => '']); ?>

        <?= DataSaved::widget(); ?>

        <?= Button::danger(Yii::t('SpaceModule.manage', 'Delete'))->right()->link($model->createUrl('delete'))->visible($model->canDelete()) ?>

        <?php ActiveForm::end(); ?>
    </div>

</div>
