<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\components\DeviceConnection;
use humhub\modules\oauth\models\Grant;
use humhub\widgets\modal\Modal;
use humhub\widgets\modal\ModalButton;

/* @var View $this */
/* @var DeviceConnection $connection */
/* @var string $token */
/* @var Grant $grant */

$fields = [];
foreach ($connection->fields as $label => $value) {
    $fields[] = ['id' => 'oauth-connect-field-' . count($fields), 'label' => $label, 'value' => $value, 'copy' => true];
}
$fields[] = ['id' => 'oauth-connect-password', 'label' => Yii::t('OauthModule.base', 'Password'), 'value' => $token, 'copy' => true];

$expiry = $grant->expires_at === null
    ? Yii::t('OauthModule.base', 'It never expires.')
    : Yii::t('OauthModule.base', 'It expires on {date}.', ['date' => Yii::$app->formatter->asDate($grant->expires_at)]);
?>
<?php Modal::beginDialog([
    'title' => Html::encode($connection->title),
    'footer' => ModalButton::cancel(Yii::t('base', 'Close')),
]) ?>

<?= $this->render('@oauth/views/common/_credential', [
    'hint' => Yii::t('OauthModule.base', 'Copy the password now. You won\'t be able to see it again.'),
    'fields' => $fields,
    'copied' => Yii::t('OauthModule.base', 'Copied to your clipboard.'),
    'doneUrl' => null,
]) ?>

<p class="text-body-secondary">
    <?= Html::encode($expiry) ?>
    <?= Yii::t('OauthModule.base', 'It is listed as “{name}” under {link}.', [
        'name' => Html::encode($grant->name),
        'link' => Html::a(Yii::t('OauthModule.base', 'Devices & Access'), ['/user/security/sessions']),
    ]) ?>
</p>

<?php Modal::endDialog() ?>
