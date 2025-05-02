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
use yii\web\View;

/* @var $this View */
/* @var $model MobileSettingsForm */
?>
<?php $this->beginContent('@admin/views/setting/_advancedLayout.php') ?>

<?php $form = ActiveForm::begin(['acknowledge' => true]) ?>

<?= $form->errorSummary($model) ?>

<?= $form->field($model, 'enableLinkService')->checkbox()
    ->label(Yii::t('AdminModule.settings', 'Enable Link Redirection Service. In order for links to open in the app on mobile devices, rather than in the mobile browser, all links (e.g. notification emails) need to be routed through the HumHub proxy server. (Experimental Features // <a href="{url}">Privacy Policy</a>)', [
        'url' => 'https://www.humhub.com/en/privacy/',
    ])) ?>

<?= $form->beginCollapsibleFields(Yii::t('AdminModule.settings', 'Well-known files')) ?>
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

<?php ActiveForm::end() ?>
<?php $this->endContent() ?>
