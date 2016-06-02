<?php echo \humhub\modules\post\widgets\Form::widget(['contentContainer' => $space]); ?>
<?php

echo \humhub\modules\content\widgets\Stream::widget(array(
    'contentContainer' => $space,
    'streamAction' => '/space/space/stream',
    'messageStreamEmpty' => ($space->permissionManager->can(new \humhub\modules\post\permissions\CreatePost())) ?
            Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b><br>Start by posting something here...') :
            Yii::t('SpaceModule.views_space_index', '<b>You are not member of this space and there is no public content, yet!</b>'),
    'messageStreamEmptyCss' => ($space->permissionManager->can(new \humhub\modules\post\permissions\CreatePost())) ?
            'placeholder-empty-stream' :
            '',
));
?>