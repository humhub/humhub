<?php

use humhub\components\View;
use humhub\helpers\Html;
use humhub\modules\comment\assets\CommentVueAsset;
use humhub\modules\comment\Module;
use humhub\modules\comment\widgets\CommentLink;
use humhub\widgets\bootstrap\Link;
use humhub\widgets\Icon;

/* @var $this View */
/* @var $content \humhub\modules\content\models\Content */
/* @var $id string unique object id */
/* @var $commentCount int */
/* @var $mode string */
/* @var $module Module */

$hasComments = ($commentCount > 0);
$commentCountSpan = Html::tag('span', ' (' . $commentCount . ')', [
    'class' => 'comment-count',
    'data-count' => $commentCount,
    'style' => ($hasComments) ? null : 'display:none',
]);

$label = Icon::get('message-circle')->asString() . Yii::t('CommentModule.base', "Comment");

?>
<?php if ($mode == CommentLink::MODE_POPUP): ?>
    <?php // The popup's island (see `comment.showComments`) mounts on this page, not in a response ?>
    <?php CommentVueAsset::register($this) ?>
    <?= Html::a($label . ' (' . $commentCount . ')', '#', [
        'data-action-click' => 'comment.showComments',
        'data-content-id' => $content->id,
        'data-modal-title' => Yii::t('CommentModule.base', 'Comments'),
    ]) ?>
<?php elseif (Yii::$app->user->isGuest): ?>
    <?= Html::a(
        $label . $commentCountSpan,
        Yii::$app->user->loginUrl,
        ['data-bs-target' => '#globalModal']
    ) ?>
<?php else : ?>
    <?= Link::to($label . $commentCountSpan)
        ->encodeLabel(false)
        ->action('comment.toggleComment', null, '#comment_' . $id) ?>
<?php endif; ?>
