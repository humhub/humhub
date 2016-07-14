<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\i18n;

use humhub\models\Setting;

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

        if (Setting::Get('defaultDateInputFormat', 'admin') != '') {
            $this->dateInputFormat = Setting::Get('defaultDateInputFormat', 'admin');
        }
    }

}
