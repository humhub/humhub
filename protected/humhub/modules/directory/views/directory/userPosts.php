<?php

if (!Yii::$app->user->isGuest) {
    echo humhub\modules\post\widgets\Form::widget(['contentContainer' => Yii::$app->user->getIdentity()]);
}

echo humhub\modules\content\widgets\Stream::widget(array(
    'streamAction' => '//directory/directory/stream',
    'messageStreamEmpty' => (!Yii::$app->user->isGuest) ?
            Yii::t('DirectoryModule.views_directory_userPosts', '<b>Nobody wrote something yet.</b><br>Make the beginning and post something...') :
            Yii::t('DirectoryModule.views_directory_userPosts', '<b>There are no profile posts yet!</b>'),
    'messageStreamEmptyCss' => (!Yii::$app->user->isGuest) ?
            'placeholder-empty-stream' :
            '',
));
?>

