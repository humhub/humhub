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
 * Date: 20.07.2017
 * Time: 18:16
 */

namespace humhub\widgets;


use kartik\base\TranslationTrait;
use kartik\base\WidgetTrait;
use Yii;

class TimePicker extends \kartik\time\TimePicker
{
    public function init()
    {
        parent::init();

        if(!isset($this->pluginOptions['showMeridian'])) {
            $this->pluginOptions['showMeridian'] = Yii::$app->formatter->isShowMeridiem();
        }

        if(!isset($this->pluginOptions['defaultTime'])) {
            $this->pluginOptions['defaultTime'] = ($this->pluginOptions['showMeridian']) ? '10:00 AM' : '10:00';
        }
    }
}