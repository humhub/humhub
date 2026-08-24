<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\services;

use Exception;
use humhub\modules\notification\models\forms\FilterForm;
use humhub\modules\notification\models\Notification;
use humhub\modules\notification\serializers\NotificationSerializer;
use Yii;
use yii\db\IntegrityException;

/**
 * Builds one page of the current user's notification list in the shape the API and the
 * notification islands consume: `{results, unseenCount, nextCursor}`.
 *
 * Used by {@see \humhub\modules\notification\controllers\api\NotificationController} and by the
 * two widgets that inline a first page into their island props, so the first paint of a page
 * carrying the notification menu (or of the overview page) costs no extra request.
 *
 * ## Paging
 *
 * Cursor-based, over the `group_max_id` aggregate the grouped list is ordered by (see
 * {@see Notification::findGrouped()}): the list reorders as notifications arrive and are read,
 * which numbered pages cannot express without skipping or repeating entries.
 *
 * ## Consistency handling
 *
 * A notification whose base model no longer resolves (its source is gone, its class left with an
 * uninstalled module) is deleted and skipped rather than failing the whole page — the same
 * behaviour the former `ListController::actionIndex()` and `OverviewController` had while
 * rendering.
 *
 * @since 1.20
 */
class NotificationWindowService
{
    /**
     * @var int page size of the notification menu, the legacy dropdown's own
     */
    public const MENU_PAGE_SIZE = 6;

    /**
     * @var int page size of the overview page
     */
    public const OVERVIEW_PAGE_SIZE = 20;

    /**
     * @param int|null $cursor `group_max_id` of the last entry of the previous page
     * @param string[]|null $categories notification category ids, `null` for no category filter
     * @param string|null $seen `seen`, `unseen` or `null` for both
     *
     * @return array{results: array[], unseenCount: int, nextCursor: int|null}
     */
    public function window(
        int $limit,
        ?int $cursor = null,
        ?array $categories = null,
        ?string $seen = null,
    ): array {
        $query = $this->buildQuery($categories, $seen);

        if ($cursor) {
            // A HAVING, because the cursor compares the group's aggregate rather than a row.
            $query->andHaving(['<', 'group_max_id', $cursor]);
        }

        $records = $query->limit($limit)->all();

        return [
            'results' => $this->serialize($records),
            'unseenCount' => (int)Notification::findUnseen()->count(),
            // A short page means there is nothing behind it. Derived from the records ASKED
            // for, not from the serialized ones: entries dropped as inconsistent must not end
            // paging early.
            'nextCursor' => count($records) < $limit ? null : (int)end($records)->group_max_id,
        ];
    }

    /**
     * @param string[]|null $categories
     */
    private function buildQuery(?array $categories, ?string $seen)
    {
        $filter = new FilterForm();

        // `null` overrides the form's own default (every known category preselected). That
        // default is a UI convenience of the overview page's checkbox list, but as a query it
        // narrows the list to notification classes a module currently registers - which would
        // silently hide a stored notification of a disabled module.
        $filter->categoryFilter = $categories;

        // FilterForm treats every non-empty value that is not 'seen' as 'unseen'.
        $filter->seenFilter = in_array($seen, ['seen', 'unseen'], true) ? $seen : null;

        return $filter->createQuery();
    }

    /**
     * @param Notification[] $records
     */
    private function serialize(array $records): array
    {
        $results = [];

        foreach ($records as $record) {
            try {
                $baseModel = $record->getBaseModel();

                if (!$baseModel || !$baseModel->validate()) {
                    throw new IntegrityException('Invalid base model found for notification');
                }

                $results[] = NotificationSerializer::notification($baseModel);
            } catch (IntegrityException $exception) {
                $record->delete();
                Yii::warning(
                    'Deleted inconsistent notification with id ' . $record->id . '. ' . $exception->getMessage(),
                );
            } catch (Exception $exception) {
                Yii::error('Could not serialize notification: ' . $record->id . '(' . $exception . ')');
            }
        }

        return $results;
    }
}
