<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\live\driver;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use humhub\modules\live\driver\BaseDriver;
use humhub\modules\live\components\LiveEvent;
use humhub\modules\live\models\Live;

/**
 * Database driver for live events
 *
 * @since 1.2
 * @author Luke
 */
class Poll extends BaseDriver
{

    /**
     * Defines the minimum polling interval in seconds if the default polling client is active.
     */
    public $minPollInterval = 15;

    /**
     * Defines the maximum polling interval in seconds if the default polling client is active.
     */
    public $maxPollInterval = 45;

    /**
     * Factor used in the actual interval calculation in case of user idle.
     */
    public $idleFactor = 0.1;

    /**
     * Interval for updating the update delay in case of user idle in seconds.
     */
    public $idleInterval = 20;

    /**
     * @var int seconds to delete old live events
     */
    public $maxLiveEventAge = 600;

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

    /**
     * @inheritdoc
     */
    public function getJsConfig()
    {
        return [
            'type' => 'humhub.modules.live.poll.PollClient',
            'options' => [
                'url' => Url::to(['/live/poll']),
                'initTime' => time(),
                'minInterval' => $this->minPollInterval, // Minimal polling request interval in seconds.
                'maxInterval' => $this->maxPollInterval, // Maximal polling request interval in seconds.
                'idleFactor' => $this->idleFactor, // Factor used in the actual interval calculation in case of user idle.
                'idleInterval' => $this->idleInterval //  Interval for updating the update delay in case of user idle in seconds.
            ]
        ];
    }

}
