<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\BoolFilter;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use Yii;
use yii\db\Expression;

/**
 * The space search of the platform: which spaces a list shows a user, filtered and in which
 * order. `GET /api/v2/space` is built with it, and so is anything else that lists spaces (the
 * directory — whose filter definitions are {@see self::definitions()} —, the chooser, the meta
 * search, pickers):
 *
 * ```php
 * $query = (new SpaceList())->build(['q' => 'marketing', 'scope' => 'member'], ListContext::forCurrentUser(SpaceList::PURPOSE_DIRECTORY))->query();
 * ```
 *
 * Parameters: `q` (keywords over name, description and tags), `scope` (one of
 * {@see self::SCOPES}; `archived` is the directory's "Archived" status: only archived spaces),
 * `archived` (`1`: only archived spaces — the same as `scope=archived`, for API clients; with
 * another scope, its archived spaces; `0` with `scope=archived` is refused),
 * `sort` (one of {@see self::SORTS}; without it a scope other than `all`/`none` orders the way
 * the platform orders a user's spaces, everything else by `default`), `ids` / `exclude` (at
 * most 100 each) and `purpose` (one of {@see self::PURPOSES}).
 *
 * Visibility ({@see ActiveQuerySpace::visible()}) and blocked spaces
 * ({@see ActiveQuerySpace::filterBlockedSpaces()}) always apply — they are the list's base, not
 * filters: no filter can bring back a space the user may not see. Archived spaces are left out
 * unless asked for.
 *
 * A module adds one filter on {@see self::EVENT_INIT} (its parameter, validation, restriction
 * and the directory's definition) and restricts the list on {@see self::EVENT_BUILD}:
 *
 * ```php
 * ['class' => SpaceList::class, 'event' => SpaceList::EVENT_INIT, 'callback' => [Events::class, 'onSpaceListInit']],
 * ['class' => SpaceList::class, 'event' => SpaceList::EVENT_BUILD, 'callback' => [Events::class, 'onSpaceListBuild']],
 *
 * public static function onSpaceListInit(ListEvent $event)
 * {
 *     $event->list->addFilter(new EnumFilter('category',
 *         values: Category::labels(),
 *         apply: static fn(QueryListBuilder $list, string $id) => $list->query()->andWhere(['space.id' => Category::spaceIds($id)]),
 *         definition: ['label' => Yii::t('EnterpriseThemeModule.base', 'Category'), 'sortOrder' => 250],
 *     ));
 * }
 *
 * public static function onSpaceListBuild(ListEvent $event)
 * {
 *     if ($event->context->purpose === SpaceList::PURPOSE_DIRECTORY) {
 *         $event->builder->query()->andWhere(['not in', 'space.id', HiddenSpaces::ids()]);
 *     }
 * }
 * ```
 *
 * @since 1.20
 */
class SpaceList extends FilterableList
{
    public const SCOPE_ALL = 'all';
    public const SCOPE_MEMBER = 'member';
    public const SCOPE_FOLLOWING = 'following';
    /**
     * Memberships and followed spaces.
     */
    public const SCOPE_MINE = 'mine';
    /**
     * Neither a member nor a follower.
     */
    public const SCOPE_NONE = 'none';
    /**
     * Only archived spaces (otherwise left out) — the directory's "Archived" status.
     */
    public const SCOPE_ARCHIVED = 'archived';

    public const SCOPES = [self::SCOPE_ALL, self::SCOPE_MEMBER, self::SCOPE_FOLLOWING, self::SCOPE_MINE, self::SCOPE_NONE, self::SCOPE_ARCHIVED];

    /**
     * The spaces' sort order, then their name ({@see ActiveQuerySpace::defaultOrderBy()}).
     */
    public const SORT_DEFAULT = 'default';
    public const SORT_NAME = 'name';
    public const SORT_NEWEST = 'newest';
    public const SORT_OLDEST = 'oldest';

    public const SORTS = [self::SORT_DEFAULT, self::SORT_NAME, self::SORT_NEWEST, self::SORT_OLDEST];

    public const PURPOSE_DIRECTORY = 'directory';
    public const PURPOSE_PICKER = 'picker';
    public const PURPOSE_CHOOSER = 'chooser';

    public const PURPOSES = [self::PURPOSE_DIRECTORY, self::PURPOSE_PICKER, self::PURPOSE_CHOOSER];

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        return [
            new SearchFilter(
                'q',
                apply: static fn(QueryListBuilder $list, string $keywords) => $list->query()->search($keywords),
                definition: [
                    'label' => Yii::t('SpaceModule.base', 'Search'),
                    'placeholder' => Yii::t('SpaceModule.base', 'Search Spaces...'),
                    'sortOrder' => 100,
                ],
            ),
            // `all` and `mine` are accepted, not offered: the placeholder is "all", and "mine" is
            // the chooser's.
            new EnumFilter(
                'scope',
                values: [
                    self::SCOPE_ALL => null,
                    self::SCOPE_MEMBER => Yii::t('SpaceModule.base', 'Member'),
                    self::SCOPE_FOLLOWING => Yii::t('SpaceModule.base', 'Following'),
                    self::SCOPE_MINE => null,
                    self::SCOPE_NONE => Yii::t('SpaceModule.base', 'Neither..nor'),
                    self::SCOPE_ARCHIVED => Yii::t('SpaceModule.base', 'Archived'),
                ],
                apply: $this->applyScope(...),
                applyMap: [self::SCOPE_ARCHIVED => static fn(QueryListBuilder $list) => self::onlyArchived($list)],
                definition: [
                    'label' => Yii::t('SpaceModule.base', 'Status'),
                    'sortOrder' => 300,
                ],
            ),
            new BoolFilter('archived', apply: static function (QueryListBuilder $list, bool $archived) {
                if ($archived) {
                    self::onlyArchived($list);
                }
            }),
            new IdsFilter('ids', column: 'space.id'),
            new IdsFilter('exclude', column: 'space.id', exclude: true),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function sortLabel(): string
    {
        return Yii::t('SpaceModule.base', 'Sort');
    }

    /**
     * @inheritdoc
     */
    protected function sorts(): array
    {
        // No option for the default order: the select's label is it, as "all" is for a select.
        return [
            self::SORT_DEFAULT => null,
            self::SORT_NAME => Yii::t('SpaceModule.base', 'By Name'),
            self::SORT_NEWEST => Yii::t('SpaceModule.base', 'Newest first'),
            self::SORT_OLDEST => Yii::t('SpaceModule.base', 'Oldest first'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function purposes(): array
    {
        return self::PURPOSES;
    }

    /**
     * @inheritdoc
     * @return QueryListBuilder over an {@see ActiveQuerySpace}
     */
    public function build(array $params, ListContext $context): QueryListBuilder
    {
        /** @var QueryListBuilder $builder */
        $builder = parent::build($params, $context);

        return $builder;
    }

    /**
     * @inheritdoc
     */
    protected function createBuilder(ListContext $context): ListBuilder
    {
        /** @var ActiveQuerySpace $query */
        $query = Space::find();
        $query->visible($context->user)->filterBlockedSpaces($context->user);

        return new QueryListBuilder($query);
    }

    /**
     * Archived spaces are left out unless asked for; then the order.
     *
     * @throws ListValidationException for `scope=archived` with `archived=0`
     *
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var QueryListBuilder $builder */
        $scope = $this->value($values, 'scope') ?? self::SCOPE_ALL;
        $archived = $this->value($values, 'archived');

        if ($scope === self::SCOPE_ARCHIVED && $archived === false) {
            throw new ListValidationException(['archived' => [Yii::t('SpaceModule.base', 'Archived spaces cannot be left out of the archived scope.')]]);
        }

        if ($scope !== self::SCOPE_ARCHIVED && $archived !== true) {
            $builder->query()->andWhere(['!=', 'space.status', Space::STATUS_ARCHIVED]);
        }

        $sort = $this->value($values, self::SORT_PARAM);

        // A sort with its own callback - one a module added, also for a key of the list's own.
        if ($sort !== null && $this->applySortCallback($builder, $sort, $context)) {
            return;
        }

        $this->applyOrder($builder->query(), $sort, $scope, $context);
    }

    private static function onlyArchived(QueryListBuilder $list): void
    {
        $list->query()->andWhere(['space.status' => Space::STATUS_ARCHIVED]);
    }

    /**
     * Without a user every scope but `all` and `none` is empty.
     *
     * The membership is joined rather than tested with an EXISTS for the scopes that order by
     * it, because the same join carries what the order needs (`last_visit`, and whether there
     * is a membership at all).
     */
    private function applyScope(QueryListBuilder $list, string $scope, ListContext $context): void
    {
        if ($scope === self::SCOPE_ALL) {
            return;
        }

        $query = $list->query();

        if ($context->user === null) {
            if ($scope !== self::SCOPE_NONE) {
                $query->andWhere('0=1');
            }

            return;
        }

        $userId = $context->user->id;

        $isFollowing = [
            'exists',
            Follow::find()
                ->where(['user_follow.user_id' => $userId, 'user_follow.object_model' => Space::class])
                ->andWhere('user_follow.object_id = space.id'),
        ];

        if ($scope === self::SCOPE_NONE) {
            $query->andWhere(['not exists', Membership::find()
                ->where(['space_membership.user_id' => $userId, 'space_membership.status' => Membership::STATUS_MEMBER])
                ->andWhere('space_membership.space_id = space.id')]);
            $query->andWhere(['not', $isFollowing]);

            return;
        }

        $query->leftJoin(
            ['scope_membership' => Membership::tableName()],
            'scope_membership.space_id = space.id'
            . ' AND scope_membership.user_id = :scopeUser'
            . ' AND scope_membership.status = :scopeMemberStatus',
            [':scopeUser' => $userId, ':scopeMemberStatus' => Membership::STATUS_MEMBER],
        );

        $isMember = ['not', ['scope_membership.id' => null]];

        $query->andWhere(match ($scope) {
            self::SCOPE_MEMBER => $isMember,
            self::SCOPE_FOLLOWING => $isFollowing,
            default => ['or', $isMember, $isFollowing],
        });
    }

    /**
     * Every order ends on the id, so paging through equal names or dates is stable.
     *
     * @param ActiveQuerySpace $query
     */
    private function applyOrder($query, ?string $sort, string $scope, ListContext $context): void
    {
        if ($sort === null) {
            if (in_array($scope, [self::SCOPE_MEMBER, self::SCOPE_FOLLOWING, self::SCOPE_MINE], true) && $context->user !== null) {
                $this->applyScopeOrder($query);

                return;
            }
            $sort = self::SORT_DEFAULT;
        }

        match ($sort) {
            self::SORT_NAME => $query->orderBy(['space.name' => SORT_ASC]),
            self::SORT_NEWEST => $query->orderBy(['space.created_at' => SORT_DESC, 'space.id' => SORT_DESC]),
            self::SORT_OLDEST => $query->orderBy(['space.created_at' => SORT_ASC]),
            default => $query->defaultOrderBy(),
        };

        if ($sort !== self::SORT_NEWEST) {
            $query->addOrderBy(['space.id' => SORT_ASC]);
        }
    }

    /**
     * Memberships first, then followed spaces — the order the space menu has always had.
     *
     * @param ActiveQuerySpace $query
     */
    private function applyScopeOrder($query): void
    {
        $order = [new Expression('scope_membership.id IS NULL')];

        // Mirrors Membership::findByUser(): the setting decides whether a user's spaces are
        // ordered by their own sort order or by how recently they visited them.
        if (Yii::$app->getModule('space')->settings->get('spaceOrder') == 0) {
            $order['space.sort_order'] = SORT_ASC;
        } else {
            $order['scope_membership.last_visit'] = SORT_DESC;
        }
        $order['space.name'] = SORT_ASC;
        $order['space.id'] = SORT_ASC;

        $query->orderBy($order);
    }
}
