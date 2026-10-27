<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\components\table;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\oauth\models\Client;
use Yii;

/**
 * The OAuth clients of the administration's client table ({@see AdminClientTable},
 * `GET /api/v2/admin/oauth-client`), narrowed by a search (`q`: name or client id) and the
 * `status` (`active`, `inactive`), in the order of a column (`sort`: `name`, `type`, `status`,
 * `createdAt`, each also descending with a leading `-`; without, the HumHub app first, then by
 * name).
 *
 * @since 1.20
 */
class AdminClientList extends FilterableList
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    private const SORT_COLUMNS = [
        'name' => ['c.name'],
        'type' => ['c.type'],
        'status' => ['c.is_active'],
        'createdAt' => ['c.created_at'],
    ];

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            new SearchFilter(
                'q',
                apply: static fn(QueryListBuilder $list, string $keywords) => $list->query()->andWhere([
                    'OR',
                    ['like', 'c.name', $keywords],
                    ['like', 'c.client_id', $keywords],
                ]),
                definition: [
                    'label' => Yii::t('OauthModule.base', 'Search'),
                    'placeholder' => Yii::t('OauthModule.base', 'Name or client ID'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'status',
                values: [
                    self::STATUS_ACTIVE => Yii::t('OauthModule.base', 'Active'),
                    self::STATUS_INACTIVE => Yii::t('OauthModule.base', 'Inactive'),
                ],
                apply: static fn(QueryListBuilder $list, string $status) => $list->query()->andWhere(['c.is_active' => $status === self::STATUS_ACTIVE ? 1 : 0]),
                definition: [
                    'label' => Yii::t('OauthModule.base', 'Status'),
                    'sortOrder' => 200,
                ],
            ),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function sorts(): array
    {
        // Accepted, not offered in a sort select: the table's column headers sort.
        $sorts = [];
        foreach (array_keys(self::SORT_COLUMNS) as $key) {
            $sorts[$key] = null;
            $sorts['-' . $key] = null;
        }

        return $sorts;
    }

    /**
     * @inheritdoc
     */
    protected function createBuilder(ListContext $context): ListBuilder
    {
        return new QueryListBuilder(Client::find()->alias('c'));
    }

    /**
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var QueryListBuilder $builder */
        $sort = $this->value($values, self::SORT_PARAM);
        if ($sort !== null && $this->applySortCallback($builder, $sort, $context)) {
            return;
        }

        $columns = self::SORT_COLUMNS[ltrim((string)$sort, '-')] ?? null;
        if ($columns === null) {
            $builder->query()->orderBy([
                'c.type' => SORT_DESC, // `system` before `admin`
                'c.name' => SORT_ASC,
                'c.id' => SORT_ASC,
            ]);
            return;
        }

        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'c.id');
    }
}
