<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\serializers;

use humhub\components\api\Format;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\services\ActivityWindowService;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\SpaceSerializer;
use humhub\modules\user\serializers\UserSerializer;

/**
 * Serializes an activity for the HTTP API (see `docs/develop/concept-api.md`), consumed by the
 * `ActivityBox` island (`activity/vue/`).
 *
 * ## The sentence comes from the server, the entry does not
 *
 * `message` is the activity's own sentence — `BaseActivity::asWeb()`, e.g. *"Jane created a new
 * post in Product Team"* — the one part a client cannot build: it is localized, module-defined
 * and composed from records the client does not have, and for a grouped entry it names the
 * group ("Jane and 2 others"). Everything AROUND it (avatar, space badge, relative time, the
 * link) used to come from `@activity/views/layouts/web.php` and is rendered client-side from
 * the fields below.
 *
 * ## Grouping stays server-side
 *
 * Activities are grouped in the query (`ActiveQueryActivity::enableGrouping()`), and an entry
 * here is one such group: `id` is the activity representing it and `groupCount` how many
 * activities it stands for.
 *
 * `key` is what identifies an entry, not `id`: `id` names the activity currently representing
 * the group (its newest, see {@see \humhub\modules\activity\services\ActivityManager::load()})
 * and changes as the group grows. A client reconciles a freshly fetched page against what it
 * shows by `key`.
 *
 * Neither is durable, and a client must not treat them as such: when an activity joins a group
 * (or forms one), the server re-keys that group to the activity that did it, so the entry
 * appears under a new `key` — and, being keyed higher, at the top of the list. That is the
 * behaviour of the grouping itself; the payload only reports it.
 *
 * `key` is opaque on purpose: the column behind it is internal, and a client only ever compares
 * it or passes a cursor back.
 *
 * ## Caller context
 *
 * The payload carries nothing caller-specific — which activities a caller may see is decided by
 * the query, not by this shape. `space` travels with every entry; whether it is shown is the
 * island's decision (it is redundant inside a space).
 *
 * @since 1.20
 */
class ActivitySerializer
{
    /**
     * @return array{
     *     id: int,
     *     key: string,
     *     message: string,
     *     url: string|null,
     *     createdAt: string|null,
     *     groupCount: int,
     *     user: array|null,
     *     space: array|null,
     * }
     */
    public static function activity(BaseActivity $activity): array
    {
        $container = $activity->contentContainer?->polymorphicRelation;

        return [
            'id' => (int)$activity->record->id,
            'key' => ActivityWindowService::encodeCursor((int)$activity->record->grouping_key),
            'message' => $activity->asWeb(),
            'url' => $activity->getUrl(),
            'createdAt' => Format::dateTime($activity->createdAt),
            'groupCount' => $activity->groupCount,
            'user' => UserSerializer::short($activity->user),
            'space' => $container instanceof Space ? SpaceSerializer::short($container) : null,
        ];
    }
}
