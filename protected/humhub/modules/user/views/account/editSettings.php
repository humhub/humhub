<?php

use humhub\libs\TimezoneHelper;
use yii\widgets\ActiveForm;
use \humhub\compat\CHtml;
?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>

<?php $form = ActiveForm::begin(['id' => 'basic-settings-form']); ?>

<?= $form->field($model, 'tags'); ?>

<?php if (count($languages) > 1) : ?>
    <?= $form->field($model, 'language')->dropDownList($languages, ['data-ui-select2' => '']); ?>
<?php endif; ?>

<?= $form->field($model, 'timeZone')->dropDownList(TimezoneHelper::generateList(), ['data-ui-select2' => '']); ?>

<?php if (Yii::$app->getModule('user')->settings->get('auth.allowGuestAccess')): ?>

    <?php
    echo $form->field($model, 'visibility')->dropDownList([
        1 => Yii::t('UserModule.views_account_editSettings', 'Registered users only'),
        2 => Yii::t('UserModule.views_account_editSettings', 'Visible for all (also unregistered users)'),
    ]);
    ?>


<?php endif; ?>

<?php if (Yii::$app->getModule('tour')->settings->get('enable') == 1) : ?>
    <?= $form->field($model, 'show_introduction_tour')->checkbox(); ?>
<?php endif; ?>

<button class="btn btn-primary" type="submit" data-ui-loader><?= Yii::t('UserModule.views_account_editSettings', 'Save') ?></button>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
