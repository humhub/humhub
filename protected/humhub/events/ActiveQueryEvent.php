<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\events;

use yii\base\Event;
use yii\db\ActiveQuery;

/**
 * ActiveQueryEvent represents the parameter needed by [[ActiveQuery]] events.
 * 
 * @since 1.2.3
 * @author Luke
 */
class ActiveQueryEvent extends Event
{

    /**
     * @var ActiveQuery the active query
     */
    public $query;

}
