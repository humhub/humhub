<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use IntlDateFormatter;
use Yii;

/**
 * @inheritdoc
 */
class Formatter extends \yii\i18n\Formatter
{

    /**
     * @inheritdoc
     */
    public $sizeFormatBase = 1000;

    /**
     * @var string the default format string to be used to format a input field [[asDate()|date]].
     * This mostly used in forms (DatePicker).
     * @see dateFormat
     */
    public $dateInputFormat = 'short';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Yii::$app->params['installed'] && Yii::$app->getModule('admin')->settings->get('defaultDateInputFormat') != '') {
            $this->dateInputFormat = Yii::$app->getModule('admin')->settings->get('defaultDateInputFormat');
        }
    }

    /**
     * Returns the date pattern for the given $locale and $dateType, $timeType.
     *
     * @since 1.2.2
     * @param int $dateType
     * @param int $timeType
     * @param null $locale
     * @return null|string
     */
    public function getDateTimePattern($dateType = IntlDateFormatter::SHORT, $timeType = IntlDateFormatter::SHORT, $locale = null) {
        if(extension_loaded('intl')) {
            $locale = empty($locale) ? $this->locale : $locale;
            $formatter = new IntlDateFormatter($locale, $dateType, $timeType, $this->timeZone, $this->calendar);
            return $formatter->getPattern();
        } else {
            return null;
        }
    }

    /**
     * Checks if the time pattern of a given $locale contains a meridiem (AM/PM).
     * If no $locale is provided the Formatter locale will be used.
     *
     * @since 1.2.2
     * @param null $locale
     * @return bool if the locale uses a 12 hour (AM/PM) otherwise a 24 hour time format is used.
     */
    public function isShowMeridiem($locale = null) {
        if(extension_loaded('intl')) {
            $pattern = $this->getDateTimePattern(IntlDateFormatter::NONE, IntlDateFormatter::SHORT);
            return strpos($pattern, 'a') !== false;
        }
        return false;
    }

}
