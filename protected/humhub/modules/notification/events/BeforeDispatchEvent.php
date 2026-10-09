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
 * Triggered by {@see BaseNotification::send()} before the dispatch is queued, with the
 * arguments of the call. A handler may change them or veto the dispatch with `isValid = false`:
 *
 * ```php
 * Event::on(NotificationManager::class, NotificationManager::EVENT_BEFORE_DISPATCH, function (BeforeDispatchEvent $event) {
 *     if ($event->class === NewLikeNotification::class) {
 *         $event->isValid = false;
 *     }
 * });
 * ```
 *
 * The arguments are properties rather than `$data`, because `Event::trigger()` overwrites `$data`
 * with the data a handler was attached with.
 *
 * @api
 * @since 1.20
 */
final class BeforeDispatchEvent extends ModelEvent
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
     * @var array data stored with the notification, see {@see BaseNotification::$payload}
     */
    public array $payload = [];

    /**
     * @var bool whether the originator receives the notification when among the recipients
     */
    public bool $notifyOriginator = false;

    /**
     * @var bool whether a recipient who already has the notification about the source by the
     * originator is skipped
     */
    public bool $dedupe = true;
}
