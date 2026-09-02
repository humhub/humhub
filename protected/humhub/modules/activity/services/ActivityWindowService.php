<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\services;

use humhub\modules\activity\components\ActiveQueryActivity;
use humhub\modules\activity\models\Activity;
use humhub\modules\activity\serializers\ActivitySerializer;
use humhub\modules\content\models\ContentContainer;
use Throwable;
use Yii;

/**
 * Builds one page of the activity list in the shape the API and the `ActivityBox` island
 * consume: `{results, nextCursor}`.
 *
 * Used by {@see \humhub\modules\activity\controllers\api\ActivityController} and by the widget
 * inlining a first page into its island props, so the first paint of a page carrying the
 * activity box costs no extra request.
 *
 * ## Paging
 *
 * Cursor-based, over the grouping key the list is ordered by
 * ({@see ActiveQueryActivity::defaultScopes()} orders by it, {@see
 * ActiveQueryActivity::enableGrouping()} groups by it). Two properties make it the only correct
 * cursor here:
 *
 * - it matches the sort exactly, so a page boundary can never fall inside a group;
 * - it is not the id of the entry that page ends with. An entry is represented by the NEWEST
 *   activity of its group ({@see ActivityManager::load()}), while the group sorts by the
 *   activity that formed it ({@see GroupingService::afterInsert()} keys a group by whichever
 *   activity pushed it over its grouping threshold) — filtering on the entry's own id would
 *   skip or repeat entries.
 *
 * The cursor is handed to clients as an opaque token and only ever read back here: the column
 * behind it is an internal detail of the grouping, not part of the API contract. An
 * unreadable token is treated as no cursor, the same leniency `limit` gets.
 *
 * ## Consistency handling
 *
 * An activity whose class or records no longer resolve (module uninstalled, group leader
 * deleted between query and load) is skipped rather than failing the whole page — the same
 * behaviour the server-rendered box had, which simply rendered nothing for it.
 *
 * @since 1.20
 */
class ActivityWindowService
{
    /**
     * @var int entries per page — including the page the widget inlines, which is sized to
     * fill the box (`max-height: 400px`) so the island's own paging does not start with a
     * request nobody scrolled for.
     */
    public const PAGE_SIZE = 10;

    /**
     * @var string prefix of the opaque cursor token, so a foreign or stale token is rejected
     * instead of silently paging from an arbitrary key
     */
    private const CURSOR_PREFIX = 'a1:';

    /**
     * @param string|null $cursor `nextCursor` of the previous page
     * @param ContentContainer|null $container the container to scope to, `null` for the
     * dashboard view over every container the user subscribes to
     *
     * @return array{results: array[], nextCursor: string|null}
     */
    public function window(int $limit, ?string $cursor = null, ?ContentContainer $container = null): array
    {
        $query = self::query($container);

        $groupingKey = self::decodeCursor($cursor);
        if ($groupingKey !== null) {
            $query->andWhere(['<', 'activity.grouping_key', $groupingKey]);
        }

        $records = $query->limit($limit)->all();

        return [
            'results' => $this->serialize($records),
            // A short page means there is nothing behind it. Derived from the records ASKED
            // for, not from the serialized ones: entries dropped as inconsistent must not end
            // paging early.
            'nextCursor' => count($records) < $limit
                ? null
                : self::encodeCursor((int)end($records)->grouping_key),
        ];
    }

    /**
     * The grouped, visibility-filtered activity query of the current user — container-scoped,
     * or over every container they subscribe to.
     */
    public static function query(?ContentContainer $container): ActiveQueryActivity
    {
        $user = Yii::$app->user->identity;

        $query = Activity::find()
            ->enableGrouping()
            ->defaultScopes($user);

        return $container === null
            ? $query->subscribedContentContainers($user)
            : $query->contentContainer($container, $user);
    }

    /**
     * @param Activity[] $records
     */
    private function serialize(array $records): array
    {
        $results = [];

        foreach ($records as $record) {
            try {
                $results[] = ActivitySerializer::activity(ActivityManager::load($record));
            } catch (Throwable $exception) {
                Yii::warning(
                    'Skipped unresolvable activity ' . $record->id . ': ' . $exception->getMessage(),
                    'activity',
                );
            }
        }

        return $results;
    }

    /**
     * The opaque form of a grouping key, used both as the paging cursor and as an entry's
     * `key` (see {@see \humhub\modules\activity\serializers\ActivitySerializer}): the column
     * behind it is an internal detail of the grouping, so it never travels as a number.
     */
    public static function encodeCursor(int $groupingKey): string
    {
        return rtrim(strtr(base64_encode(self::CURSOR_PREFIX . $groupingKey), '+/', '-_'), '=');
    }

    private static function decodeCursor(?string $cursor): ?int
    {
        if ($cursor === null || $cursor === '') {
            return null;
        }

        $decoded = base64_decode(strtr($cursor, '-_', '+/'), true);

        if ($decoded === false || !str_starts_with($decoded, self::CURSOR_PREFIX)) {
            return null;
        }

        $groupingKey = substr($decoded, strlen(self::CURSOR_PREFIX));

        return ctype_digit($groupingKey) ? (int)$groupingKey : null;
    }
}
