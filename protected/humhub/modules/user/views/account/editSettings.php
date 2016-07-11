<?php

use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>

    <?php $form = ActiveForm::begin(['id' => 'basic-settings-form']); ?>

    <?php echo $form->field($model, 'tags'); ?>

    <?php if(count($languages) > 1) : ?>
        <?php echo $form->field($model, 'language')->dropdownList($languages); ?>
    <?php endif; ?>

    <?php echo $form->field($model, 'timeZone')->dropdownList(\humhub\libs\TimezoneHelper::generateList()); ?>

    <?php if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess')): ?>

        <?php
        echo $form->field($model, 'visibility')->dropdownList([
            1 => Yii::t('UserModule.views_account_editSettings', 'Registered users only'),
            2 => Yii::t('UserModule.views_account_editSettings', 'Visible for all (also unregistered users)'),
        ]);
        ?>


    <?php endif; ?>

    <?php if (Yii::$app->getModule('tour')->settings->get('enable') == 1) : ?>
        <?php echo $form->field($model, 'show_introduction_tour')->checkbox(); ?>
    <?php endif; ?>

    <hr>

    <?php echo CHtml::submitButton(Yii::t('UserModule.views_account_editSettings', 'Save'), array('class' => 'btn btn-primary', 'data-ui-loader' => '')); ?>

    <?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
