<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\components\DeviceConnection;
use humhub\modules\oauth\models\forms\DeviceTokenForm;
use humhub\modules\oauth\services\PersonalAccessTokenService;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;
use yii\helpers\Url;

/* @var View $this */
/* @var DeviceConnection $connection */
/* @var DeviceTokenForm $model */
/* @var bool $limitReached */

$createUrl = Url::to(['/oauth/connect/create', 'id' => $connection->id]);
$footer = ModalButton::cancel();
if (!$limitReached) {
    $footer .= ' ' . ModalButton::primary(Yii::t('OauthModule.base', 'Create password'))->submit($createUrl);
}

$fields = [];
foreach ($connection->fields as $label => $value) {
    $fields[] = ['id' => 'oauth-connect-field-' . count($fields), 'label' => $label, 'value' => $value, 'copy' => true];
}
?>
<?php $form = Modal::beginFormDialog([
    'title' => Html::encode($connection->title),
    'footer' => $footer,
    'form' => ['action' => $createUrl, 'sudo' => true],
]) ?>

<p><?= Html::encode($connection->description) ?></p>

<?= $this->render('@oauth/views/common/_credential', [
    'hint' => null,
    'fields' => $fields,
    'copied' => Yii::t('OauthModule.base', 'Copied to your clipboard.'),
    'doneUrl' => null,
]) ?>

<p class="text-body-secondary">
    <?= Yii::t('OauthModule.base', 'Your device signs in with a password of its own, never with the password of your account. You can revoke it at any time under {link}.', [
        'link' => Html::a(Yii::t('OauthModule.base', 'Devices & Access'), ['/user/security/sessions']),
    ]) ?>
</p>

<?php if ($limitReached): ?>
    <p class="text-danger"><?= Html::encode(PersonalAccessTokenService::getLimitMessage()) ?></p>
<?php else: ?>
    <?= $form->field($model, 'name')->textInput(['maxlength' => 100, 'autocomplete' => 'off']) ?>
<?php endif ?>

<?php Modal::endFormDialog() ?>
