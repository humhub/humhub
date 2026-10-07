<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\events;

use humhub\components\ActiveRecord;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\user\components\ActiveQueryUser;
use humhub\modules\user\models\User;
use yii\base\ModelEvent;

/**
 * Triggered by {@see NotificationManager::dispatch()} before the dispatch is queued, with the
 * arguments of the call. A handler may change them or veto the dispatch with `isValid = false`.
 *
 * The arguments are properties rather than `$data`, because `Event::trigger()` overwrites `$data`
 * with the data a handler was attached with.
 *
 * @since 1.20
 */
class BeforeDispatchEvent extends ModelEvent
{
    /**
     * @var class-string<BaseNotification>
     */
    public string $class;

    /**
     * @var ActiveQueryUser|User|int|array<User|int>
     */
    public ActiveQueryUser|User|int|array $recipients = [];

    public ?ActiveRecord $source = null;

    public ?User $originator = null;

    /**
     * @var array the options of {@see NotificationManager::dispatch()}
     */
    public array $options = [];
}
