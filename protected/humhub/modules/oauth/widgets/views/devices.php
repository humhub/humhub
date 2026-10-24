<?php

use humhub\helpers\Html;
use humhub\modules\oauth\models\Grant;
use humhub\modules\user\helpers\UserAgentHelper;

/* @var Grant[] $grants */
?>
<ul class="list-group mb-3">
    <?php foreach ($grants as $grant): ?>
        <?= $this->render('_grant-row', [
            'grant' => $grant,
            'icon' => UserAgentHelper::isMobile($grant->user_agent) ? 'device-mobile' : 'device-desktop',
            'title' => UserAgentHelper::describe($grant->user_agent),
            'titleHint' => $grant->user_agent,
            'details' => [
                Html::encode(Yii::t('OauthModule.base', 'Signed in {date}', ['date' => Yii::$app->formatter->asDate($grant->created_at)])),
            ],
            'actionLabel' => Yii::t('OauthModule.base', 'Sign out'),
            'confirmTitle' => Yii::t('OauthModule.base', 'Sign out this device?'),
            'confirm' => Yii::t('OauthModule.base', 'The HumHub app on this device is signed out and has to sign in again.'),
        ]) ?>
    <?php endforeach ?>
</ul>
