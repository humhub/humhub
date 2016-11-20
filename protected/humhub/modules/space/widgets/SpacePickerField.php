<?php

namespace humhub\modules\space\widgets;

use Yii;
use humhub\modules\user\widgets\BasePickerField;

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
     */
    public $defaultRoute = '/space/browse/search-json';

    /**
     * @inheritdoc 
     */
    public function init() {
        $this->itemClass = \humhub\modules\space\models\Space::className();
        $this->itemKey = 'guid';
    }

    /**
     * @inheritdoc 
     */
    protected function getTexts()
    {
        $result = parent::getTexts();
        $allowMultiple = $this->maxSelection !== 1;
        $result['data-placeholder'] = Yii::t('UserModule.widgets_SpacePickerField', 'Select {n,plural,=1{space} other{spaces}}', ['n' => ($allowMultiple) ? 2 : 1]);
        $result['data-placeholder-more'] = Yii::t('UserModule.widgets_SpacePickerField', 'Add Space');
        $result['data-no-result'] = Yii::t('UserModule.widgets_SpacePickerField', 'No spaces found for the given query.');

        if ($this->maxSelection) {
            $result['data-maximum-selected'] = Yii::t('UserModule.widgets_UserPickerField', 'This field only allows a maximum of {n,plural,=1{# space} other{# spaces}}.', ['n' => $this->maxSelection]);
        }
        return $result;
    }

    /**
     * @inheritdoc 
     */
    protected function getItemText($item)
    {
        return \yii\helpers\Html::encode($item->getDisplayName());
    }

    /**
     * @inheritdoc 
     */
    protected function getItemImage($item)
    {
        return Image::widget(["space" => $item, "width" => 24]);
    }

}
