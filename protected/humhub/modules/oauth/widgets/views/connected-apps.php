<?php

use humhub\helpers\Html;
use humhub\modules\oauth\models\Grant;

/* @var Grant[] $grants */
/* @var array<int, string[]> $scopeLabels */
?>
<ul class="list-group mb-3">
    <?php foreach ($grants as $grant): ?>
        <?= $this->render('_grant-row', [
            'grant' => $grant,
            'icon' => 'plug-connected',
            'title' => $grant->client->name,
            'titleHint' => null,
            'details' => [
                ...array_map([Html::class, 'encode'], $scopeLabels[$grant->id] ?? []),
                Html::encode(Yii::t('OauthModule.base', 'Connected {date}', ['date' => Yii::$app->formatter->asDate($grant->created_at)])),
            ],
            'actionLabel' => Yii::t('OauthModule.base', 'Disconnect'),
            'confirmTitle' => Yii::t('OauthModule.base', 'Disconnect this app?'),
            'confirm' => Yii::t('OauthModule.base', 'The app loses access to your account. You can connect it again later.'),
        ]) ?>
    <?php endforeach ?>
</ul>
