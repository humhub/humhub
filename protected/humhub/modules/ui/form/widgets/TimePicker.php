<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use Yii;

/**
 * TimePicker form field widget
 *
 * @inheritdoc
 * @package humhub\modules\ui\form\widgets
 */
class TimePicker extends \kartik\time\TimePicker
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!isset($this->pluginOptions['showMeridian'])) {
            $this->pluginOptions['showMeridian'] = Yii::$app->formatter->isShowMeridiem();
        }

        if (!isset($this->pluginOptions['defaultTime'])) {
            $this->pluginOptions['defaultTime'] = ($this->pluginOptions['showMeridian']) ? '10:00 AM' : '10:00';
        }
    }
}
