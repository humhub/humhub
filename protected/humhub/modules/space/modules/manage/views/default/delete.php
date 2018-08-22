<?php

use yii\bootstrap\ActiveForm;
use humhub\modules\space\modules\manage\widgets\DefaultMenu;
use humhub\widgets\Button;

?>

<div class="panel panel-default">
    <div class="panel-heading">
       <?= Yii::t('SpaceModule.views_settings', '<strong>Space</strong> settings'); ?>
    </div>

    <?= DefaultMenu::widget(['space' => $space]); ?>
    
    <div class="panel-body">
        <p><?= Yii::t('SpaceModule.views_admin_delete', 'Are you sure, that you want to delete this space? All published content will be removed!'); ?></p>
        <p><?= Yii::t('SpaceModule.views_admin_delete', 'Please provide your password to continue!'); ?></p>
        <br>

        <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'currentPassword')->passwordInput(); ?>

            <hr>

            <?= Button::danger(Yii::t('SpaceModule.views_admin_delete', 'Delete'))->submit() ?>

        <?php ActiveForm::end(); ?>
    </div>
</div>
