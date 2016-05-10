<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;

$this->registerJsFile('@web/resources/space/colorpicker/js/bootstrap-colorpicker-modified.js');
$this->registerCssFile('@web/resources/space/colorpicker/css/bootstrap-colorpicker.min.css');
?>


<div class="panel panel-default">

    <div>
        <div class="panel-heading">
            <?php echo Yii::t('SpaceModule.views_settings', '<strong>Space</strong> settings'); ?>
        </div>
    </div>

    <?= DefaultMenu::widget(['space' => $model]); ?>
    <div class="panel-body">

        <?php $form = ActiveForm::begin(['options' => ['id' => 'spaceIndexForm']]); ?>


         <?= humhub\modules\space\widgets\SpaceNameColorInput::widget(['form' => $form, 'model' => $model])?>


        <?php echo $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <div class="row">
            <div class="col-md-3"> 
                <?php echo $form->field($model, 'indexUrl')->dropDownList($indexModuleSelection) ?>
            </div>
            <div class="col-md-9">
                <?php echo $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>
            </div>
        </div>


        <?php echo Html::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

        <?php echo \humhub\widgets\DataSaved::widget(); ?>

        <div class="pull-right">
            <?php if ($model->status == Space::STATUS_ENABLED) { ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Archive'), $model->createUrl('/space/manage/default/archive'), array('class' => 'btn btn-warning', 'data-post' => 'POST')); ?>
            <?php } elseif ($model->status == Space::STATUS_ARCHIVED) { ?>
                <?php echo Html::a(Yii::t('SpaceModule.views_admin_edit', 'Unarchive'), $model->createUrl('/space/manage/default/unarchive'), array('class' => 'btn btn-warning', 'data-post' => 'POST')); ?>
            <?php } ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>

</div>