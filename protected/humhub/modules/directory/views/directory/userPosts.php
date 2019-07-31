<?php

use humhub\modules\post\widgets\Form;
use humhub\modules\stream\widgets\StreamViewer;

if (!Yii::$app->user->isGuest) {
    echo Form::widget(['contentContainer' => Yii::$app->user->getIdentity()]);
}

echo StreamViewer::widget([
    'streamAction' => '//directory/directory/stream',
    'messageStreamEmpty' => (!Yii::$app->user->isGuest) ?
            Yii::t('DirectoryModule.base', '<b>Nobody has written anything yet.</b><br>Post to get things started...') :
            Yii::t('DirectoryModule.base', '<b>There are no profile posts yet!</b>'),
    'messageStreamEmptyCss' => (!Yii::$app->user->isGuest) ?
            'placeholder-empty-stream' :
            '',
]);
