<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\components\api\ApiRules;
use humhub\components\listing\FilterableList;
use humhub\components\listing\filters\EnumFilter;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\admin\models\forms\PeopleSettingsForm;
use humhub\modules\content\models\ContentContainerTag;
use humhub\modules\content\models\ContentContainerTagRelation;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\user\components\listing\ProfileFieldFilter;
use humhub\modules\user\components\listing\SpaceMembersFilter;
use humhub\modules\user\models\fieldtype\CheckboxList;
use humhub\modules\user\models\fieldtype\CountrySelect;
use humhub\modules\user\models\fieldtype\Select;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\Group;
use humhub\modules\user\models\GroupUser;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use Yii;
use yii\db\Expression;
use yii\db\Query;

/**
 * The user search of the platform: which users a list shows a user, filtered and in which
 * order. `GET /api/v2/user` is built with it, and so is anything else that lists users (the
 * People directory — whose filter definitions are {@see self::definitions()} —, the meta
 * search, user pickers and mentioning):
 *
 * ```php
 * $query = (new UserList())->build(['q' => 'marketing', 'groupId' => 3], ListContext::forCurrentUser(UserList::PURPOSE_DIRECTORY))->query();
 * ```
 *
 * Parameters: `q` (keywords over username, the searchable profile fields and tags), `scope`
 * (one of {@see self::availableScopes()}: the follow scopes while following is not disabled,
 * the friendship scopes while the friendship system is on; without a user every scope but `all`
 * is empty), `groupId` (members of a group shown in the directory and of its sub groups),
 * `tag` (repeated or comma-separated user tags, the users having all of them — as a card's tags
 * narrow the People directory step by step), `spaceId` (members of a space the caller may see the
 * members of, {@see SpaceMembersFilter}),
 * `fields[<internal name>]` (one filter per profile field marked as directory filter,
 * {@see ProfileFieldFilter}), `sort` (one of {@see self::SORTS}; `default` — and no sort — is
 * the administrator's People order), `ids` / `exclude` (at most 100 each) and `purpose` (one of
 * {@see self::PURPOSES}). `q` is at most {@see self::MAX_SEARCH_LENGTH} characters, `tag` at
 * most {@see self::MAX_TAGS} tags.
 *
 * Availability ({@see ActiveQueryUser::available()}: enabled, visible to the user, not blocked
 * by them) always applies, and hidden users ({@see User::VISIBILITY_HIDDEN}) are never listed —
 * not even to an administrator, who may otherwise see them —, except to themselves: they are the
 * list's base, not filters, so no filter can bring back a user the list must not show. The
 * directory ({@see self::PURPOSE_DIRECTORY}) never lists the context's user.
 *
 * A module adds one filter on {@see self::EVENT_INIT} (its parameter, validation, restriction
 * and the directory's definition) and restricts the list on {@see self::EVENT_BUILD}:
 *
 * ```php
 * ['class' => UserList::class, 'event' => UserList::EVENT_INIT, 'callback' => [Events::class, 'onUserListInit']],
 * ['class' => UserList::class, 'event' => UserList::EVENT_BUILD, 'callback' => [Events::class, 'onUserListBuild']],
 *
 * public static function onUserListInit(ListEvent $event)
 * {
 *     $event->list->addFilter(new EnumFilter('interest',
 *         values: Interest::labels(),
 *         apply: static fn(QueryListBuilder $list, string $id) => $list->query()->andWhere(['user.id' => Interest::userIds($id)]),
 *         definition: ['label' => Yii::t('MatchmakingModule.base', 'Interest'), 'sortOrder' => 250],
 *     ));
 * }
 *
 * public static function onUserListBuild(ListEvent $event)
 * {
 *     if ($event->context->purpose === UserList::PURPOSE_DIRECTORY) {
 *         $event->builder->query()->andWhere(['not in', 'user.id', HiddenPeople::ids()]);
 *     }
 * }
 * ```
 *
 * Such a restriction also applies to the suggestions of the directory's pickers
 * ({@see self::tagValues()}, {@see self::filterValues()}) and to whether it offers the tag
 * picker: they are taken from the list built without a filter, for the same context.
 *
 * @since 1.20
 */
class UserList extends FilterableList
{
    public const SCOPE_ALL = 'all';
    /**
     * Users the user follows.
     */
    public const SCOPE_FOLLOWING = 'following';
    /**
     * Users following the user.
     */
    public const SCOPE_FOLLOWERS = 'followers';
    /**
     * Mutual friendships.
     */
    public const SCOPE_FRIENDS = 'friends';
    /**
     * Friendship requests the user sent that are not answered yet.
     */
    public const SCOPE_PENDING_FRIENDS = 'pendingFriends';

    public const SCOPES = [self::SCOPE_ALL, self::SCOPE_FOLLOWING, self::SCOPE_FOLLOWERS, self::SCOPE_FRIENDS, self::SCOPE_PENDING_FRIENDS];

    /**
     * The administrator's order of the People directory (`people.defaultSorting`, see
     * {@see PeopleSettingsForm}); its "Default" puts the `people.defaultSortingGroup` first.
     */
    public const SORT_DEFAULT = 'default';
    public const SORT_FIRSTNAME = 'firstname';
    public const SORT_LASTNAME = 'lastname';
    public const SORT_LASTLOGIN = 'lastlogin';

    public const SORTS = [self::SORT_DEFAULT, self::SORT_FIRSTNAME, self::SORT_LASTNAME, self::SORT_LASTLOGIN];

    public const PURPOSE_DIRECTORY = 'directory';
    public const PURPOSE_PICKER = 'picker';
    public const PURPOSE_MENTIONING = 'mentioning';

    public const PURPOSES = [self::PURPOSE_DIRECTORY, self::PURPOSE_PICKER, self::PURPOSE_MENTIONING];

    /**
     * The most values {@see self::filterValues()} takes from what users entered, and the most
     * tags {@see self::tagValues()} answers: the top of an `optionsUrl` picker's suggestions,
     * without and with a search.
     */
    public const MAX_FILTER_VALUES = 20;

    /**
     * The longest tag the `tag` filter takes — the length of a tag's name.
     */
    public const MAX_TAG_LENGTH = 100;

    /**
     * The most tags the `tag` filter takes at once.
     */
    public const MAX_TAGS = 20;

    /**
     * The longest search (`q`) the list takes — also the longest typed text of its `optionsUrl`
     * endpoints (`user/tags`, `user/field-values`).
     */
    public const MAX_SEARCH_LENGTH = 255;

    /**
     * The profile field types a list can be filtered by, by their form input type: text fields,
     * dropdowns (also a country field) and checkbox lists, as the People directory's before 1.20.
     */
    public const FILTER_FIELD_INPUT_TYPES = ['text', 'dropdownlist', 'checkboxlist'];

    /**
     * @inheritdoc
     */
    protected function filters(): array
    {
        $filters = [
            new SearchFilter(
                'q',
                maxLength: self::MAX_SEARCH_LENGTH,
                apply: static fn(QueryListBuilder $list, string $keywords) => $list->query()->search($keywords),
                definition: [
                    'label' => Yii::t('UserModule.base', 'Search'),
                    'placeholder' => Yii::t('UserModule.base', 'Search people...'),
                    'sortOrder' => 100,
                ],
            ),
        ];

        // The groups shown in the directory: without one, there is nothing to filter by.
        $groups = Group::find()
            ->select(['name'])
            ->where(['show_at_directory' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'name' => SORT_ASC])
            ->indexBy('id')
            ->column();
        if ($groups !== []) {
            $filters[] = new EnumFilter(
                'groupId',
                values: array_map('strval', $groups),
                apply: static fn(QueryListBuilder $list, string $groupId) => self::onlyGroupMembers($list, (int)$groupId),
                definition: ['label' => Yii::t('UserModule.base', 'Group'), 'sortOrder' => 150],
            );
        }

        // `all` is accepted, not offered: the select's label is it.
        $labels = [
            self::SCOPE_ALL => null,
            self::SCOPE_FOLLOWERS => Yii::t('UserModule.base', 'Followers'),
            self::SCOPE_FOLLOWING => Yii::t('UserModule.base', 'Following'),
            self::SCOPE_FRIENDS => Yii::t('UserModule.base', 'Friends'),
            self::SCOPE_PENDING_FRIENDS => Yii::t('UserModule.base', 'Pending Requests'),
        ];
        $scopes = array_intersect_key($labels, array_flip(self::availableScopes()));
        $filters[] = new EnumFilter(
            'scope',
            values: $scopes,
            apply: static fn(QueryListBuilder $list, string $scope, ListContext $context) => self::applyScope($list, $scope, $context),
            // No select while only `all` is left.
            definition: count($scopes) > 1 ? ['label' => Yii::t('UserModule.base', 'Status'), 'sortOrder' => 300] : null,
        );

        $filters[] = new EnumFilter(
            'tag',
            apply: static fn(QueryListBuilder $list, array $tags) => self::onlyTagged($list, $tags),
            multiple: true,
            // Any name but one with a comma - the separator of the parameter's values.
            pattern: '/^[^,]{1,' . self::MAX_TAG_LENGTH . '}$/u',
            max: self::MAX_TAGS,
            // Left out by definitions() while no user the list shows has a tag.
            definition: [
                'type' => 'picker',
                'label' => Yii::t('UserModule.base', 'Tags'),
                'multiple' => true,
                'optionsUrl' => ApiRules::url('user/tags'),
                'sortOrder' => 180,
            ],
        );

        $filters[] = new SpaceMembersFilter('spaceId');

        $sortOrder = 1000;
        foreach (self::filterFields() as $field) {
            $filters[] = new ProfileFieldFilter($field, $sortOrder);
            $sortOrder += 10;
        }

        $filters[] = new IdsFilter('ids', column: 'user.id');
        $filters[] = new IdsFilter('exclude', column: 'user.id', exclude: true);

        return $filters;
    }

    /**
     * @inheritdoc
     */
    protected function sorts(): array
    {
        // No option for the default order: the select's label is it, as "all" is for a select.
        return [
            self::SORT_DEFAULT => null,
            self::SORT_FIRSTNAME => Yii::t('AdminModule.user', 'First name'),
            self::SORT_LASTNAME => Yii::t('AdminModule.user', 'Last name'),
            self::SORT_LASTLOGIN => Yii::t('AdminModule.user', 'Last login'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function sortLabel(): string
    {
        return Yii::t('UserModule.base', 'Sort');
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
     * @return QueryListBuilder over an {@see ActiveQueryUser}
     */
    public function build(array $params, ListContext $context): QueryListBuilder
    {
        /** @var QueryListBuilder $builder */
        $builder = parent::build($params, $context);

        return $builder;
    }

    /**
     * Without the tag picker while no user the list shows the context's user has a tag.
     *
     * @inheritdoc
     */
    public function definitions(ListContext $context): array
    {
        $definitions = parent::definitions($context);

        foreach ($definitions as $index => $definition) {
            if ($definition['key'] === 'tag' && !$this->hasUserTags($context)) {
                unset($definitions[$index]);
            }
        }

        return array_values($definitions);
    }

    /**
     * The condition leaving out hidden users ({@see User::VISIBILITY_HIDDEN}) — all but the
     * caller, who still finds themselves.
     */
    public static function notHidden(?User $user): array
    {
        $notHidden = ['!=', 'user.visibility', User::VISIBILITY_HIDDEN];

        return $user === null ? $notHidden : ['OR', $notHidden, ['user.id' => $user->id]];
    }

    /**
     * @inheritdoc
     */
    protected function createBuilder(ListContext $context): ListBuilder
    {
        /** @var ActiveQueryUser $query */
        $query = User::find();
        // What a list page renders of every user, loaded with the page rather than per user.
        $query->with(['profile', 'contentContainerRecord']);
        $query->available($context->user)->andWhere(self::notHidden($context->user));

        if ($context->purpose === self::PURPOSE_DIRECTORY && $context->user !== null) {
            // The directory is about the others.
            $query->andWhere(['!=', 'user.id', $context->user->id]);
        }

        return new QueryListBuilder($query);
    }

    /**
     * The order: a sort with its own callback (one a module added, also for a key of the list's
     * own), else the list's.
     *
     * @inheritdoc
     */
    protected function finalize(ListBuilder $builder, array $values, ListContext $context): void
    {
        /** @var QueryListBuilder $builder */
        $sort = $this->value($values, self::SORT_PARAM);

        if ($sort !== null && $this->applySortCallback($builder, $sort, $context)) {
            return;
        }

        $this->applyOrder($builder->query(), $sort ?? self::SORT_DEFAULT);
    }

    /**
     * The scopes available on this platform: the follow scopes only while following is not
     * disabled, the friendship scopes only while the friendship system is enabled.
     *
     * @return string[] a subset of {@see self::SCOPES}
     */
    public static function availableScopes(): array
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        $friendship = (bool)Yii::$app->getModule('friendship')->settings->get('enable');

        return array_values(array_filter(self::SCOPES, static fn(string $scope) => match ($scope) {
            self::SCOPE_FOLLOWING, self::SCOPE_FOLLOWERS => !$module->disableFollow,
            self::SCOPE_FRIENDS, self::SCOPE_PENDING_FRIENDS => $friendship,
            default => true,
        }));
    }

    /**
     * The profile fields a list can be filtered by: the visible ones an administrator marked as
     * directory filter, of a type a filter is offered for ({@see self::FILTER_FIELD_INPUT_TYPES}).
     *
     * @return array<string, ProfileField> by internal name, in the order of the profile
     */
    public static function filterFields(): array
    {
        /** @var ProfileField[] $fields */
        $fields = ProfileField::find()
            ->where(['directory_filter' => 1, 'visible' => 1])
            ->orderBy(['sort_order' => SORT_ASC, 'id' => SORT_ASC])
            ->indexBy('internal_name')
            ->all();

        return array_filter($fields, static function (ProfileField $field) {
            $fieldType = $field->getFieldType();
            $inputType = $fieldType ? ($fieldType->getFieldFormDefinition()[$field->internal_name]['type'] ?? null) : null;

            return in_array($inputType, self::FILTER_FIELD_INPUT_TYPES, true);
        });
    }

    /**
     * The values a filter over the profile field offers ({@see ProfileFieldFilter}), as
     * `[{id, name, count?}]` — `id` is the value to filter by, `name` its label, `count` how many
     * users entered a value users entered:
     *
     * - a dropdown: its options
     * - a checkbox list: its options, plus the "Other:" values users entered while "Other:" is
     *   allowed
     * - a country field: the countries users picked, by name
     * - any other field: the values users entered
     *
     * Values users entered are taken from the users the list shows the context's user without a
     * filter ({@see self::unfilteredQuery()}: the restrictions of modules apply), the most frequent first, at most {@see self::MAX_FILTER_VALUES}. With
     * `$search` (a picker's typed text), only the values containing it — for an option or a
     * country its label, for what users entered the value itself — and each kind at most
     * {@see self::MAX_FILTER_VALUES}.
     *
     * @return array<int, array{id: string, name: string, count?: int}>
     */
    public function filterValues(ProfileField $field, ListContext $context, ?string $search = null): array
    {
        $fieldType = $field->getFieldType();
        $name = $field->internal_name;
        $search = $search !== null && trim($search) !== '' ? trim($search) : null;
        $matches = static fn(string $label) => $search === null || mb_stripos($label, $search) !== false;

        if ($fieldType instanceof CountrySelect) {
            // Stored are the country codes: searched by name, over every country users picked.
            $items = $fieldType->getSelectItems();
            $results = [];
            foreach ($this->storedValues($name, $context, null, null) as $stored) {
                $label = (string)($items[$stored['value']] ?? $stored['value']);
                if ($matches($label)) {
                    $results[] = ['id' => $stored['value'], 'name' => $label, 'count' => $stored['count']];
                }
            }

            return array_slice($results, 0, self::MAX_FILTER_VALUES);
        }

        if (!($fieldType instanceof Select || $fieldType instanceof CheckboxList)) {
            $results = [];
            foreach ($this->storedValues($name, $context, $search) as $stored) {
                $results[] = ['id' => $stored['value'], 'name' => $stored['value'], 'count' => $stored['count']];
            }

            return $results;
        }

        $items = $fieldType->getSelectItems();
        // The "Other:" option itself is nothing to filter by - what users entered there is.
        unset($items['other']);
        $results = [];
        foreach ($items as $id => $label) {
            if ($matches((string)$label)) {
                $results[] = ['id' => (string)$id, 'name' => (string)$label];
            }
        }

        if ($fieldType instanceof CheckboxList && $fieldType->allowOther) {
            foreach ($this->storedValues(CheckboxList::getOtherColumnName($name), $context, $search) as $stored) {
                if (!isset($items[$stored['value']])) {
                    $results[] = ['id' => $stored['value'], 'name' => $stored['value'], 'count' => $stored['count']];
                }
            }
        }

        return $results;
    }

    /**
     * The user tags of the users the list shows the context's user without a filter
     * ({@see self::unfilteredQuery()}: the restrictions of modules apply), as
     * `[{id, name, count}]` — `id` and `name` the tag, what the `tag` filter takes, `count` how
     * many of these users have it —, the most frequent first, at most
     * {@see self::MAX_FILTER_VALUES}; with `$search` only the tags containing it.
     *
     * @return array<int, array{id: string, name: string, count: int}>
     */
    public function tagValues(ListContext $context, ?string $search = null): array
    {
        $search = $search !== null && trim($search) !== '' ? trim($search) : null;

        $containers = $this->unfilteredQuery($context)->select('user.contentcontainer_id');

        $rows = (new Query())
            ->select(['name' => 'tag.name', 'count' => 'COUNT(*)'])
            ->from(['tag' => ContentContainerTag::tableName()])
            ->innerJoin(['relation' => ContentContainerTagRelation::tableName()], 'relation.tag_id = tag.id')
            ->where(['tag.contentcontainer_class' => User::class])
            ->andWhere(['relation.contentcontainer_id' => $containers])
            ->andFilterWhere(['LIKE', 'tag.name', $search])
            ->groupBy(['tag.id', 'tag.name'])
            ->orderBy(['count' => SORT_DESC, 'tag.name' => SORT_ASC])
            ->limit(self::MAX_FILTER_VALUES)
            ->all();

        return array_map(static fn(array $row) => [
            'id' => (string)$row['name'],
            'name' => (string)$row['name'],
            'count' => (int)$row['count'],
        ], $rows);
    }

    /**
     * Whether any user the list shows the context's user has a tag — without, the directory
     * offers no tag picker.
     */
    private function hasUserTags(ListContext $context): bool
    {
        return (new Query())
            ->from(['relation' => ContentContainerTagRelation::tableName()])
            ->innerJoin(['tag' => ContentContainerTag::tableName()], 'tag.id = relation.tag_id')
            ->where(['tag.contentcontainer_class' => User::class])
            ->andWhere(['relation.contentcontainer_id' => $this->unfilteredQuery($context)->select('user.contentcontainer_id')])
            ->exists();
    }

    /**
     * The users the list shows the context's user without a filter, in no order — what the
     * suggestions of its pickers are taken from: built as the list itself ({@see self::build()}),
     * so the restrictions of modules ({@see self::EVENT_BUILD}, e.g. for the context's purpose)
     * apply to them as well.
     */
    private function unfilteredQuery(ListContext $context): ActiveQueryUser
    {
        /** @var ActiveQueryUser $query */
        $query = $this->build([], $context)->query();

        return $query->orderBy(null);
    }

    /**
     * Users having every one of the tags: one subquery for any number of tags. Tag names are
     * unique per container class in the database's collation, so tags that differ in case only
     * are one tag.
     *
     * @param string[] $tags
     */
    private static function onlyTagged(QueryListBuilder $list, array $tags): void
    {
        $tags = array_values(array_unique(array_map('mb_strtolower', $tags)));

        $list->query()->andWhere(['user.contentcontainer_id' => (new Query())
            ->select('relation.contentcontainer_id')
            ->from(['relation' => ContentContainerTagRelation::tableName()])
            ->innerJoin(['tag' => ContentContainerTag::tableName()], 'tag.id = relation.tag_id')
            ->where(['tag.contentcontainer_class' => User::class, 'tag.name' => $tags])
            ->groupBy('relation.contentcontainer_id')
            ->having(['=', new Expression('COUNT(DISTINCT tag.id)'), count($tags)])]);
    }

    /**
     * The non-empty values of a profile column among the users the list may show, with how
     * many of them entered each, the most frequent first (at most `$limit`, `null` = all);
     * with `$search` only those containing it.
     *
     * @return array<int, array{value: string, count: int}>
     */
    private function storedValues(string $column, ListContext $context, ?string $search = null, ?int $limit = self::MAX_FILTER_VALUES): array
    {
        $column = 'profile.' . $column;

        // A plain command: the list's eager loading has no place in an aggregate.
        $rows = $this->unfilteredQuery($context)
            ->joinWith('profile', false)
            ->select(['value' => $column, 'count' => new Expression('COUNT(*)')])
            ->andWhere(['IS NOT', $column, null])
            ->andWhere(['!=', $column, ''])
            ->andFilterWhere(['LIKE', $column, $search])
            ->groupBy($column)
            ->orderBy([new Expression('COUNT(*) DESC'), $column => SORT_ASC])
            ->limit($limit)
            ->createCommand()
            ->queryAll();

        return array_map(static fn(array $row) => ['value' => (string)$row['value'], 'count' => (int)$row['count']], $rows);
    }

    /**
     * Members of the group and of its sub groups — a subquery rather than
     * {@see ActiveQueryUser::isGroupMember()}'s join: a member of the group and of one of its
     * sub groups must not be listed twice.
     */
    private static function onlyGroupMembers(QueryListBuilder $list, int $groupId): void
    {
        $list->query()->andWhere(['user.id' => GroupUser::find()->select('group_user.user_id')->where(['group_user.group_id' => Group::find()
            ->select('id')
            ->where(['parent_group_id' => $groupId])
            ->orWhere(['id' => $groupId])])]);
    }

    /**
     * Without a user every scope but `all` is empty.
     */
    private static function applyScope(QueryListBuilder $list, string $scope, ListContext $context): void
    {
        if ($scope === self::SCOPE_ALL) {
            return;
        }

        $query = $list->query();

        if ($context->user === null) {
            $query->andWhere('0=1');

            return;
        }

        $me = (int)$context->user->id;

        // `$follower` follows `$followed`, both SQL expressions (`user.id` or the user's id).
        $follow = static fn(string $follower, string $followed) => [
            'exists',
            Follow::find()
                ->where(['user_follow.object_model' => User::class])
                ->andWhere('user_follow.user_id = ' . $follower)
                ->andWhere('user_follow.object_id = ' . $followed),
        ];

        // `$from` asked `$to` - a friendship is a row in each direction, a request one row.
        $request = static fn(string $from, string $to) => [
            'exists',
            Friendship::find()
                ->where('user_friendship.user_id = ' . $from)
                ->andWhere('user_friendship.friend_user_id = ' . $to),
        ];

        $query->andWhere(match ($scope) {
            self::SCOPE_FOLLOWING => $follow((string)$me, 'user.id'),
            self::SCOPE_FOLLOWERS => $follow('user.id', (string)$me),
            self::SCOPE_FRIENDS => ['and', $request((string)$me, 'user.id'), $request('user.id', (string)$me)],
            default => ['and', $request((string)$me, 'user.id'), ['not', $request('user.id', (string)$me)]],
        });
    }

    /**
     * Every order ends on the id, so paging through equal names or dates is stable.
     *
     * @param ActiveQueryUser $query
     */
    private function applyOrder($query, string $sort): void
    {
        $groupFirst = null;

        if ($sort === self::SORT_DEFAULT) {
            $settings = new PeopleSettingsForm();
            $sort = in_array($settings->defaultSorting, self::SORTS, true) ? $settings->defaultSorting : self::SORT_LASTLOGIN;
            if ((string)$settings->defaultSorting === '' && PeopleSettingsForm::isDefaultGroupDefined()) {
                // "Default": the prioritised group's members first, everybody by last login.
                $groupFirst = (int)$settings->defaultSortingGroup;
                $sort = self::SORT_LASTLOGIN;
            }
        }

        $order = [];

        if ($groupFirst !== null) {
            $order[] = new Expression(
                'EXISTS (SELECT 1 FROM ' . GroupUser::tableName() . ' sort_group_user'
                . ' WHERE sort_group_user.user_id = user.id AND sort_group_user.group_id = ' . $groupFirst . ') DESC',
            );
        }

        if ($sort === self::SORT_FIRSTNAME || $sort === self::SORT_LASTNAME) {
            $query->joinWith('profile');
        }

        $order += match ($sort) {
            self::SORT_FIRSTNAME => ['profile.firstname' => SORT_ASC, 'profile.lastname' => SORT_ASC],
            self::SORT_LASTNAME => ['profile.lastname' => SORT_ASC, 'profile.firstname' => SORT_ASC],
            default => ['user.last_login' => SORT_DESC],
        };
        $order['user.id'] = SORT_ASC;

        $query->orderBy($order);
    }
}
