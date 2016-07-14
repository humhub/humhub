<?php echo \humhub\modules\post\widgets\Form::widget(['contentContainer' => $user]); ?>
<?php

echo \humhub\modules\content\widgets\Stream::widget(array(
    'contentContainer' => $user,
    'streamAction' => '//user/profile/stream',
    'messageStreamEmpty' => ($user->canWrite()) ?
            Yii::t('UserModule.views_profile_index', '<b>Your profile stream is still empty</b><br>Get started and post something...') :
            Yii::t('UserModule.views_profile_index', '<b>This profile stream is still empty!</b>'),
    'messageStreamEmptyCss' => ($user->canWrite()) ?
            'placeholder-empty-stream' :
            '',
));
?>
