<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\driver;

use humhub\modules\live\driver\BaseDriver;
use humhub\modules\live\components\LiveEvent;
use humhub\modules\live\models\Live;

/**
 * Database driver for live events
 *
 * @since 1.2
 * @author Luke
 */
class Database extends BaseDriver
{

    /**
     * @inheritdoc
     */
    public function send(LiveEvent $liveEvent)
    {
        $model = new Live();
        $model->serialized_data = serialize($liveEvent);
        $model->created_at = time();
        $model->visibility = $liveEvent->visibility;
        $model->contentcontainer_id = $liveEvent->contentContainerId;
        $model->created_at = time();
        return $model->save();
    }

}
