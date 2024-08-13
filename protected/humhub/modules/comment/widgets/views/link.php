<?php

use humhub\modules\comment\widgets\CommentLink;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;
use \humhub\modules\comment\models\Comment;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $objectModel string */
/* @var $objectId integer */
/* @var $id string unique object id */
/* @var $commentCount integer */
/* @var $mode string */
/* @var $isNestedComment boolean */
/* @var $comment \humhub\modules\comment\models\Comment */
/* @var $module \humhub\modules\comment\Module */

$hasComments = ($commentCount > 0);
$commentCountSpan = Html::tag('span', ' (' . $commentCount . ')', [
    'class' => 'comment-count',
    'data-count' => $commentCount,
    'style' => ($hasComments) ? null : 'display:none'
]);

$label = ($isNestedComment) ? Yii::t('CommentModule.base', "Reply") : Yii::t('CommentModule.base', "Comment");

?>
<?php if ($mode == CommentLink::MODE_POPUP): ?>
    <?php $url = Url::to(['/comment/comment/show', 'objectModel' => $objectModel, 'objectId' => $objectId, 'mode' => 'popup']); ?>
    <a href="#" data-action-click="ui.modal.load" data-action-url="<?= $url ?>">
        <?= $label . ' (' . $commentCount . ')' ?>
    </a>
<?php elseif (Yii::$app->user->isGuest): ?>
    <?= Html::a(
        $label . $commentCountSpan,
        Yii::$app->user->loginUrl,
        ['data-target' => '#globalModal']) ?>
<?php else : ?>
    <?= Button::asLink($label . $commentCountSpan)
        ->action('comment.toggleComment', null, '#comment_' . $id) ?>
<?php endif; ?>

