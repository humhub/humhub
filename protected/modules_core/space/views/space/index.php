<?php

$this->widget('application.modules_core.post.widgets.PostFormWidget', array(
    'contentContainer' => $this->getSpace(),
));

$this->widget('application.modules_core.wall.widgets.StreamWidget', array(
    'contentContainer' => $this->getSpace(),
    'streamAction' => '//space/space/stream',
    'messageStreamEmpty' => ($this->getSpace()->canWrite()) ?
            Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b><br>Start by posting something here...') :
            Yii::t('SpaceModule.views_space_index', '<b>This space is still empty!</b>'),
    'messageStreamEmptyCss' => ($this->getSpace()->canWrite()) ?
            'placeholder-empty-stream' :
            '',
));
?>