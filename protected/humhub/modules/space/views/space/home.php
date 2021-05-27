<?php

/**
 * @var \humhub\modules\ui\view\components\View $this
 */

use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\post\widgets\Form;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\widgets\PendingApprovals;
use humhub\modules\space\widgets\Members;
use humhub\modules\space\widgets\Sidebar;
use humhub\modules\stream\widgets\StreamViewer;

/* @var $space Space */
/* @var $canCreatePosts bool */
/* @var $isMember bool */
/* @var $isSingleContentRequest bool */

$emptyMessage = '';
if ($canCreatePosts) {
    $emptyMessage = Yii::t('SpaceModule.base', '<b>This space is still empty!</b><br>Start by posting something here...');
} elseif ($isMember) {
    $emptyMessage = Yii::t('SpaceModule.base', '<b>This space is still empty!</b>');
} else {
    $emptyMessage = Yii::t('SpaceModule.base', '<b>You are not member of this space and there is no public content, yet!</b>');
}
?>

<div data-stream-create-content="stream.wall.WallStream"<?php if ($isSingleContentRequest) : ?> style="display:none"<?php endif; ?>>
    <?= Form::widget(['contentContainer' => $space]); ?>
</div>

<?= StreamViewer::widget([
    'contentContainer' => $space,
    'streamAction' => '/space/space/stream',
    'messageStreamEmpty' => $emptyMessage,
    'messageStreamEmptyCss' => ($canCreatePosts) ? 'placeholder-empty-stream' : '',
]); ?>

<?php
$this->beginBlock('sidebar');
$widgets = [
    [ActivityStreamViewer::class, ['contentContainer' => $space], ['sortOrder' => 10]],
    [PendingApprovals::class, ['space' => $space], ['sortOrder' => 20]]
];

if (!Yii::$app->getModule('space')->settings->contentContainer($space)->get('hideMembersSidebar')) {
    $widgets[] = [Members::class, ['space' => $space], ['sortOrder' => 30]];
}
?>
<?= Sidebar::widget(['space' => $space, 'widgets' => $widgets]); ?>
<?php $this->endBlock(); ?>
