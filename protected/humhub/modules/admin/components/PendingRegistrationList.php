<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\user\models\Invite;
use Yii;

/**
 * The pending registrations of the administration's table ({@see PendingRegistrationTable},
 * `GET /api/v2/admin/invite`): the open invitations and sign-ups of the allowed sources,
 * narrowed by a search (`q`: e-mail or name) and the `source`, in the order of a column
 * (`sort`: `email`, `source`, `createdAt`, each also descending with a leading `-`; without, the
 * newest first).
 *
 * @since 1.20
 */
class PendingRegistrationList extends FilterableList
{
    /**
     * The sortable columns by sort key.
     */
    private const SORT_COLUMNS = [
        'email' => ['user_invite.email'],
        'source' => ['user_invite.source'],
        'createdAt' => ['user_invite.created_at'],
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
                    ['like', 'user_invite.email', $keywords],
                    ['like', 'user_invite.firstname', $keywords],
                    ['like', 'user_invite.lastname', $keywords],
                ]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Search'),
                    'placeholder' => Yii::t('AdminModule.user', 'Email or name'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'source',
                values: (new Invite())->getAllowedSources(),
                apply: static fn(QueryListBuilder $list, string $source) => $list->query()->andWhere(['user_invite.source' => $source]),
                definition: [
                    'label' => Yii::t('AdminModule.user', 'Source'),
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
        return new QueryListBuilder(Invite::find()->andWhere(Invite::filterSource()));
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
            $builder->query()->orderBy(['user_invite.created_at' => SORT_DESC, 'user_invite.id' => SORT_DESC]);
            return;
        }

        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'user_invite.id');
    }
}
