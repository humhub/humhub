<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\events;

use yii\base\Event;

/**
 * UserEvent
 *
 * @since 1.2
 * @author Luke
 */
class UserEvent extends Event
{

    /**
     * @var \humhub\modules\user\models\User the user
     */
    public $user;

}
