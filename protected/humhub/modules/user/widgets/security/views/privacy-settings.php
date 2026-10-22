<?php

use humhub\modules\user\models\forms\PrivacySettings;
use humhub\modules\user\widgets\UserPickerField;
use humhub\widgets\bootstrap\Button;
use humhub\widgets\form\ActiveForm;
use humhub\widgets\form\MultiSelect;

/* @var PrivacySettings $model */
?>
<?php $form = ActiveForm::begin(['action' => ['/user/security/privacy'], 'acknowledge' => true]) ?>

<?php if ($model->isVisibilityViewable()): ?>
    <?= $form->field($model, 'visibility')->dropDownList($model->getVisibilityOptions(), [
        'disabled' => !$model->isVisibilityEditable(),
    ]) ?>
<?php endif ?>

<?php if ($model->isOnlineStatusEnabled()): ?>
    <?= $form->field($model, 'hideOnlineStatus')->checkbox() ?>
<?php endif ?>

<?php if ($model->getAllowedDomains() !== []): ?>
    <?= $form->field($model, 'allowedExternalSources')->widget(MultiSelect::class, [
        'items' => array_combine($model->getAllowedDomains(), $model->getAllowedDomains()),
        'placeholderMore' => '',
        'options' => ['data-tags' => 'false'],
    ]) ?>
<?php endif ?>

<?php if ($model->isBlockUsersEnabled()): ?>
    <?= $form->field($model, 'blockedUsers')->widget(UserPickerField::class, ['minInput' => 2]) ?>
<?php endif ?>

<?= Button::save()->submit() ?>

<?php ActiveForm::end() ?>
