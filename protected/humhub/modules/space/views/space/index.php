<?php echo \humhub\modules\post\widgets\Form::widget(['contentContainer' => $space]); ?>
<?php

echo \humhub\modules\content\widgets\Stream::widget(array(
    'contentContainer' => $space,
    'streamAction' => '/space/space/stream',
    'messageStreamEmpty' => ($space->canWrite()) ?
            Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b><br>Start by posting something here...') :
            Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b>'),
    'messageStreamEmptyCss' => ($space->canWrite()) ?
            'placeholder-empty-stream' :
            '',
));
?>