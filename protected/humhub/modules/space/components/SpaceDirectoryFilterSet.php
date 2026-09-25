<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\components\filter\FilterSet;
use Yii;

/**
 * The filters of the space directory (`/spaces`). A filter's key is the parameter of
 * `GET /api/v2/space` ({@see SpaceListQuery}) its value travels in, so a module adding a filter
 * on {@see self::EVENT_INIT} reads it back on {@see SpaceListQuery::EVENT_INIT} under the same
 * name (e.g. `category`) — the successor of `SpaceDirectoryFilters::EVENT_INIT`.
 *
 * @since 1.20
 */
class SpaceDirectoryFilterSet extends FilterSet
{
    protected function initDefaultFilters(): void
    {
        $this->addFilter('q', [
            'type' => 'text',
            'label' => Yii::t('SpaceModule.base', 'Search'),
            'placeholder' => Yii::t('SpaceModule.base', 'Search Spaces...'),
            'sortOrder' => 100,
        ]);

        // No option for the default order: the placeholder is it, as "all" is for a select.
        $this->addFilter('sort', [
            'type' => 'select',
            'label' => Yii::t('SpaceModule.base', 'Sort'),
            'options' => [
                ['value' => SpaceListQuery::SORT_NAME, 'label' => Yii::t('SpaceModule.base', 'By Name')],
                ['value' => SpaceListQuery::SORT_NEWEST, 'label' => Yii::t('SpaceModule.base', 'Newest first')],
                ['value' => SpaceListQuery::SORT_OLDEST, 'label' => Yii::t('SpaceModule.base', 'Oldest first')],
            ],
            'sortOrder' => 200,
        ]);

        $this->addFilter('scope', [
            'type' => 'select',
            'label' => Yii::t('SpaceModule.base', 'Status'),
            'options' => [
                ['value' => SpaceListQuery::SCOPE_MEMBER, 'label' => Yii::t('SpaceModule.base', 'Member')],
                ['value' => SpaceListQuery::SCOPE_FOLLOWING, 'label' => Yii::t('SpaceModule.base', 'Following')],
                ['value' => SpaceListQuery::SCOPE_NONE, 'label' => Yii::t('SpaceModule.base', 'Neither..nor')],
                // Not a scope: the option sends `archived=1` to the list instead (option `params`).
                ['value' => 'archived', 'label' => Yii::t('SpaceModule.base', 'Archived'), 'params' => ['archived' => 1]],
            ],
            'sortOrder' => 300,
        ]);
    }
}
