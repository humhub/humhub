<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components\listing;

use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListFilter;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;

/**
 * `spaceId`: only the members of a space — a context filter of the
 * {@see \humhub\modules\user\components\UserList} (a picker of a space's members), without UI.
 *
 * It authorizes itself while parsing: the space must be one the caller may see together with its
 * members ({@see Space::canViewMembers()}); another one, or an id of no space at all, is an
 * "Unknown value", so a caller cannot probe which spaces exist. The value is the space id.
 *
 * @since 1.20
 */
class SpaceMembersFilter extends ListFilter
{
    public function __construct(private readonly string $key = 'spaceId')
    {
    }

    /**
     * @inheritdoc
     */
    public function key(): string
    {
        return $this->key;
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

        $space = $id === false ? null : Space::find()
            ->visible($context->user)
            ->filterBlockedSpaces($context->user)
            ->andWhere(['space.id' => $id])
            ->one();

        if ($space === null || !$space->canViewMembers($context->user?->id)) {
            return $this->invalid($this->unknownValue($value));
        }

        return FilterValue::of((int)$space->id);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        /** @var QueryListBuilder $list */
        $list->query()->andWhere(['user.id' => Membership::find()
            ->select('space_membership.user_id')
            ->where(['space_membership.space_id' => $value->value, 'space_membership.status' => Membership::STATUS_MEMBER])]);
    }
}
