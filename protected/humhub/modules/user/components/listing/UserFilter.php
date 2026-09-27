<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components\listing;

use humhub\components\listing\FilterDefinition;
use humhub\components\listing\filters\ConfigurableFilter;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\user\components\UserList;
use Yii;

/**
 * One person (default parameter `userId`) — a list's "Author", "Assignee" or "Created by": the
 * value is the id of a user the caller may see, presented as the `user` filter type of the
 * `FilterBar` (a user search with suggestions from `GET /api/v2/user?purpose=picker`, the
 * user module's `UserFilterControl`).
 *
 * ```php
 * new UserFilter('authorId', column: 'content.created_by', definition: ['label' => Yii::t('TasksModule.base', 'Author'), 'sortOrder' => 200]);
 * ```
 *
 * The user must be one the {@see UserList} shows the caller as a picker
 * ({@see UserList::PURPOSE_PICKER}: available, not hidden, and the restrictions modules add) —
 * exactly the users the control suggests. Any other id, and an id of nobody, is "User not
 * found.", so a caller cannot probe which users exist; what is no id at all is refused as such.
 * The value is the user id (an int). Not available to guests ({@see self::isAvailable()}).
 *
 * Applied by the `apply` callback (`fn(ListBuilder $list, int $userId, ListContext $context)`),
 * or, on a {@see QueryListBuilder}, as `column = <id>`. The definition's `type` is always
 * `user` — the value is an id only this control produces —, its label defaults to "Person".
 *
 * @since 1.20
 */
class UserFilter extends ConfigurableFilter
{
    public const TYPE = 'user';

    public function __construct(
        string $key = 'userId',
        protected readonly ?string $column = null,
        ?callable $apply = null,
        ?array $definition = null,
    ) {
        parent::__construct($key, $apply, $definition);
    }

    /**
     * Not for guests: the users a picker offers are searched in `GET /api/v2/user`, which has
     * no guest access — a guest gets no definition, and a guest's value is refused.
     *
     * @inheritdoc
     */
    public function isAvailable(ListContext $context): bool
    {
        return $context->user !== null;
    }

    /**
     * @inheritdoc
     */
    public function parse(array $raw, ListContext $context): FilterValue
    {
        $value = $raw[$this->key] ?? null;

        if ($value === null || $value === '') {
            return FilterValue::absent();
        }

        $id = is_int($value) || is_string($value)
            ? filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])
            : false;

        if ($id === false) {
            return $this->invalid(Yii::t('yii', '{attribute} must be an integer.', ['attribute' => $this->key]));
        }

        if (!$this->isListed($id, $context)) {
            return $this->invalid(Yii::t('UserModule.base', 'User not found.'));
        }

        return FilterValue::of($id);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        if ($this->applyCallback($list, $value->value, $context) || $this->column === null || !$list instanceof QueryListBuilder) {
            return;
        }

        $list->query()->andWhere([$this->column => $value->value]);
    }

    /**
     * @inheritdoc
     */
    public function definition(ListContext $context): ?FilterDefinition
    {
        if ($this->definition === null) {
            return null;
        }

        // The type last: a definition cannot turn the filter into a control that sends no user id.
        return new FilterDefinition(...array_merge($this->defaultDefinition($context), $this->definition, ['type' => self::TYPE]));
    }

    /**
     * @inheritdoc
     */
    protected function defaultDefinition(ListContext $context): array
    {
        return ['type' => self::TYPE, 'label' => Yii::t('UserModule.base', 'Person')];
    }

    /**
     * Whether the user list of a picker shows the context's user this user — built as the list
     * itself, so the rules of the suggestions (availability, hidden users, the restrictions of
     * modules for the purpose) are the rules of the value, never a copy of them.
     *
     * The context is the one the control's suggestions are searched in (`GET /api/v2/user?purpose=picker`
     * knows the caller only): the caller and the picker purpose, without the host list's purpose
     * and container — a module restricting those must not refuse a user the control offered.
     */
    private function isListed(int $id, ListContext $context): bool
    {
        return (new UserList())
            ->build(['ids' => [$id]], new ListContext($context->user, UserList::PURPOSE_PICKER))
            ->query()
            ->exists();
    }
}
