<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
?>

<?= DefaultMenu::widget(['space' => $model]); ?>
<br/>
<div class="panel panel-default">
    <div
        class="panel-heading"><?php echo Yii::t('SpaceModule.manage', '<strong>General</strong> settings'); ?></div>
    <div class="panel-body">
        <?php $form = ActiveForm::begin(); ?>

        <?php echo $form->field($model, 'name')->textInput(['maxlength' => 45]); ?>

        <?php echo $form->field($model, 'description')->textarea(['rows' => 6]); ?>

        <?php echo $form->field($model, 'website')->textInput(['maxlength' => 45]); ?>

        <?php echo $form->field($model, 'tags')->textInput(['maxlength' => 200]); ?>

        <?php echo Html::submitButton(Yii::t('SpaceModule.views_admin_edit', 'Save'), array('class' => 'btn btn-primary')); ?>

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


