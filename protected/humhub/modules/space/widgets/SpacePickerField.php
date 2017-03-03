<?php

namespace humhub\modules\space\widgets;

use Yii;
use humhub\widgets\BasePickerField;
use yii\helpers\Html;

/**
 * Mutliselect input field for selecting space guids.
 *
 * @package humhub.modules_core.space.widgets
 * @since 1.2
 * @author buddha
 */
class SpacePickerField extends BasePickerField
{
    /**
     * @inheritdoc
     * Min guids string value of Space model equal 2
     */
    public $minInput = 2;

    /**
     * @inheritdoc
     */
    public $defaultRoute = '/space/browse/search-json';
    public $itemClass = \humhub\modules\space\models\Space::class;
    public $itemKey = 'guid';

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        $result = parent::getData();
        $allowMultiple = $this->maxSelection !== 1;
        $result['placeholder'] = Yii::t('SpaceModule.widgets_SpacePickerField', 'Select {n,plural,=1{space} other{spaces}}', ['n' => ($allowMultiple) ? 2 : 1]);
        $result['placeholder-more'] = Yii::t('SpaceModule.widgets_SpacePickerField', 'Add Space');
        $result['no-result'] = Yii::t('SpaceModule.widgets_SpacePickerField', 'No spaces found for the given query');

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('SpaceModule.widgets_SpacePickerField', 'This field only allows a maximum of {n,plural,=1{# space} other{# spaces}}', ['n' => $this->maxSelection]);
        }
        return $result;
    }

    /**
     * @inheritdoc
     */
    protected function getItemText($item)
    {
        return Html::encode($item->getDisplayName());
    }

    /**
     * @inheritdoc
     */
    protected function getItemImage($item)
    {
        return Image::widget(["space" => $item, "width" => 24]);
    }

}
