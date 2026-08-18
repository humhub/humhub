<?php

use humhub\helpers\Html;
use humhub\modules\like\assets\LikeVueAsset;
use humhub\widgets\VueComponent;

/* @var $recordId int */
/* @var $likeCount int */
/* @var $currentUserLiked bool */
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
        'props' => [
            'recordId' => $recordId,
            'likeCount' => $likeCount,
            'currentUserLiked' => $currentUserLiked,
        ],
    ]) ?>
<?php endif; ?>
