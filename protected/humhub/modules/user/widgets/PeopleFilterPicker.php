<?php

namespace humhub\modules\user\widgets;

use humhub\modules\ui\form\widgets\BasePicker;
use humhub\modules\user\models\Profile;
use humhub\modules\user\models\User;
use Yii;
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
    }

    /**
     * @inheritdoc
     */
    protected function getSelectedOptions()
    {
        $get = Yii::$app->request->get('fields');
        if(isset($get[$this->itemKey])) {
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
        $result['no-result'] = Yii::t('UserModule.chooser', 'No users found for the given query.');
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
    protected function getItemImage($item) {
        return $item->user->getProfileImage();
    }

    public function getSuggestions($keyword)
    {
        return Profile::find()->select([
                $this->itemKey . ' AS id',
                $this->itemKey . ' AS text',
            ])
            ->groupBy($this->itemKey)
            ->where(['LIKE', $this->itemKey, $keyword])
            ->limit(100)
            ->asArray()
            ->all();
    }
}
