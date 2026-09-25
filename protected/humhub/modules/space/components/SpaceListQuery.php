<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\components;

use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Component;
use yii\base\InvalidArgumentException;
use yii\db\Expression;

/**
 * The space search of the platform: which spaces a list shows a user, filtered and in which
 * order. `GET /api/v2/space` is built with it, and so is anything else that lists spaces (the
 * directory, the chooser, pickers).
 *
 * Configure it with the named filters, then take the query:
 *
 * ```php
 * $query = (new SpaceListQuery($user))->search('marketing')->scope('member')->sort('name')->getQuery();
 * // or, from a parameter map (fires EVENT_INIT):
 * $query = (new SpaceListQuery($user))->build(['q' => 'marketing', 'scope' => 'member', 'purpose' => 'directory']);
 * ```
 *
 * Visibility ({@see ActiveQuerySpace::visible()}) and blocked spaces
 * ({@see ActiveQuerySpace::filterBlockedSpaces()}) always apply: no filter can bring back a
 * space the user may not see. Archived spaces are left out unless asked for.
 *
 * Modules add filters and restrictions on {@see self::EVENT_INIT}, fired by {@see self::build()}
 * with a {@see SpaceListQueryEvent} — the query, every parameter (also those the core does not
 * know, e.g. a `category`), the `purpose` and the user:
 *
 * ```php
 * ['class' => SpaceListQuery::class, 'event' => SpaceListQuery::EVENT_INIT, 'callback' => [Events::class, 'onSpaceListQueryInit']],
 *
 * public static function onSpaceListQueryInit(SpaceListQueryEvent $event)
 * {
 *     if ($event->purpose === SpaceListQuery::PURPOSE_DIRECTORY) {
 *         $event->query->andWhere(...);
 *     }
 * }
 * ```
 *
 * @since 1.20
 */
class SpaceListQuery extends Component
{
    /**
     * @event SpaceListQueryEvent triggered by {@see self::build()} once the query carries the
     *        core's filters and order
     */
    public const EVENT_INIT = 'init';

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

    public const SCOPES = [self::SCOPE_ALL, self::SCOPE_MEMBER, self::SCOPE_FOLLOWING, self::SCOPE_MINE, self::SCOPE_NONE];

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

    private ?User $user;

    private string $keywords = '';

    private string $scope = self::SCOPE_ALL;

    private bool $archived = false;

    private ?string $sort = null;

    /**
     * @var int[]
     */
    private array $ids = [];

    /**
     * @var int[]
     */
    private array $exclude = [];

    private ?string $purpose = null;

    /**
     * @param User|null $user whose list it is; `null` = the current user (a guest when nobody
     *        is logged in)
     */
    public function __construct(?User $user = null, array $config = [])
    {
        $this->user = $user ?? (Yii::$app->user->isGuest ? null : Yii::$app->user->getIdentity());

        parent::__construct($config);
    }

    /**
     * Keywords over name, description and tags ({@see ActiveQuerySpace::search()}); empty = no
     * restriction.
     */
    public function search(string $keywords): static
    {
        $this->keywords = trim($keywords);

        return $this;
    }

    /**
     * One of {@see self::SCOPES}. Without a user every scope but `all` and `none` is empty.
     *
     * @throws InvalidArgumentException for an unknown scope
     */
    public function scope(string $scope): static
    {
        if (!in_array($scope, self::SCOPES, true)) {
            throw new InvalidArgumentException('Unknown scope "' . $scope . '".');
        }
        $this->scope = $scope;

        return $this;
    }

    /**
     * `true`: only archived spaces; `false` (the default): no archived ones.
     */
    public function archived(bool $archived): static
    {
        $this->archived = $archived;

        return $this;
    }

    /**
     * One of {@see self::SORTS}, or `null` for the list's natural order: a scope other than
     * `all`/`none` orders the way the platform orders a user's spaces (memberships first, as the
     * `spaceOrder` setting asks, followed spaces after them), everything else by `default`.
     *
     * @throws InvalidArgumentException for an unknown order
     */
    public function sort(?string $sort): static
    {
        if ($sort !== null && !in_array($sort, self::SORTS, true)) {
            throw new InvalidArgumentException('Unknown sort "' . $sort . '".');
        }
        $this->sort = $sort;

        return $this;
    }

    /**
     * Only these spaces (still only those visible); empty = no restriction.
     *
     * @param int[] $ids
     */
    public function ids(array $ids): static
    {
        $this->ids = self::normalizeIds($ids);

        return $this;
    }

    /**
     * Not these spaces.
     *
     * @param int[] $ids
     */
    public function exclude(array $ids): static
    {
        $this->exclude = self::normalizeIds($ids);

        return $this;
    }

    /**
     * What the list is for (one of {@see self::PURPOSES}); `null` = neutral. The core does not
     * filter by it — it is passed to {@see self::EVENT_INIT} so a module can restrict a list in
     * one context (e.g. hide spaces from the directory) and not in another.
     *
     * @throws InvalidArgumentException for an unknown purpose
     */
    public function purpose(?string $purpose): static
    {
        if ($purpose !== null && !in_array($purpose, self::PURPOSES, true)) {
            throw new InvalidArgumentException('Unknown purpose "' . $purpose . '".');
        }
        $this->purpose = $purpose;

        return $this;
    }

    public function getPurpose(): ?string
    {
        return $this->purpose;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Applies the known parameters to the named filters, then fires {@see self::EVENT_INIT} with
     * all of them.
     *
     * @param array{q?: string, scope?: string, archived?: bool, sort?: string|null, ids?: int[], exclude?: int[], purpose?: string|null} $params
     *        plus any parameter a module understands; the values must be valid (see the
     *        filters), the HTTP API validates them before
     */
    public function build(array $params = []): ActiveQuerySpace
    {
        $this->search((string)($params['q'] ?? ''))
            ->scope((string)($params['scope'] ?? self::SCOPE_ALL))
            ->archived((bool)($params['archived'] ?? false))
            ->sort(isset($params['sort']) && $params['sort'] !== '' ? (string)$params['sort'] : null)
            ->ids((array)($params['ids'] ?? []))
            ->exclude((array)($params['exclude'] ?? []))
            ->purpose(isset($params['purpose']) && $params['purpose'] !== '' ? (string)$params['purpose'] : null);

        $query = $this->getQuery();

        $this->trigger(self::EVENT_INIT, new SpaceListQueryEvent([
            'query' => $query,
            'params' => $params,
            'purpose' => $this->purpose,
            'user' => $this->user,
        ]));

        return $query;
    }

    /**
     * A new query with the configured filters and order — without {@see self::EVENT_INIT}
     * (that is {@see self::build()}).
     */
    public function getQuery(): ActiveQuerySpace
    {
        /** @var ActiveQuerySpace $query */
        $query = Space::find();
        $query->visible($this->user)->filterBlockedSpaces($this->user);

        $query->andWhere([$this->archived ? '=' : '!=', 'space.status', Space::STATUS_ARCHIVED]);

        if ($this->keywords !== '') {
            $query->search($this->keywords);
        }

        if ($this->ids !== []) {
            $query->andWhere(['space.id' => $this->ids]);
        }

        if ($this->exclude !== []) {
            $query->andWhere(['not in', 'space.id', $this->exclude]);
        }

        $this->applyScope($query);
        $this->applyOrder($query);

        return $query;
    }

    /**
     * The membership is joined rather than tested with an EXISTS for the scopes that order by
     * it, because the same join carries what the order needs (`last_visit`, and whether there
     * is a membership at all).
     */
    private function applyScope(ActiveQuerySpace $query): void
    {
        if ($this->scope === self::SCOPE_ALL) {
            return;
        }

        if ($this->user === null) {
            if ($this->scope !== self::SCOPE_NONE) {
                $query->andWhere('0=1');
            }

            return;
        }

        $userId = $this->user->id;

        $isFollowing = [
            'exists',
            Follow::find()
                ->where(['user_follow.user_id' => $userId, 'user_follow.object_model' => Space::class])
                ->andWhere('user_follow.object_id = space.id'),
        ];

        if ($this->scope === self::SCOPE_NONE) {
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

        $query->andWhere(match ($this->scope) {
            self::SCOPE_MEMBER => $isMember,
            self::SCOPE_FOLLOWING => $isFollowing,
            default => ['or', $isMember, $isFollowing],
        });
    }

    /**
     * Every order ends on the id, so paging through equal names or dates is stable.
     */
    private function applyOrder(ActiveQuerySpace $query): void
    {
        $sort = $this->sort;

        if ($sort === null) {
            if (in_array($this->scope, [self::SCOPE_MEMBER, self::SCOPE_FOLLOWING, self::SCOPE_MINE], true) && $this->user !== null) {
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
     */
    private function applyScopeOrder(ActiveQuerySpace $query): void
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

    /**
     * @return int[] positive, unique
     */
    private static function normalizeIds(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids), static fn(int $id) => $id > 0)));
    }
}
