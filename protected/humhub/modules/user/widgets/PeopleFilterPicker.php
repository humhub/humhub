<?php

namespace humhub\modules\user\widgets;

use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\ProfileField;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;
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
    }

    /**
     * @inheritdoc
     */
    protected function getSelectedOptions()
    {
        $get = Yii::$app->request->get('fields');
        if (isset($get[$this->itemKey])) {
            $this->selection[] = $get[$this->itemKey];
        }

        if (!$this->selection) {
            $this->selection = [];
        }

        $result = [];
        foreach ($this->selection as $item) {
            if (!$item) {
                continue;
            }

            $result[$this->itemKey] = [
                'data-id' => $item,
                'data-text' => $item
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
            'size' => '1',
            'class' => 'form-control',
            'style' => 'width:100%',
            'title' => $this->placeholder
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        $result = parent::getData();
        $result['placeholder'] = '';
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
     * @param $keyword
     * @return Profile[]
     */
    public function getSuggestions($keyword = '')
    {
        return Profile::find()->select([
            'id' => $this->itemKey,
            'text' => $this->itemKey,
        ])
            ->groupBy($this->itemKey)
            ->where(['LIKE', $this->itemKey, $keyword])
            ->limit(100)
            ->asArray()
            ->all();
    }

    /**
     * @inheritdoc
     */
    protected function getDefaultResultData()
    {
        return $this->getSuggestions();
    }
}
