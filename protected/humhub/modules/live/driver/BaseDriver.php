<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\driver;

use yii\base\Object;
use humhub\modules\live\components\LiveEvent;
use humhub\modules\user\models\User;

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

    /**
     * Returns the JavaScript Configuration for this driver
     * 
     * @since 1.3
     * @see \humhub\widgets\CoreJsConfig
     * @return array the JS Configuratoin
     */
    abstract public function getJsConfig();

    /**
     * This callback will be executed whenever the access rules for a 
     * contentcontainer is changed. e.g. user joined a new space as member.
     * 
     * @since 1.3
     * @see \humhub\modules\live\Module::getLegitimateContentContainerIds()
     */
    public function onContentContainerLegitimationChanged(User $user, $legitimation = [])
    {
        
    }

}
