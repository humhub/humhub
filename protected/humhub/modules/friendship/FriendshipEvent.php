<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship;

use yii\base\Event;

/**
 * FriendshipEvent
 * 
 * @since 1.2
 * @author Luke
 */
class FriendshipEvent extends Event
{

    /**
     * @var \humhub\modules\user\models\User first user
     */
    public $user1;

    /**
     * @var \humhub\modules\user\models\User second user
     */
    public $user2;

}
