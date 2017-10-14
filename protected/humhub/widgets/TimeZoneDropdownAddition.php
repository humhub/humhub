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

/**
 * This input addition will add a time zone input dropdown field, which is hidden by default and a time zone link displaying the current
 * time zone value. The time zone link will toggle the actual input field.
 *
 * @package humhub\widgets
 */
class TimeZoneDropdownAddition extends InputWidget
{
    public $toggleClass = 'input-field-addon-sm colorInfo pull-right';

    /**
     * @var array cached timeZone item array
     * @see TimezoneHelper::generateList()
     */
    private $timeZoneItems;

    /**
     * @inheritdoc
     */
    public $attribute = 'timeZone';


    /**
     * @inheritdoc
     */
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

    /**
     * @return null|array of timezone items, note we only include UTC+00:00 as possible selection if the current (or default) time zone is UTC
     */
    private function getCurrentLabel()
    {
        $value = $this->getTimeZoneValue();
        $timeZones =  $this->getTimeZoneItems();

        if(isset($timeZones[$value])) {
            return $timeZones[$value];
        }

        return array_values($timeZones)[0];
    }

    /**
     * @return string the current timeZone value either directly set as widget attribute or retrieved by model/attribute or default formatter timezone
     */
    private function getTimeZoneValue()
    {
        if(!$this->value && $this->hasModel()) {
            $attribute = $this->attribute;
            $this->value = $this->model->$attribute;
        } else if(empty($this->value)) {
            $this->value = Yii::$app->formatter->timeZone;
        }

        return $this->value;
    }

    /**
     * @return array of timezones with UTC offset, note that the result will be cached
     * @see TimezoneHelper::generateList()
     */
    public function getTimeZoneItems()
    {
        $value = $this->getTimeZoneValue();
        if(empty($this->timeZoneItems)) {
            $this->timeZoneItems = TimezoneHelper::generateList($value == 'UTC');
        }

        return $this->timeZoneItems;
    }
}