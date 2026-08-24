<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\serializers;

use humhub\components\api\Format;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\SpaceSerializer;
use humhub\modules\user\serializers\UserSerializer;

/**
 * Serializes a notification for the HTTP API (see `docs/develop/concept-api.md`), consumed by
 * the notification islands (`notification/vue/`).
 *
 * ## The sentence comes from the server, the entry does not
 *
 * `html` is the notification's own sentence — `BaseNotification::html()`, e.g.
 * *"Jane commented on Post 'Release notes'"* — the one part of a notification a client cannot
 * build: it is localized, module-defined, and composed from records the client does not have.
 * Everything AROUND it (the originator's avatar, the space badge, the relative time, the
 * unread marker) used to come from `@notification/views/layouts/web.php` and is now rendered
 * client-side from the fields below.
 *
 * A notification class that implements no `html()` falls back to `text()` (the same
 * derivation `SocialActivity` itself uses: tags stripped, entities decoded), so an entry never
 * renders empty.
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
     *     html: string|null,
     *     url: string,
     *     isNew: bool,
     *     createdAt: string|null,
     *     groupKey: string|null,
     *     originator: array|null,
     *     space: array|null,
     * }
     */
    public static function notification(BaseNotification $notification): array
    {
        // One call, because it is the same preparation the legacy render path did: it decodes
        // the record's payload (which `html()` implementations read), resolves the entry URL
        // and the unread flag, and hands over the originator/space records.
        $params = $notification->getViewParams();
        $record = $notification->record;
        $space = $params['space'] ?? null;

        return [
            'id' => (int)$record->id,
            'html' => $params['html'] ?: ($params['text'] ?? null),
            // The `/notification/entry` redirect - the same target the legacy entry linked to,
            // relative so a click stays in the current origin.
            'url' => $params['relativeUrl'] ?? $params['url'],
            'isNew' => (bool)($params['isNew'] ?? false),
            'createdAt' => Format::dateTime($record->created_at),
            // Composite `<class>:<groupKey>`, byte-identical to what the live event carries as
            // `notificationGroup` (see `notification\targets\WebTarget::handle()`) and to the
            // former `data-notification-group` attribute - which is what lets a client dedupe
            // an arriving live event against an already listed entry.
            'groupKey' => self::groupKey($notification),
            'originator' => UserSerializer::short($params['originator'] ?? null),
            'space' => $space instanceof Space ? SpaceSerializer::short($space) : null,
        ];
    }

    private static function groupKey(BaseNotification $notification): ?string
    {
        $groupKey = $notification->getGroupKey();

        return $groupKey ? $notification::class . ':' . $groupKey : null;
    }
}
