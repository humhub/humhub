<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components\listing;

use humhub\components\api\ApiRules;
use humhub\components\listing\FilterDefinition;
use humhub\components\listing\FilterValue;
use humhub\components\listing\ListBuilder;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListFilter;
use humhub\components\listing\QueryListBuilder;
use humhub\modules\user\models\fieldtype\CheckboxList;
use humhub\modules\user\models\fieldtype\CountrySelect;
use humhub\modules\user\models\fieldtype\Select;
use humhub\modules\user\models\ProfileField;
use Yii;

/**
 * `fields[<internal name>]`: one profile field marked as directory filter — one filter per field
 * of {@see \humhub\modules\user\components\UserList::filterFields()}, keyed by the bracket key
 * (`FilterableList::build()` reads `fields[city]=x` back from PHP's `['fields' => ['city' => 'x']]`).
 *
 * The matching of the People directory's profile field filters since 1.9: a dropdown matches its
 * option key, a checkbox list any of its stored options (or the "Other:" value while that is
 * allowed), any other field a part of its text. The value is the trimmed string; an empty one is
 * absent.
 *
 * The definition:
 *
 * - a dropdown (not a country field) and a checkbox list without "Other:" — a fixed set of
 *   options — are a `select` with the options in it, or with more than
 *   {@see self::MAX_SELECT_OPTIONS} options a `picker` with them (searched in the browser)
 * - any other field (text, country, a checkbox list with "Other:" — values users entered) is a
 *   `picker` whose suggestions load from `GET /api/v2/user/field-values` (the most frequent,
 *   then a search as typed, {@see \humhub\modules\user\components\UserList::filterValues()})
 *
 * The value is a single one either way: the picker is single-choice. For a field matched by a
 * part of its text (neither a dropdown nor a checkbox list) the picker is `custom`: the typed
 * text itself can be applied, not only a value users entered.
 *
 * @since 1.20
 */
class ProfileFieldFilter extends ListFilter
{
    public const PARAM = 'fields';

    /**
     * The most options a field's filter lists in a `select`; a field with more is a `picker`.
     */
    public const MAX_SELECT_OPTIONS = 15;

    public function __construct(
        private readonly ProfileField $field,
        private readonly int $sortOrder = 1000,
    ) {
    }

    /**
     * The key of the filter over the profile field `$name`: `fields[<name>]`.
     */
    public static function keyOf(string $name): string
    {
        return self::PARAM . '[' . $name . ']';
    }

    /**
     * @inheritdoc
     */
    public function key(): string
    {
        return self::keyOf($this->field->internal_name);
    }

    public function getField(): ProfileField
    {
        return $this->field;
    }

    /**
     * @inheritdoc
     */
    public function parse(array $raw, ListContext $context): FilterValue
    {
        $value = $raw[$this->key()] ?? null;

        if ($value === null) {
            return FilterValue::absent();
        }

        if (!is_string($value)) {
            return $this->invalid($this->unknownValue($value));
        }

        $value = trim($value);

        return $value === '' ? FilterValue::absent() : FilterValue::of($value);
    }

    /**
     * @inheritdoc
     */
    public function apply(ListBuilder $list, FilterValue $value, ListContext $context): void
    {
        /** @var QueryListBuilder $list */
        $query = $list->query();
        $query->joinWith('profile');

        $name = $this->field->internal_name;
        $fieldType = $this->field->getFieldType();
        $column = 'profile.' . $name;

        if ($fieldType instanceof CheckboxList) {
            // The stored value is the selected option keys, one per line.
            $delimiter = preg_quote(CheckboxList::MULTI_VALUE_DELIMITER, '/');
            $condition = ['or', ['REGEXP', $column, '(^|' . $delimiter . ')' . preg_quote($value->value, '/') . '(' . $delimiter . '|$)']];
            if ($fieldType->allowOther) {
                // Only while "Other:" is allowed - the column may still hold stale values from
                // before it was switched off.
                $condition[] = ['profile.' . CheckboxList::getOtherColumnName($name) => $value->value];
            }
            $query->andWhere($condition);
        } elseif ($fieldType instanceof Select) {
            $query->andWhere([$column => $value->value]);
        } else {
            $query->andWhere(['LIKE', $column, $value->value]);
        }
    }

    /**
     * @inheritdoc
     */
    public function definition(ListContext $context): ?FilterDefinition
    {
        $fieldType = $this->field->getFieldType();

        $isStatic = ($fieldType instanceof Select && !$fieldType instanceof CountrySelect)
            || ($fieldType instanceof CheckboxList && !$fieldType->allowOther);

        $options = null;
        $optionsUrl = null;

        if ($isStatic) {
            $items = $fieldType->getSelectItems();
            // The "Other:" option itself is nothing to filter by.
            unset($items['other']);
            $options = [];
            foreach ($items as $value => $label) {
                $options[] = ['value' => (string)$value, 'label' => (string)$label];
            }
        } else {
            $optionsUrl = ApiRules::url('user/field-values') . '?' . http_build_query(['field' => $this->field->internal_name]);
        }

        $type = $options !== null && count($options) <= self::MAX_SELECT_OPTIONS ? 'select' : 'picker';

        return new FilterDefinition(
            type: $type,
            label: Yii::t($this->field->getTranslationCategory(), $this->field->title),
            options: $options,
            optionsUrl: $optionsUrl,
            // Matched by a part of the text (see apply()): what is typed is a value as well.
            custom: $this->matchesPart() ? true : null,
            sortOrder: $this->sortOrder,
        );
    }

    /**
     * Whether the field is matched by a part of its text — neither a dropdown (nor a country
     * field) nor a checkbox list, whose values are keys.
     */
    private function matchesPart(): bool
    {
        $fieldType = $this->field->getFieldType();

        return !($fieldType instanceof Select || $fieldType instanceof CheckboxList);
    }
}
