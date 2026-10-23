<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\oauth\models\Client;
use humhub\modules\oauth\models\Scope;
use humhub\widgets\SiteLogo;

/* @var $this View */
/* @var $client Client */
/* @var $scopes Scope[] */
/* @var $redirectHost string */
/* @var $requestId string */

$this->pageTitle = Yii::t('OauthModule.base', 'Authorize app');
$isApp = $client->type === Client::TYPE_SYSTEM;
?>

<div id="oauth-authorize" class="container container-login">
    <?= SiteLogo::widget(['place' => SiteLogo::PLACE_LOGIN]) ?>
    <br>

    <div class="panel panel-default mb-4">
        <div class="panel-heading">
            <strong class="fw-bolder">
                <?= $isApp
                    ? Yii::t('OauthModule.base', 'Sign in to the app on this device?')
                    : Yii::t('OauthModule.base', '{app} wants to access your account', ['app' => Html::encode($client->name)]) ?>
            </strong>
        </div>
        <div class="panel-body">
            <?php if ($isApp): ?>
                <p class="text-muted"><?= Html::encode(Yii::$app->name) ?></p>
            <?php else: ?>
                <p><?= Yii::t('OauthModule.base', 'It will be allowed to:') ?></p>
                <ul>
                    <?php foreach ($scopes as $scope): ?>
                        <li><?= Html::encode($scope->label) ?></li>
                    <?php endforeach; ?>
                </ul>
                <p class="text-muted">
                    <?= Yii::t('OauthModule.base', 'You will be redirected to {host}.', ['host' => Html::encode($redirectHost)]) ?>
                </p>
            <?php endif; ?>

            <?= Html::beginForm() ?>
                <?= Html::hiddenInput('request', $requestId) ?>
                <?= Html::submitButton(Yii::t('OauthModule.base', 'Allow'), [
                    'name' => 'decision', 'value' => 'allow', 'class' => 'btn btn-primary w-100',
                ]) ?>
                <?= Html::submitButton(Yii::t('OauthModule.base', 'Deny'), [
                    'name' => 'decision', 'value' => 'deny', 'class' => 'btn btn-light w-100 mt-2',
                ]) ?>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>
