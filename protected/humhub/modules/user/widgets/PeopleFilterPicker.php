<?php

namespace humhub\modules\user\widgets;

use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\models\fieldtype\CheckboxList;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\Expression;
use yii\helpers\Url;

/**
 * People Filter Picker
 */
class PeopleFilterPicker extends BasePicker
{
    /**
     * @inheritdoc
     */
    public $minInput = 1;

    /**
     * @inheritdoc
     */
    public $defaultRoute = '/user/people/filter-people-json';

    public ?PeopleQuery $query = null;

    protected ?array $cachedDefaultResultData = null;

    protected ?ProfileField $_profileField = null;

    /**
     * Whether "Other:" values still need to be merged into {@see $defaultResults}.
     * Deferred so the underlying queries only run when suggestions are actually built.
     */
    protected bool $_pendingOtherValuesMerge = false;

    /**
     * Per-request memoization of {@see getFilteredProfileFieldValues()}, keyed by field name.
     */
    protected array $_filteredProfileFieldValuesCache = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->itemClass = Profile::class;
        $this->url = Url::to([$this->defaultRoute, 'field' => $this->itemKey]);

        parent::init();

        $this->_profileField = ProfileField::findOne(['internal_name' => $this->itemKey, 'directory_filter' => 1]);
        if ($this->_profileField === null) {
            throw new InvalidConfigException('Invalid filter key');
        }

        if (empty($this->defaultResults) && $this->_profileField->internal_name != 'country') {
            $definition = $this->_profileField->fieldType->getFieldFormDefinition();
            $type = $definition[$this->_profileField->internal_name]['type'] ?? null;
            if (in_array($type, ['dropdownlist', 'checkboxlist'], true)) {
                $this->defaultResults = $definition[$this->_profileField->internal_name]['items'];
            }

            // "Other:" values are merged lazily in mergeOtherValuesIntoDefaultResults(),
            // the first time suggestions are actually requested, to avoid running the
            // extra queries on every render regardless of whether they're needed.
            if (!empty($this->_profileField->fieldType->allowOther) && isset($this->defaultResults['other'])) {
                unset($this->defaultResults['other']);
                $this->_pendingOtherValuesMerge = true;
            }
        }
    }

    /**
     * Merges all "Other:" values entered by users into the default options list.
     * No-op after the first call (guarded by {@see $_pendingOtherValuesMerge}).
     */
    protected function mergeOtherValuesIntoDefaultResults(): void
    {
        if (!$this->_pendingOtherValuesMerge) {
            return;
        }
        $this->_pendingOtherValuesMerge = false;

        $otherValues = $this->getFilteredProfileFieldValues($this->itemKey);
        foreach ($otherValues as $otherValue) {
            if (!isset($this->defaultResults[$otherValue]) && $otherValue !== '' && $otherValue !== 'other') {
                $this->defaultResults[$otherValue] = $otherValue;
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function beforeRun()
    {
        return parent::beforeRun() && $this->hasOptions();
    }

    public function hasOptions(): bool
    {
        return $this->getDefaultResultData() !== [] || $this->getSelectedOptions() !== [];
    }

    protected function getSelectedOptionKey(): ?string
    {
        $get = Yii::$app->request->get('fields');

        return $get[$this->itemKey] ?? null;
    }

    /**
     * @inheritdoc
     */
    protected function getSelectedOptions()
    {
        if (!$this->selection) {
            $this->selection = [];
        }

        $selectedKey = $this->getSelectedOptionKey();
        if ($selectedKey !== null) {
            $this->selection[] = $selectedKey;
        }

        $result = [];
        foreach ($this->selection as $item) {
            if ($item === '' || $item === null) {
                continue;
            }

            $result[$this->itemKey] = [
                'data-id' => $item,
                'data-text' => $this->defaultResults[$item] ?? $item,
            ];
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getAttributes()
    {
        return [
            'data-multiple' => 'false',
            'data-tags' => 'false',
            'data-clearable' => 'false',
            'size' => '1',
            'class' => 'form-control',
            'style' => 'width:100%',
            'title' => $this->placeholder,
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        $result = parent::getData();
        $result['placeholder'] = Yii::t('UiModule.base', 'Select');
        $result['no-result'] = Yii::t('UserModule.chooser', 'No results found.');
        $result['maximum-selected'] = '';

        return $result;
    }

    /**
     * @inheritdoc
     * @param Profile $item
     */
    protected function getItemText($item)
    {
        $itemKey = $this->itemKey;
        return $item->$itemKey;
    }

    /**
     * @inheritdoc
     * @param Profile $item
     */
    protected function getItemImage($item)
    {
        return $item->user->getProfileImage();
    }

    /**
     * Returns suggestions by keyword
     *
     * @param string $keyword
     * @return array
     */
    public function getSuggestions($keyword = ''): array
    {
        $this->mergeOtherValuesIntoDefaultResults();

        if (empty($this->defaultResults)) {
            if ($this->query instanceof PeopleQuery && $this->query->isFiltered()) {
                $filteredValues = $this->getFilteredProfileFieldValues($this->itemKey);
                $suggestions = [];
                foreach ($filteredValues as $filteredValue) {
                    $suggestions[] = ['id' => $filteredValue, 'text' => $filteredValue];
                }
                return $suggestions;
            }

            return User::find()
                ->select(['id' => $this->itemKey, 'text' => $this->itemKey])
                ->visible()
                ->joinWith('profile')
                ->andWhere(['LIKE', $this->itemKey, $keyword])
                ->groupBy($this->itemKey)
                ->orderBy($this->itemKey)
                ->limit(100)
                ->asArray()
                ->all();
        }

        if ($this->query instanceof PeopleQuery && $this->query->isFiltered()) {
            $filteredResults = $this->getFilteredProfileFieldValues($this->itemKey);
            $filteredResults[] = $this->getSelectedOptionKey();
        }

        $result = [];
        foreach ($this->defaultResults as $itemKey => $itemText) {
            if (isset($filteredResults) && !in_array($itemKey, $filteredResults)) {
                continue;
            }

            if ($keyword !== '' && stripos((string) $itemText, $keyword) === false) {
                continue;
            }

            $result[] = [
                'data-id' => $itemKey,
                'data-text' => $itemText,
            ];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultResultData()
    {
        if ($this->cachedDefaultResultData === null) {
            $this->cachedDefaultResultData = $this->getSuggestions();
        }

        return $this->cachedDefaultResultData;
    }

    protected function getFilteredProfileFieldValuesQuery(string $field): ActiveQuery
    {
        $query = $this->query instanceof PeopleQuery ? clone $this->query : new PeopleQuery();

        return $query->select('fp.' . $field)
            ->distinct('fp.' . $field)
            ->leftJoin('profile AS fp', 'fp.user_id = user.id')
            ->andWhere(['IS NOT', 'fp.' . $field, new Expression('NULL')])
            ->limit(100)
            ->offset(null)
            ->orderBy('fp.' . $field);
    }

    protected function getFilteredProfileFieldValues(string $field): array
    {
        if (array_key_exists($field, $this->_filteredProfileFieldValuesCache)) {
            return $this->_filteredProfileFieldValuesCache[$field];
        }

        $values = $this->getFilteredProfileFieldValuesQuery($field)->column();

        if ($this->_profileField->fieldType instanceof CheckboxList) {
            // Split multi value strings into their individual values; explode() already
            // returns [$value] unchanged when the delimiter is absent, so no branch is needed.
            $checkboxListValues = array_merge([], ...array_map(
                static fn($value) => explode(CheckboxList::MULTI_VALUE_DELIMITER, $value),
                $values,
            ));

            if ($this->_profileField->fieldType->allowOther) {
                // Append all "Other:" values entered by users
                $checkboxListValues = array_merge(
                    $checkboxListValues,
                    $this->getFilteredProfileFieldValuesQuery(CheckboxList::getOtherColumnName($field))->column(),
                );
            }

            $values = array_values(array_unique($checkboxListValues));
        }

        return $this->_filteredProfileFieldValuesCache[$field] = $values;
    }
}
