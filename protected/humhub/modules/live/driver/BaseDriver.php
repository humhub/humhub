<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\driver;

use yii\base\Object;
use humhub\modules\live\components\LiveEvent;

/**
 * Base driver for live event storage and distribution
 *
 * @since 1.2
 * @author Luke
 */
abstract class BaseDriver extends Object
{

    /**
     * Sends a live event
     * 
     * @param LiveEvent $liveEvent The live event to send
     * @return boolean indicates the sent was successful
     */
    abstract public function send(LiveEvent $liveEvent);
}
