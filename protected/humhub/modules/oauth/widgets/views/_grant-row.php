<?php

use humhub\helpers\Html;
use humhub\modules\oauth\models\Grant;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\Icon;
use humhub\widgets\TimeAgo;

/* @var Grant $grant */
/* @var string $icon */
/* @var string $title */
/* @var string|null $titleHint */
/* @var string[] $details already encoded detail lines */
/* @var string $actionLabel */
/* @var string $confirmTitle */
/* @var string $confirm */
?>
<li class="list-group-item d-flex align-items-center gap-3" data-grant-id="<?= $grant->id ?>">
    <?= Icon::get($icon)->size(Icon::SIZE_LG) ?>
    <div class="flex-grow-1">
        <strong<?= empty($titleHint) ? '' : ' title="' . Html::encode($titleHint) . '"' ?>><?= Html::encode($title) ?></strong>
        <div class="text-body-secondary small">
            <?= implode(' · ', $details) ?>
            <?php if ($grant->last_used_at !== null): ?>
                · <?= Yii::t('OauthModule.base', 'Last used {time}', ['time' => TimeAgo::widget(['timestamp' => $grant->last_used_at])]) ?>
            <?php endif ?>
        </div>
    </div>
    <?= Link::danger($actionLabel)
        ->post(['/oauth/account/revoke', 'id' => $grant->id])
        ->confirm($confirmTitle, Html::encode($confirm))
        ->sm() ?>
</li>
