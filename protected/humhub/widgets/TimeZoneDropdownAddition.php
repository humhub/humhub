<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 14.07.2017
 * Time: 23:55
 */

namespace humhub\widgets;


use Yii;
use humhub\libs\TimezoneHelper;
use humhub\components\Widget;
use humhub\libs\Html;
use humhub\widgets\InputWidget;
use humhub\widgets\JsWidget;

class TimeZoneDropdownAddition extends InputWidget
{
    public $toggleClass = 'input-field-addon-sm colorInfo pull-right';

    private $timeZoneItems;

    /*
     * @inheritdoc
     */
    public $attribute = 'timeZone';


    public function run()
    {
        return $this->render('selectTimeZoneDropdown', [
            'id' => $this->id,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'toggleClass' => $this->toggleClass,
            'name' => $this->name,
            'currentTimeZoneLabel' => $this->getCurrentLabel(),
            'value' => $this->value,
            'timeZoneItems' => $this->getTimeZoneItems()]);
    }

    private function getCurrentLabel()
    {
        if($this->model) {
            $attribute = $this->attribute;
            $this->value = $this->model->$attribute;
        }

        if(isset($this->getTimeZoneItems()[$this->value])) {
            return $this->getTimeZoneItems()[$this->value];
        }

        return $this->getTimeZoneItems()[Yii::$app->formatter->timeZone];
    }


    public function getTimeZoneItems()
    {
        if(empty($this->timeZoneItems)) {
            $this->timeZoneItems = TimezoneHelper::generateList();
        }

        return $this->timeZoneItems;
    }

}