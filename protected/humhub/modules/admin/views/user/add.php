<?php

use yii\helpers\Url;
?>
<div class="panel panel-default">
    <div class="panel-heading"><?php echo Yii::t('AdminModule.views_user_add', '<strong>Add</strong> user'); ?></div>
    <div class="panel-body">
        <?= \humhub\modules\admin\widgets\UserMenu::widget(); ?>
        <p />

        <?php $form = \yii\widgets\ActiveForm::begin(); ?>
        <?php echo $hForm->render($form); ?>
        <?php \yii\widgets\ActiveForm::end(); ?>

    </div>
</div>