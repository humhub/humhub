<?php

return [ 
    // User 1 follows User 2
    ['id' => '1', 'object_model' => 'humhub\modules\user\models\User', 'object_id' => '2', 'user_id' => '1'],

    // Admin User follows Space 2
    ['id' => '2', 'object_model' => 'humhub\modules\space\models\Space', 'object_id' => '2', 'user_id' => '1', 'send_notifications' => '1'],
    
    ['id' => '3', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '1', 'user_id' => '1'],
    ['id' => '4', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '2', 'user_id' => '1'],
    ['id' => '5', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '3', 'user_id' => '2'],
    ['id' => '6', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '4', 'user_id' => '2'],
    ['id' => '7', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '5', 'user_id' => '3'],
    ['id' => '8', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '6', 'user_id' => '3'],
    ['id' => '9', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '7', 'user_id' => '1'],
    ['id' => '10', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '8', 'user_id' => '1'],
    ['id' => '11', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '9', 'user_id' => '3'],
    ['id' => '12', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '10', 'user_id' => '1'],
    ['id' => '13', 'object_model' => 'humhub\modules\post\models\Post', 'object_id' => '11', 'user_id' => '1'],
];
