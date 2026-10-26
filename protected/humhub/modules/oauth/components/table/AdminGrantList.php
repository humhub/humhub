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
use humhub\modules\oauth\components\ScopeRegistry;
use humhub\modules\oauth\models\Grant;
use humhub\modules\oauth\models\Scope;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * The grants of all users in the administration's grant table ({@see AdminGrantTable},
 * `GET /api/v2/admin/oauth-grant`): connected apps, devices and personal access tokens, narrowed
 * by a search over the user (`q`: name, username or e-mail), the `client` (its id), a `scope`
 * (a grant with full access has every scope) and the `type` (`oauth`, `pat`), in the order of a column (`sort`: `user`, `type`, `lastUsed`, `expires`,
 * `createdAt`, each also descending with a leading `-`; without, the newest first).
 *
 * @since 1.20
 */
class AdminGrantList extends FilterableList
{
    private const SORT_COLUMNS = [
        'user' => ['profile.lastname', 'profile.firstname'],
        'type' => ['g.type'],
        'lastUsed' => ['g.last_used_at'],
        'expires' => ['g.expires_at'],
        'createdAt' => ['g.created_at'],
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
                    'label' => Yii::t('OauthModule.base', 'Search'),
                    'placeholder' => Yii::t('OauthModule.base', 'User (name, username or email)'),
                    'sortOrder' => 100,
                ],
            ),
            new EnumFilter(
                'client',
                values: array_map('strval', ArrayHelper::map(Client::find()->orderBy(['name' => SORT_ASC])->all(), 'id', 'name')),
                apply: static fn(QueryListBuilder $list, string $client) => $list->query()->andWhere(['g.client_id' => (int)$client]),
                definition: [
                    'label' => Yii::t('OauthModule.base', 'Client'),
                    'sortOrder' => 200,
                ],
            ),
            new EnumFilter(
                'scope',
                values: array_map(static fn(Scope $scope) => $scope->label, ScopeRegistry::getScopes()),
                apply: static fn(QueryListBuilder $list, string $scope) => $list->query()->andWhere(self::scopeCondition($scope)),
                definition: [
                    'label' => Yii::t('OauthModule.base', 'Scope'),
                    'sortOrder' => 250,
                ],
            ),
            new EnumFilter(
                'type',
                values: self::types(),
                apply: static fn(QueryListBuilder $list, string $type) => $list->query()->andWhere(['g.type' => $type]),
                definition: [
                    'label' => Yii::t('OauthModule.base', 'Type'),
                    'sortOrder' => 300,
                ],
            ),
        ];
    }

    /**
     * The grants allowed the scope: with it among their scopes (a JSON list), or with full access,
     * which includes it.
     */
    private static function scopeCondition(string $scope): array
    {
        $has = static fn(string $id) => ['like', 'g.scopes', '"' . $id . '"'];

        return $scope === ScopeRegistry::FULL ? $has($scope) : ['OR', $has($scope), $has(ScopeRegistry::FULL)];
    }

    /**
     * @return array<string, string> the grant types' titles by value
     */
    public static function types(): array
    {
        return [
            Grant::TYPE_OAUTH => Yii::t('OauthModule.base', 'Connected app'),
            Grant::TYPE_PAT => Yii::t('OauthModule.base', 'Personal access token'),
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
        return new QueryListBuilder(Grant::find()->alias('g')->joinWith(['user', 'user.profile', 'client']));
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
            $builder->query()->orderBy(['g.created_at' => SORT_DESC, 'g.id' => SORT_DESC]);
            return;
        }

        $builder->sortBy($columns, str_starts_with((string)$sort, '-'), 'g.id');
    }
}
