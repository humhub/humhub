<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.user', 'Accept user: <strong>{displayName}</strong> ', ['{displayName}' => Html::encode($model->displayName)]); ?></h4>

    <?php $form = CActiveForm::begin(); ?>

    <div class="form-group">
        <?= $form->labelEx($approveFormModel, 'subject'); ?>
        <?= $form->textField($approveFormModel, 'subject', ['class' => 'form-control']); ?>
        <?= $form->error($approveFormModel, 'subject'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($approveFormModel, 'message'); ?>
        <?= $form->textArea($approveFormModel, 'message', ['rows' => 6, 'cols' => 50, 'class' => 'form-control autosize']); ?>
        <?= $form->error($approveFormModel, 'message'); ?>
    </div>


    <hr>
    <?= Html::submitButton(Yii::t('AdminModule.user', 'Send & save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>
    <a href="<?= Url::to(['index']); ?>" class="btn btn-primary" data-ui-loader><?= Yii::t('AdminModule.user', 'Cancel'); ?></a>

    <?php CActiveForm::end(); ?>
</div>