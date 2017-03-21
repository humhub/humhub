<?php

use yii\helpers\Html;
use humhub\compat\CActiveForm;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
?>

<div class="panel panel-default">
    <div class="panel-heading">
       <?= Yii::t('SpaceModule.views_settings', '<strong>Space</strong> settings'); ?>
    </div>
    <?= DefaultMenu::widget(['space' => $space]); ?>
    
    <div class="panel-body">
        <p><?= Yii::t('SpaceModule.views_admin_delete', 'Are you sure, that you want to delete this space? All published content will be removed!'); ?></p>
        <p><?= Yii::t('SpaceModule.views_admin_delete', 'Please provide your password to continue!'); ?></p><br>

        <?php $form = CActiveForm::begin(); ?>

        <div class="form-group">
            <?= $form->labelEx($model, 'currentPassword'); ?>
            <?= $form->passwordField($model, 'currentPassword', array('class' => 'form-control', 'rows' => '6')); ?>
            <?= $form->error($model, 'currentPassword'); ?>
        </div>

        <hr>
        <?= Html::submitButton(Yii::t('SpaceModule.views_admin_delete', 'Delete'), array('class' => 'btn btn-danger', 'data-ui-loader' => '')); ?>

        <?php CActiveForm::end(); ?>
    </div>
</div>