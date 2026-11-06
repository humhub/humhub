<?php

use humhub\modules\user\models\forms\AccessibilitySettings;
use humhub\widgets\form\ActiveForm;

/* @var AccessibilitySettings $model */
?>
<?php $this->beginContent('@user/views/appearance/_layout.php') ?>

<?php $form = ActiveForm::begin(['id' => 'accessibility-settings-form', 'acknowledge' => true]); ?>

<?= $form->field($model, 'contrast')->dropDownList($model->getOptions()) ?>
<?= $form->field($model, 'motion')->dropDownList($model->getOptions()) ?>
<?= $form->field($model, 'font')->checkbox() ?>

<button class="btn btn-primary" type="submit" data-ui-loader><?= Yii::t('UserModule.account', 'Save') ?></button>

<?php ActiveForm::end(); ?>
<?php $this->endContent(); ?>
