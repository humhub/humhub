<?php

use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
use \humhub\models\Setting;
?>

<div class="panel-heading">
    <?php echo Yii::t('UserModule.views_account_editSettings', '<strong>User</strong> settings'); ?>
</div>
<div class="panel-body">
    <?php $form = ActiveForm::begin(['id' => 'basic-settings-form']); ?>

    <?php echo $form->field($model, 'tags'); ?>

    <?php echo $form->field($model, 'language')->dropdownList($languages); ?>

    <?php echo $form->field($model, 'timeZone')->dropdownList(\humhub\libs\TimezoneHelper::generateList()); ?>

    <?php if (Setting::Get('allowGuestAccess', 'authentication_internal')): ?>

        <?php
        echo $form->field($model, 'visibility')->dropdownList([
            1 => Yii::t('UserModule.views_account_editSettings', 'Registered users only'),
            2 => Yii::t('UserModule.views_account_editSettings', 'Visible for all (also unregistered users)'),
        ]);
        ?>


    <?php endif; ?>

    <?php if (Setting::Get('enable', 'tour') == 1) : ?>
        <?php echo $form->field($model, 'show_introduction_tour')->checkbox(); ?>
    <?php endif; ?>

    <?php if (Setting::Get('enable', 'share') == 1) : ?>
        <?php echo $form->field($model, 'show_share_panel')->checkbox(); ?>
    <?php endif; ?>
    <hr>

    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_editSettings', 'Save'), array('class' => 'btn btn-primary')); ?>

    <!-- show flash message after saving -->
    <?php echo \humhub\widgets\DataSaved::widget(); ?>

    <?php ActiveForm::end(); ?>
</div>
