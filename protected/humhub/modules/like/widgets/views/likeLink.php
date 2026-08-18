<?php

use humhub\helpers\Html;
use humhub\modules\like\assets\LikeVueAsset;
use humhub\modules\ui\vue\widgets\VueComponent;

/* @var $likeCount int */
/* @var $userListUrl string */
/* @var $likeUrl string */
/* @var $unlikeUrl string */
/* @var $currentUserLiked bool */
/* @var $id string */
/* @var $title string */
?>

<?php if (Yii::$app->user->isGuest): ?>
    <span class="likeLinkContainer">
        <?= Html::a(
            Yii::t('LikeModule.base', 'Like'),
            Yii::$app->user->loginUrl,
            ['data-bs-target' => '#globalModal']
        ); ?>
    </span>
<?php else: ?>
    <?= VueComponent::widget([
        'name' => 'LikeButton',
        'assetBundle' => LikeVueAsset::class,
        'options' => ['id' => 'likeLinkContainer_' . $id],
        'props' => [
            'likeUrl' => $likeUrl,
            'unlikeUrl' => $unlikeUrl,
            'userListUrl' => $userListUrl,
            'likeCount' => $likeCount,
            'currentUserLiked' => $currentUserLiked,
            'title' => $title,
        ],
    ]) ?>
<?php endif; ?>
