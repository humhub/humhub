<?php

use humhub\modules\content\widgets\ContainerTagPicker;
use humhub\modules\user\models\forms\AccountSettings;
use humhub\widgets\form\ActiveForm;

/* @var AccountSettings $model */
?>

<?php $this->beginContent('@user/views/account/_userSettingsLayout.php') ?>

<?php $form = ActiveForm::begin(['id' => 'basic-settings-form', 'acknowledge' => true]); ?>

<?= $form->field($model, 'tags')->widget(ContainerTagPicker::class, ['minInput' => 2]); ?>

<?php if (Yii::$app->getModule('tour')->settings->get('enable') == 1): ?>
    <?= $form->field($model, 'show_introduction_tour')->checkbox(); ?>
<?php endif; ?>

<button class="btn btn-primary" type="submit" data-ui-loader><?= Yii::t('UserModule.account', 'Save') ?></button>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
