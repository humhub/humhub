<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\installer;

use yii\base\BaseObject;
use yii\db\Connection;

/**
 * Events provides callbacks to handle events.
 */
class Events extends BaseObject
{
    public static function onConnectionAfterOpen($event)
    {
        /* @var $connection Connection */
        $connection = $event->sender;

        if (in_array($connection->getDriverName(), ['mysql', 'mysqli'], true)) {
            $connection->pdo->exec('SET default_storage_engine = InnoDB');
        }
    }

}
