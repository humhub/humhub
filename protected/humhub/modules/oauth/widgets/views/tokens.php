<?php

use humhub\helpers\Html;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\services\PersonalAccessTokenService;

/* @var Grant[] $grants */
/* @var array<int, string[]> $scopeLabels */
/* @var bool $limitReached */
?>
<?php if ($grants !== []): ?>
    <ul class="list-group mb-3">
        <?php foreach ($grants as $grant): ?>
            <?php
            if ($grant->isExpired()) {
                $expiry = Yii::t('OauthModule.base', 'Expired');
            } elseif ($grant->expires_at === null) {
                $expiry = Yii::t('OauthModule.base', 'Never expires');
            } else {
                $expiry = Yii::t('OauthModule.base', 'Expires {date}', ['date' => Yii::$app->formatter->asDate($grant->expires_at)]);
            }
            ?>
            <?= $this->render('_grant-row', [
                'grant' => $grant,
                'icon' => 'key',
                'title' => $grant->name,
                'titleHint' => null,
                'details' => [
                    Html::tag('span', Html::encode(PersonalAccessTokenService::PREFIX . '…' . $grant->token_hint), ['class' => 'font-monospace']),
                    ...array_map([Html::class, 'encode'], $scopeLabels[$grant->id] ?? []),
                    $grant->isExpired() ? Html::tag('span', Html::encode($expiry), ['class' => 'text-danger']) : Html::encode($expiry),
                ],
                'actionLabel' => Yii::t('OauthModule.base', 'Revoke'),
                'confirmTitle' => Yii::t('OauthModule.base', 'Revoke this token?'),
                'confirm' => Yii::t('OauthModule.base', 'Scripts and agents using this token lose access to your account.'),
            ]) ?>
        <?php endforeach ?>
    </ul>
<?php endif ?>

<?php if ($limitReached): ?>
    <p class="text-body-secondary"><?= Html::encode(PersonalAccessTokenService::getLimitMessage()) ?></p>
<?php endif ?>
