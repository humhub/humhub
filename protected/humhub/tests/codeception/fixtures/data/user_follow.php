<?php

return array(
    // User 1 follows User 2
    array('id' => '1', 'object_model' => 'humhub\modules\user\models\User', 'object_id' => '2', 'user_id' => '1'),

    // User 1 follows Space 2
    array('id' => '2', 'object_model' => 'humhub\modules\space\models\Space', 'object_id' => '2', 'user_id' => '1'),
    
);
