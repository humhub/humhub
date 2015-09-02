<?php

if (!Yii::app()->user->isGuest) {
    $this->widget('application.modules_core.post.widgets.PostFormWidget', array(
        'contentContainer' => Yii::app()->user->model
    ));
}

$this->widget('application.modules_core.wall.widgets.StreamWidget', array(
    'streamAction' => '//directory/directory/stream',
    'messageStreamEmpty' => (!Yii::app()->user->isGuest) ?
            Yii::t('DirectoryModule.views_directory_userPosts', '<b>Nobody wrote something yet.</b><br>Make the beginning and post something...') :
            Yii::t('DirectoryModule.views_directory_userPosts', '<b>There are no profile posts yet!</b>'),
    'messageStreamEmptyCss' => (!Yii::app()->user->isGuest) ?
            'placeholder-empty-stream' :
            '',    
));
?>

