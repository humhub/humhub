
<?php

use humhub\modules\post\widgets\Form;
use humhub\modules\stream\widgets\StreamViewer;

$this->beginContent('@space/views/space/_sidebar.php', ['space' => $space]);

echo Form::widget(['contentContainer' => $space]);

$emptyMessage = '';
if ($canCreatePosts) {
    $emptyMessage = Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b><br>Start by posting something here...');
} elseif ($isMember) {
    $emptyMessage = Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b>');
} else {
    $emptyMessage = Yii::t('SpaceModule.views_space_index', '<b>You are not member of this space and there is no public content, yet!</b>');
}

echo StreamViewer::widget([
    'contentContainer' => $space,
    'streamAction' => '/space/space/stream',
    'messageStreamEmpty' => $emptyMessage,
    'messageStreamEmptyCss' => ($canCreatePosts) ? 'placeholder-empty-stream' : '',
]);

$this->endContent();
