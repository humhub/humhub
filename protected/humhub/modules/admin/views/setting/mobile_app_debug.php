<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

use humhub\helpers\DeviceDetectorHelper;
use humhub\libs\Html;
use humhub\widgets\Button;
use humhub\widgets\Link;
use humhub\widgets\ModalDialog;
use yii\helpers\Json;
use yii\helpers\Url;

?>
<?php ModalDialog::begin([
    'header' => Yii::t('AdminModule.settings', '<strong>Mobile App</strong> Debug Page'),
    'size' => 'medium',
]) ?>
<div class="modal-body">

<?php if (DeviceDetectorHelper::isAppRequest()): ?>
    <div class="alert alert-success">
        <strong>App Detection</strong>: Current Request: Is App Request
        <br>
        <strong>FCM Detection</strong>: App is using <?= DeviceDetectorHelper::isAppWithCustomFcm() ? 'custom Firebase' : 'Proxy Firebase Service' ?>
    </div>

<?php else: ?>
    <div class="alert alert-warning">
        <strong>App Detection</strong>: Current Request: NO App Request Detected
    </div>
<?php endif; ?>

<?= Button::defaultType('Show Opener')
    ->cssClass('postFlutterMsgLink')
    ->options(['data-message' => Json::encode(['type' => 'showOpener'])])
    ->loader(false) ?>

<?= Button::defaultType('Hide Opener')
    ->cssClass('postFlutterMsgLink')
    ->options(['data-message' => Json::encode(['type' => 'hideOpener'])])
    ->loader(false) ?>

<?= Button::defaultType('Open native console')
    ->cssClass('postFlutterMsgLink')
    ->options(['data-message' => Json::encode(['type' => 'openNativeConsole'])])
    ->loader(false) ?>

<div class="panel panel-default" style="margin-top:15px">
    <div class="panel-body">
        <h4>Test Push Notification</h4>
        <p>
            Make sure the <code>Mobile</code> checkbox is enabled for
            <?= Link::asLink('Administrative Notifications!', ['/notification/user']) ?>.
            It may take a few minutes.
        </p>
        <?= Button::primary('Trigger "HumHub Update" notification')
            ->link(['mobile-app', 'triggerNotification' => 1])
            ->right() ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <h4>Test Update Notification Count</h4>
        <p>Set Notification Count to a number between 100 and 200.</p>
        <p><code><?= $message = Json::encode(['type' => 'updateNotificationCount', 'count' => rand(100, 200)]) ?></code></p>
        <?= Button::primary('Execute via JS Channel')
            ->cssClass('postFlutterMsgLink')
            ->options(['data-message' => $message])
            ->loader(false)
            ->right() ?>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <h4>Headers</h4>
        <pre><?php print_r($_SERVER) ?></pre>
    </div>
</div>

<div class="panel panel-default">
    <div class="panel-body">
        <h4>Send <code>registerFcmDevice</code> message </h4>
        <p><code><?= $message = Json::encode(['type' => 'registerFcmDevice', 'url' => Url::to(['/fcm-push/token/update-mobile-app'], true)]) ?></code></p>
        <p>The POST to given URL request must contain a `token` field in the payload.</p>
        <hr>
        <p>HTTP Return Codes for given URL:</p>
        <ul>
            <li>201 - Token saved</li>
            <li>200 - Token already saved</li>
            <li>404 - No valid Method POST Request</li>
            <li>400 - No `token` in payload</li>
        </ul>
        <?= Button::primary('Execute via JS Channel')
            ->cssClass('postFlutterMsgLink')
            ->options(['data-message' => $message])
            ->loader(false)
            ->right() ?>
    </div>
</div>
<div class="panel panel-default">
    <div class="panel-body">
        <h4>Send <code>unregisterFcmDevice</code> message </h4>
        <p><code><?= $message = Json::encode(['type' => 'unregisterFcmDevice', 'url' => Url::to(['/fcm-push/token/delete-mobile-app'], true)]) ?></code></p>
        <p>The POST to given URL request must contain a `token` field in the payload.</p>
        <hr>
        <p>HTTP Return Codes for given URL:</p>
        <ul>
            <li>201 - Token saved</li>
            <li>200 - Token already saved</li>
            <li>404 - No valid Method POST Request</li>
            <li>400 - No `token` in payload</li>
        </ul>
        <?= Button::primary('Execute via JS Channel')
            ->cssClass('postFlutterMsgLink')
            ->options(['data-message' => $message])
            ->loader(false)
            ->right() ?>
    </div>
</div>

<script <?= Html::nonce() ?>>
    $('.postFlutterMsgLink').on('click', function (evt) {
        var message = $(evt.target).data('message');
        if (window.flutterChannel) {
            try {
                window.flutterChannel.postMessage(JSON.stringify(message))
            } catch (err) {
                alert("Flutter Channel Error: " + err)
            }
            alert("Message sent! Message: " + JSON.stringify(message));
        } else {
            alert("Could not send message! Message: " + JSON.stringify(message));
        }
    });
</script>
</div>
<?php ModalDialog::end() ?>
