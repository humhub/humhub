<?php echo \humhub\modules\post\widgets\Form::widget(['contentContainer' => $space]); ?>
<?php

$emptyMessage = '';
if ($canCreatePosts) {
    $emptyMessage = Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b><br>Start by posting something here...');
} elseif ($isMember) {
    $emptyMessage = Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b>');
} else {
    $emptyMessage = Yii::t('SpaceModule.views_space_index', '<b>You are not member of this space and there is no public content, yet!</b>');
}

echo \humhub\modules\content\widgets\Stream::widget([
    'contentContainer' => $space,
    'streamAction' => '/space/space/stream',
    'messageStreamEmpty' => $emptyMessage,
    'messageStreamEmptyCss' => ($canCreatePosts) ? 'placeholder-empty-stream' : '',
]);
?>