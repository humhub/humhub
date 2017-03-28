<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;
?>

<div class="panel-body">
    <h4><?= Yii::t('AdminModule.views_approval_approveUserAccept', 'Accept user: <strong>{displayName}</strong> ', ['{displayName}' => Html::encode($model->displayName)]); ?></h4>

    <?php $form = CActiveForm::begin(); ?>

    <?php //echo $form->errorSummary($approveFormModel);   ?>

    <div class="form-group">
        <?= $form->labelEx($approveFormModel, 'subject'); ?>
        <?= $form->textField($approveFormModel, 'subject', ['class' => 'form-control']); ?>
        <?= $form->error($approveFormModel, 'subject'); ?>
    </div>

    <div class="form-group">
        <?= $form->labelEx($approveFormModel, 'message'); ?>
        <?= $form->textArea($approveFormModel, 'message', ['rows' => 6, 'cols' => 50, 'class' => 'form-control wysihtml5']); ?>
        <?= $form->error($approveFormModel, 'message'); ?>
    </div>

    <script>
        $('.wysihtml5').wysihtml5({
            "font-styles": false, //Font styling, e.g. h1, h2, etc. Default true
            "emphasis": true, //Italics, bold, etc. Default true
            "lists": false, //(Un)ordered lists, e.g. Bullets, Numbers. Default true
            "html": false, //Button which allows you to edit the generated HTML. Default false
            "link": true, //Button to insert a link. Default true
            "image": false, //Button to insert an image. Default true,
            "color": false, //Button to change color of font
            "size": 'sm' //Button size like sm, xs etc.
        });
    </script>

    <hr>
    <?= Html::submitButton(Yii::t('AdminModule.views_approval_approveUserAccept', 'Send & save'), ['class' => 'btn btn-primary', 'data-ui-loader' => ""]); ?>
    <a href="<?= Url::to(['index']); ?>" class="btn btn-primary"><?= Yii::t('AdminModule.views_approval_approveUserAccept', 'Cancel'); ?></a>

    <?php CActiveForm::end(); ?>
</div>