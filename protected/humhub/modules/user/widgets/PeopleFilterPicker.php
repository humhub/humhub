<?php

namespace humhub\modules\user\widgets;

use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\user\components\PeopleQuery;
use humhub\modules\user\models\User;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use Yii;
use yii\base\InvalidConfigException;
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

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->itemClass = Profile::class;
        $this->url = Url::to([$this->defaultRoute, 'field' => $this->itemKey]);

        parent::init();

        $profileField = ProfileField::findOne(['internal_name' => $this->itemKey, 'directory_filter' => 1]);
        if ($profileField === null) {
            throw new InvalidConfigException('Invalid filter key');
        }
        if (empty($this->defaultResults) && $profileField->internal_name != 'country') {
            $definition = $profileField->fieldType->getFieldFormDefinition();
            if (isset($definition[$profileField->internal_name]['type']) && $definition[$profileField->internal_name]['type'] === 'dropdownlist') {
                $this->defaultResults = $definition[$profileField->internal_name]['items'];
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
                'data-text' => isset($this->defaultResults[$item]) ? $this->defaultResults[$item] : $item,
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

            if ($keyword !== '' && stripos($itemText, $keyword) === false) {
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

    protected function getFilteredProfileFieldValues(string $field): array
    {
        $query = clone $this->query;

        return $query->select('fp.' . $field)
            ->distinct('fp.' . $field)
            ->leftJoin('profile AS fp', 'fp.user_id = user.id')
            ->andWhere(['IS NOT', 'fp.' . $field, new Expression('NULL')])
            ->limit(100)
            ->offset(null)
            ->orderBy('fp.' . $field)
            ->column();
    }
}
