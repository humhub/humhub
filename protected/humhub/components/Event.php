<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components;

/**
 * Event is the base class for all event classes.
 *
 * @since 1.2.3
 * @author Luke
 */
class Event extends \yii\base\Event
{

    /**
     * @var mixed an optional result which can be manipulated by the event handler.
     * Note that this varies according to which event is currently executing.
     */
    public $result;

}
