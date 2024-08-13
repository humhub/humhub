<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
use humhub\commands\CronController;
use humhub\modules\queue\Events;
use humhub\modules\queue\Module;
use yii\queue\Queue;

return [
    'id' => 'queue',
    'class' => Module::class,
    'isCoreModule' => true,
    'events' => [
        [CronController::class, CronController::EVENT_ON_DAILY_RUN, [Events::class, 'onCronRun']],
        [Queue::class, Queue::EVENT_AFTER_ERROR, [Events::class, 'onQueueError']],
        [Queue::class, Queue::EVENT_BEFORE_PUSH, [Events::class, 'onQueueBeforePush']],
        [Queue::class, Queue::EVENT_AFTER_PUSH, [Events::class, 'onQueueAfterPush']]
    ],
];
