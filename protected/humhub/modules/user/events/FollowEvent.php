<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\events;

use yii\base\Event;

/**
 * FollowEvent
 *
 * @since 1.2
 * @author Luke
 */
class FollowEvent extends Event
{

    /**
     * @var \humhub\modules\user\models\User
     */
    public $user;

    /**
     * @var \humhub\components\ActiveRecord the followed item
     */
    public $target;

}
