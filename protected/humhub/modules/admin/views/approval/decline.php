<?php

use yii\helpers\Url;
use yii\helpers\Html;
use humhub\compat\CActiveForm;
?>

<div class="panel-body">
    <h4><?php echo Yii::t('AdminModule.views_approval_approveUserDecline', 'Decline & delete user: <strong>{displayName}</strong>', array('{displayName}' => Html::encode($model->displayName))); ?></h4>

    <?php $form = CActiveForm::begin(); ?>

    <div class="form-group">
        <?php echo $form->labelEx($approveFormModel, 'subject'); ?>
        <?php echo $form->textField($approveFormModel, 'subject', array('class' => 'form-control')); ?>
        <?php echo $form->error($approveFormModel, 'subject'); ?>
    </div>

    <div class="form-group">
        <?php echo $form->labelEx($approveFormModel, 'message'); ?>
        <?php echo $form->textArea($approveFormModel, 'message', array('rows' => 6, 'cols' => 50, 'class' => 'form-control wysihtml5')); ?>
        <?php echo $form->error($approveFormModel, 'message'); ?>
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
    <hr/>
    <?php echo Html::submitButton(Yii::t('SpaceModule.approval_approveUserDecline', 'Send & decline'), array('class' => 'btn btn-danger', 'data-ui-loader' => "")); ?>
    <a href="<?php echo Url::to(['index']); ?>" class="btn btn-primary"><?php echo Yii::t('AdminModule.views_approval_approveUserDecline', 'Cancel'); ?></a>

    <?php CActiveForm::end(); ?>
</div>