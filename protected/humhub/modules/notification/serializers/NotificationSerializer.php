<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\serializers;

use humhub\components\api\Format;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationPriority;
use humhub\modules\notification\services\NotificationListService;
use humhub\modules\space\serializers\SpaceSerializer;
use humhub\modules\user\serializers\UserSerializer;

/**
 * Serializes a notification for the HTTP API (see `docs/develop/concept-api.md`), consumed by
 * the notification islands (`notification/vue/`).
 *
 * ## The sentence comes from the server, the entry does not
 *
 * `html` is the notification's own sentence — {@see BaseNotification::asWeb()}, e.g.
 * *"Jane commented on Post 'Release notes'"* — the one part of a notification a client cannot
 * build: it is localized, module-defined and composed from records the client does not have,
 * and for a grouped entry it names the group ("Jane and 2 more"). Everything AROUND it (the
 * originator's avatar, the space badge, the relative time, the unread marker) is rendered
 * client-side from the fields below.
 *
 * ## Grouping stays server-side
 *
 * An entry is one group of notifications ({@see \humhub\modules\notification\components\ActiveQueryNotification::grouped()}),
 * represented by its newest member ({@see \humhub\modules\notification\components\NotificationManager::load()}):
 * `id` is that member, `count` the size of the group. `groupKey` identifies the entry - the
 * opaque grouping key, identical to the live event's `notificationGroup` (see
 * {@see \humhub\modules\notification\jobs\DispatchJob}), so a client can dedupe an arriving live
 * event against an already listed entry.
 *
 * ## Caller context
 *
 * Unlike a comment payload, a notification IS the reader's own record: `isNew` (the unread
 * marker) is part of it, and nothing here is cached.
 *
 * @since 1.20
 */
class NotificationSerializer
{
    /**
     * @return array{
     *     id: int,
     *     html: string,
     *     url: string,
     *     isNew: bool,
     *     createdAt: string|null,
     *     groupKey: string,
     *     count: int,
     *     priority: string,
     *     originator: array|null,
     *     space: array|null,
     * }
     */
    public static function notification(BaseNotification $notification): array
    {
        $record = $notification->record;
        $space = $notification->getSpace();

        return [
            'id' => (int)$record->id,
            'html' => $notification->asWeb(),
            // The `/notification/entry` redirect, which marks the group seen. Absolute like
            // every other URL of the API, so a token client can follow it too.
            'url' => $notification->getEntryUrl(),
            // A group is new while any of its members is unseen.
            'isNew' => $record->group_unseen !== null
                ? (bool)$record->group_unseen
                : $record->seen_at === null,
            'createdAt' => Format::dateTime($record->created_at),
            'groupKey' => NotificationListService::encodeCursor((int)$record->grouping_key),
            'count' => $notification->groupCount,
            'priority' => self::priority((int)$record->priority),
            'originator' => UserSerializer::short($notification->originator),
            'space' => $space !== null ? SpaceSerializer::short($space) : null,
        ];
    }

    private static function priority(int $value): string
    {
        return match (NotificationPriority::tryFrom($value)) {
            NotificationPriority::Low => 'low',
            NotificationPriority::High => 'high',
            default => 'normal',
        };
    }
}
