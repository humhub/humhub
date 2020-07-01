<?php

use humhub\modules\comment\widgets\CommentLink;
use humhub\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this \humhub\modules\ui\view\components\View */
/* @var $objectModel string */
/* @var $objectId integer */
/* @var $id string unique object id */
/* @var $mode string */

$commentCount = $this->context->getCommentsCount();
$hasComments = ($commentCount > 0);
$commentCountSpan = Html::tag('span', ' (' . $commentCount . ')', [
    'class' => 'comment-count',
    'data-count' => $commentCount,
    'style' => ($hasComments) ? null : 'display:none'
]);
?>

<?php if ($mode == CommentLink::MODE_POPUP): ?>
    <?php $url = Url::to(['/comment/comment/show', 'objectModel' => $objectModel, 'objectId' => $objectId, 'mode' => 'popup']); ?>
    <a href="#" data-action-click="ui.modal.load" data-action-url="<?= $url ?>">
        <?= Yii::t('CommentModule.base', "Comment") . ' (' . $this->context->getCommentsCount() . ')' ?>
    </a>
<?php elseif (Yii::$app->user->isGuest): ?>
    <?= Html::a(Yii::t('CommentModule.base', "Comment") . $commentCountSpan, Yii::$app->user->loginUrl, ['data-target' => '#globalModal']) ?>
<?php else : ?>
    <?= Button::asLink(Yii::t('CommentModule.base', "Comment") . $commentCountSpan)->action('comment.toggleComment', null, '#comment_' . $id) ?>
<?php endif; ?>
