<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\events;

use humhub\components\Event;
use humhub\modules\user\models\User;

/**
 * UnreadCountChangedEvent is triggered whenever the number of unseen/unread notifications
 * of a user changes - for example after a notification (or conversation message) is marked
 * as seen or unread.
 *
 * Modules can listen to this event to keep an external representation of the unread count in
 * sync (e.g. the push notification badge count of a mobile or PWA app).
 *
 * @since 1.19
 */
class UnreadCountChangedEvent extends Event
{
    /**
     * @event triggered when the unread notification count of a user has changed.
     */
    public const EVENT_UNREAD_COUNT_CHANGED = 'unreadCountChanged';

    /**
     * @var User the user whose unread notification count has changed
     */
    public User $user;

    /**
     * Triggers the {@see EVENT_UNREAD_COUNT_CHANGED} event for the given user.
     *
     * @param User $user the user whose unread notification count has changed
     */
    public static function triggerChanged(User $user): void
    {
        Event::trigger(self::class, self::EVENT_UNREAD_COUNT_CHANGED, new self(['user' => $user]));
    }
}
