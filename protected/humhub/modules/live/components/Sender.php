<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\components;

use Yii;
use yii\base\Component;

/**
 * Live Data Sender
 *
 * @since 1.2
 * @author Luke
 */
class Sender extends Component
{

    /**
     * @var \humhub\modules\live\driver\BaseDriver|array|string
     */
    public $driver = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->driver = Yii::createObject($this->driver);
    }

    /**
     * Sends a live event
     * 
     * @param LiveEvent $event the live event
     */
    public function send($event)
    {
        return $this->driver->send($event);
    }

}
