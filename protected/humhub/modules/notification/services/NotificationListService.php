<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use humhub\modules\content\models\Content;
use humhub\modules\notification\components\ActiveQueryNotification;
use humhub\modules\notification\components\BaseNotification;
use humhub\modules\notification\components\NotificationManager;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\serializers\NotificationSerializer;
use humhub\modules\user\models\User;
use Throwable;
use Yii;
use yii\db\IntegrityException;

/**
 * Builds one page of the current user's notification list in the shape the API and the
 * notification islands consume: `{results, unseenCount, nextCursor}`.
 *
 * Used by {@see \humhub\modules\notification\controllers\api\NotificationController} and by the
 * widgets that inline a first page into their island props, so the first paint of a page
 * carrying the notification menu (or of the overview page) costs no extra request.
 *
 * ## What is listed
 *
 * The user's listed notifications ({@see ActiveQueryNotification::listed()}), one entry per
 * group ({@see ActiveQueryNotification::grouped()}), newest group first. A notification about
 * a content that is not published (a draft, a scheduled or a deleted content) is left out.
 *
 * ## Paging
 *
 * Cursor-based, over the grouping key the list is ordered by: it matches the sort exactly, so a
 * page boundary never falls inside a group, and it does not move while a user pages. The cursor
 * travels as an opaque token ({@see encodeCursor()}) - the same token an entry carries as its
 * `groupKey` and the live event as its `notificationGroup`. An unreadable token is treated as no
 * cursor.
 *
 * ## Consistency handling
 *
 * An entry that no longer resolves is skipped rather than failing the whole page, and what made
 * it unresolvable is deleted: the whole group when its class left with an uninstalled module,
 * otherwise the member whose source record is gone. Any other error
 * while serializing an entry is logged and the entry skipped.
 *
 * @since 1.20
 */
class NotificationListService
{
    /**
     * @var int page size of the notification menu
     */
    public const MENU_PAGE_SIZE = 6;

    /**
     * @var int page size of the overview page
     */
    public const OVERVIEW_PAGE_SIZE = 20;

    /**
     * Version tag of the opaque cursor format, see {@see encodeCursor()}.
     */
    private const CURSOR_PREFIX = 'n1:';

    /**
     * @param string|null $cursor `nextCursor` of the previous page
     * @param string[]|null $groups ids of {@see \humhub\modules\notification\components\NotificationGroup}s
     * to include, `null` for no group filter; ids matching no group give an empty list
     * @param string|null $seen `seen`, `unseen` or `null` for both
     *
     * @return array{results: array[], unseenCount: int, nextCursor: string|null}
     */
    public function page(int $limit, ?string $cursor = null, ?array $groups = null, ?string $seen = null): array
    {
        $user = Yii::$app->user->getIdentity();

        $query = self::publishedOnly(Notification::find()->forUser($user)->listed()->grouped());

        $groupingKey = self::decodeCursor($cursor);
        if ($groupingKey !== null) {
            $query->andWhere(['<', 'notification.grouping_key', $groupingKey]);
        }

        if ($groups !== null) {
            $classes = $this->classesOfGroups($groups);
            $query->andWhere($classes === [] ? '0=1' : ['notification.class' => $classes]);
        }

        if ($seen === 'seen') {
            $query->seen();
        } elseif ($seen === 'unseen') {
            $query->unseen();
        }

        $records = $query->limit($limit)->all();

        return [
            'results' => $this->serialize($records),
            'unseenCount' => self::unseenCount($user),
            // A short page means there is nothing behind it. Derived from the records ASKED
            // for, not from the serialized ones: entries dropped as inconsistent must not end
            // paging early.
            'nextCursor' => count($records) < $limit
                ? null
                : self::encodeCursor((int)end($records)->grouping_key),
        ];
    }

    /**
     * The user's number of unseen entries in the web list: listed groups with an unseen member,
     * each counted once, without those about an unpublished content - the badge of the
     * notification menu.
     */
    public static function unseenCount(User $user): int
    {
        return (int)self::publishedOnly(Notification::find()->forUser($user)->listed()->unseen())
            ->select('COUNT(DISTINCT notification.grouping_key)')
            ->scalar();
    }

    /**
     * Leaves out the notifications about a content that is not published.
     */
    private static function publishedOnly(ActiveQueryNotification $query): ActiveQueryNotification
    {
        return $query
            ->leftJoin('content', 'content.id = notification.content_id')
            ->andWhere(['OR',
                ['content.id' => null],
                ['content.state' => Content::STATE_PUBLISHED],
            ]);
    }

    /**
     * The notification classes of the enabled modules belonging to one of the given groups.
     *
     * @param string[] $groups
     * @return string[]
     */
    private function classesOfGroups(array $groups): array
    {
        $classes = [];

        foreach (Yii::$app->notification->getNotifications() as $class) {
            try {
                if (in_array($class::group()->id, $groups, true)) {
                    $classes[] = $class;
                }
            } catch (Throwable $e) {
                Yii::warning('Could not determine the notification group of ' . $class . ': ' . $e->getMessage(), 'notification');
            }
        }

        return $classes;
    }

    /**
     * @param Notification[] $records
     */
    private function serialize(array $records): array
    {
        $results = [];

        foreach ($records as $record) {
            try {
                $results[] = NotificationSerializer::notification(NotificationManager::load($record));
            } catch (IntegrityException $e) {
                $this->deleteInconsistent($record, $e);
            } catch (Throwable $e) {
                Yii::error('Could not serialize notification #' . $record->id . ': ' . $e, 'notification');
            }
        }

        return $results;
    }

    /**
     * Deletes what made an entry unresolvable: the whole group when its class is unknown (all
     * members share it), otherwise only the member that failed to load - the group's newest,
     * which {@see NotificationManager::load()} represents it by -, so the other members show
     * up again with the next request. A head deleted concurrently is simply skipped.
     */
    private function deleteInconsistent(Notification $record, IntegrityException $e): void
    {
        if (!class_exists($record->class) || !is_subclass_of($record->class, BaseNotification::class)) {
            Notification::deleteAll(['user_id' => $record->user_id, 'grouping_key' => $record->grouping_key]);
            Yii::warning('Deleted the notifications of unknown class ' . $record->class . ' in group #' . $record->grouping_key . ': ' . $e->getMessage(), 'notification');
            return;
        }

        $head = Notification::findOne(['id' => $record->group_max_id ?? $record->id]);
        if ($head === null) {
            return;
        }

        $head->delete();
        Yii::warning('Deleted inconsistent notification #' . $head->id . ': ' . $e->getMessage(), 'notification');
    }

    /**
     * The opaque form of a grouping key, used as the paging cursor and as an entry's group key:
     * the column behind it is an internal detail of the grouping, so it never travels as a number.
     */
    public static function encodeCursor(int $groupingKey): string
    {
        return rtrim(strtr(base64_encode(self::CURSOR_PREFIX . $groupingKey), '+/', '-_'), '=');
    }

    /**
     * The grouping key of an opaque cursor; `null` for no or an unreadable cursor.
     */
    public static function decodeCursor(?string $cursor): ?int
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
