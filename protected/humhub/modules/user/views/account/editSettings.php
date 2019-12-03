<?php

use humhub\libs\TimezoneHelper;
use humhub\modules\user\helpers\AuthHelper;
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

<?php if (AuthHelper::isGuestAccessEnabled()): ?>

    <?php
    echo $form->field($model, 'visibility')->dropDownList([
        1 => Yii::t('UserModule.views_account_editSettings', 'Private: Visible to registered users only'),
        2 => Yii::t('UserModule.views_account_editSettings', 'Public: Visible to all (including unregistered users)'),
    ]);
?>
    <p class='help-block'> <?= Yii::t('UserModule.settings', 'Public profiles are visible to everyone on the internet, without the need to log in.<br>Private profiles are removed from search results.'); ?></p><br/>



<?php endif; ?>

<?php if (Yii::$app->getModule('tour')->settings->get('enable') == 1) : ?>
    <?= $form->field($model, 'show_introduction_tour')->checkbox(); ?>
<?php endif; ?>

<button class="btn btn-primary" type="submit" data-ui-loader><?= Yii::t('UserModule.account', 'Save') ?></button>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
