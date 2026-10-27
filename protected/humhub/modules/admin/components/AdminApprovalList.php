<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\user\models\User;
use Yii;

/**
 * The registrations awaiting approval in the administration's table ({@see ApprovalTable},
 * `GET /api/v2/admin/approval`): the accounts of the status "need approval" the viewer may
 * administer, narrowed by a search (`q`: name, e-mail or username), in the order of a column
 * (`sort`: `name`, `createdAt`, each also descending with a leading `-`; without, the oldest
 * first).
 *
 * @since 1.20
 */
class AdminApprovalList extends FilterableList
{
    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = [
        'name' => ['profile.lastname', 'profile.firstname'],
        'createdAt' => ['user.created_at'],
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
                    ['like', 'user.username', $keywords],
                    ['like', 'user.email', $keywords],
                    ['like', 'profile.firstname', $keywords],
                    ['like', 'profile.lastname', $keywords],
                    ['like', 'concat(profile.firstname, " ", profile.lastname)', $keywords],
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Search'),
                    'placeholder' => Yii::t('AdminModule.user', 'Name or email'),
                    'sortOrder' => 100,
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
        return new QueryListBuilder(User::find()
            ->joinWith('profile')
            ->administrableBy(Yii::$app->user->getIdentity())
            ->andWhere(['user.status' => User::STATUS_NEED_APPROVAL]));
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
            $builder->query()->orderBy(['user.created_at' => SORT_ASC, 'user.id' => SORT_ASC]);
            return;
        }

        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'user.id');
    }
}
