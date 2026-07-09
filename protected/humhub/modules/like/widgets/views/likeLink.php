<?php

use humhub\helpers\Html;

/* @var $likeCount int */
/* @var $userListUrl string */
/* @var $likeUrl string */
/* @var $unlikeUrl string */
/* @var $currentUserLiked bool */
/* @var $id string */
/* @var $title string */

humhub\modules\like\assets\LikeAsset::register($this);
?>

<span class="likeLinkContainer" id="likeLinkContainer_<?= $id ?>">
    <?php if (Yii::$app->user->isGuest): ?>
        <?= Html::a(
            Yii::t('LikeModule.base', 'Like'),
            Yii::$app->user->loginUrl,
            ['data-bs-target' => '#globalModal']
        ); ?>
    <?php else: ?>
        <a href="#" data-action-click="like.toggleLike" data-action-url="<?= $likeUrl ?>"
           class="like likeAnchor<?= $currentUserLiked ? ' d-none' : '' ?>">
            <?= Yii::t('LikeModule.base', 'Like') ?>
        </a>
        <a href="#" data-action-click="like.toggleLike" data-action-url="<?= $unlikeUrl ?>"
           class="unlike likeAnchor<?= !$currentUserLiked ? ' d-none' : '' ?>">
            <?= Yii::t('LikeModule.base', 'Unlike') ?>
        </a>
    <?php endif; ?>

    <!-- Create link to show all users, who liked this -->
    <a href="<?= $userListUrl; ?>" data-bs-target="#globalModal">
        <?php if ($likeCount) : ?>
            <span class="likeCount tt" data-placement="top" data-bs-toggle="tooltip"
                  title="<?= Html::encode($title) ?>">(<?= $likeCount ?>)</span>
        <?php else: ?>
            <span class="likeCount"></span>
        <?php endif; ?>
    </a>
</span>
