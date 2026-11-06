<?php

use humhub\libs\TimezoneHelper;
use humhub\modules\user\models\forms\AppearanceSettings;
use humhub\widgets\bootstrap\Badge;
use humhub\widgets\form\ActiveForm;

/* @var AppearanceSettings $model */

$languages = $model->getLanguages();
?>
<?php $this->beginContent('@user/views/appearance/_layout.php') ?>

<?php $form = ActiveForm::begin(['id' => 'appearance-settings-form', 'acknowledge' => true]); ?>

<?php if (Yii::$app->appearance->isDarkModeAllowed()) : ?>
    <?= $form->field($model, 'colorMode')
        ->dropDownList($model->getColorModes())
        ->label($model->getAttributeLabel('colorMode') . ' ' . Badge::light(Yii::t('UserModule.account', 'Experimental'))) ?>
<?php endif; ?>

<?php if (count($languages) > 1) : ?>
    <?= $form->field($model, 'language')->dropDownList($languages) ?>
<?php endif; ?>

<?= $form->field($model, 'timeZone')->dropDownList(TimezoneHelper::generateList(true)) ?>

<?= $form->field($model, 'markdownEditorMode')->dropDownList($model->getEditorModes()) ?>

<button class="btn btn-primary" type="submit" data-ui-loader><?= Yii::t('UserModule.account', 'Save') ?></button>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
