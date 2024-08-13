<?php

/**
 * @var \humhub\modules\ui\view\components\View $this
 */

use humhub\modules\activity\widgets\ActivityStreamViewer;
use humhub\modules\content\widgets\WallCreateContentFormContainer;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\space\modules\manage\widgets\PendingApprovals;
use humhub\modules\space\widgets\Members;
use humhub\modules\space\widgets\Sidebar;
use humhub\modules\stream\widgets\StreamViewer;

/* @var $space Space */
/* @var $canCreateEntries bool */
/* @var $isMember bool */
/* @var $isSingleContentRequest bool */

if ($canCreateEntries) {
    $emptyMessage = Yii::t('SpaceModule.base', '<b>This space is still empty!</b><br>Start by posting something here...');
} elseif ($isMember) {
    $emptyMessage = Yii::t('SpaceModule.base', '<b>This space is still empty!</b>');
} else {
    $emptyMessage = Yii::t('SpaceModule.base', '<b>You are not member of this space and there is no public content, yet!</b>');
}

/** @var Module $module */
$module = Yii::$app->getModule('space');
?>

<?php if ($canCreateEntries && !$isSingleContentRequest) : ?>
    <div data-stream-create-content="stream.wall.WallStream">
        <?= WallCreateContentFormContainer::widget(['contentContainer' => $space]); ?>
    </div>
<?php endif; ?>

<?= StreamViewer::widget([
    'contentContainer' => $space,
    'streamAction' => '/space/space/stream',
    'messageStreamEmpty' => $emptyMessage,
    'messageStreamEmptyCss' => $canCreateEntries ? 'placeholder-empty-stream' : '',
]); ?>

<?php
$this->beginBlock('sidebar');
$widgets = [];

if (!$space->getAdvancedSettings()->hideActivities) {
    $widgets[] = [ActivityStreamViewer::class, ['contentContainer' => $space], ['sortOrder' => 10]];
}

$widgets[] = [PendingApprovals::class, ['space' => $space], ['sortOrder' => 20]];

if (!$space->getAdvancedSettings()->hideMembers) {
    $widgets[] = [Members::class, ['space' => $space], ['sortOrder' => 30]];
}
?>
<?= Sidebar::widget(['space' => $space, 'widgets' => $widgets]); ?>
<?php $this->endBlock(); ?>
