<?php

namespace humhub\modules\space\widgets;

use humhub\modules\content\widgets\ContentContainerPickerField;
use humhub\modules\space\models\Space;
use Yii;

/**
 * Mutliselect input field for selecting space guids.
 *
 * @package humhub.modules_core.space.widgets
 * @since 1.2
 * @author buddha
 */
class SpacePickerField extends ContentContainerPickerField
{
    public $itemClass = Space::class;
    /**
     * @inheritdoc
     */
    public $defaultRoute = '/space/browse/search-json';

    /**
     * @inheritdoc
     * Min guids string value
     */
    public $minInput = 2;

    /**
     * @inheritdoc
     */
    protected function getData()
    {
        $result = parent::getData();
        $allowMultiple = $this->maxSelection !== 1;
        $result['placeholder'] = Yii::t('SpaceModule.chooser', 'Select {n,plural,=1{space} other{spaces}}', ['n' => ($allowMultiple) ? 2 : 1]);
        $result['placeholder-more'] = Yii::t('SpaceModule.chooser', 'Add Space');
        $result['no-result'] = Yii::t('SpaceModule.chooser', 'No spaces found for the given query');

        if ($this->maxSelection) {
            $result['maximum-selected'] = Yii::t('SpaceModule.chooser', 'This field only allows a maximum of {n,plural,=1{# space} other{# spaces}}', ['n' => $this->maxSelection]);
        }

        return $result;
    }
}
