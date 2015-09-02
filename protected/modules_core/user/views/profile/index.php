<?php $this->widget('application.modules_core.post.widgets.PostFormWidget', array('contentContainer' => $this->getUser())); ?>
<?php

$this->widget('application.modules_core.wall.widgets.StreamWidget', array(
    'contentContainer' => $this->getUser(),
    'streamAction' => '//user/profile/stream',
    'messageStreamEmpty' => ($this->getUser()->canWrite()) ?
            Yii::t('UserModule.views_profile_index', '<b>Your profile stream is still empty</b><br>Get started and post something...') :
            Yii::t('UserModule.views_profile_index', '<b>This profile stream is still empty!</b>'),
    'messageStreamEmptyCss' => ($this->getUser()->canWrite()) ?
            'placeholder-empty-stream' :
            '',
));
?>
