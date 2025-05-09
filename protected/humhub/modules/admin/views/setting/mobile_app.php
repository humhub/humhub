<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\modules\admin\models\forms\MobileSettingsForm;
use humhub\modules\ui\form\widgets\ActiveForm;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\widgets\Button;
use humhub\widgets\ModalButton;
use yii\web\View;

/* @var $this View */
/* @var $model MobileSettingsForm */
?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(['acknowledge' => true]) ?>

<?= $form->errorSummary($model) ?>

<?= $form->field($model, 'enableLinkService')->checkbox() ?>

<?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Well-known files')) ?>
<div class="help-block"><?= Yii::t('AdminModule.settings', 'Allow establishing verified connections with the mobile app to enable Android app links and iOS universal links and redirect web content to the mobile app.') ?></div>
<?php if (!Yii::$app->urlManager->enablePrettyUrl) : ?>
    <div class="alert alert-warning">
        <?= Icon::get('warning') ?>
        <?= Yii::t('AdminModule.settings', 'Please enable <a href="{url}" target="_blank">Pretty URLs</a> for proper working of the well-known files.', [
            'url' => 'https://docs.humhub.org/docs/admin/installation/#pretty-urls',
        ]) ?>
    </div>
<?php endif; ?>

<?= $form->field($model, 'fileAssetLinks')->textarea(['rows' => 10]) ?>
<?= $form->field($model, 'fileAppleAssociation')->textarea(['rows' => 10]) ?>
<?= $form->endCollapsibleFields() ?>

<hr>

<?= Button::save()->submit() ?>
<?= ModalButton::defaultType(Yii::t('AdminModule.settings', 'Debug'))
    ->load(['mobile-app-debug'])
    ->icon('bug')
    ->right() ?>

<?php ActiveForm::end() ?>
<?php $this->endContent() ?>
