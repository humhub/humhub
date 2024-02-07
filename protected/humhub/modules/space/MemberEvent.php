<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space;

use humhub\modules\user\models\User;
use yii\base\Event;

/**
 * MemberEvent
 *
 * @since 1.2
 * @author Luke
 */
class MemberEvent extends Event
{
    /**
     * @var models\Space
     */
    public $space;

    /**
     * @var User
     */
    public $user;

}
