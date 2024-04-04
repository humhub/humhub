<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship;

use humhub\modules\user\models\User;
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
     * @var User first user
     */
    public $user1;

    /**
     * @var User second user
     */
    public $user2;

}
