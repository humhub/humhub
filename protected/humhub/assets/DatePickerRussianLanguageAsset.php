<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * Fixes https://github.com/humhub/humhub/issues/4638 by aligning jui and icu month short names
 *
 * @author buddha
 * @since 1.7.1
 */
class DatePickerRussianLanguageAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $basePath = '@webroot-static';

    /**
     * @inheritdoc
     */
    public $baseUrl = '@web-static';

    /**
     * @inheritdoc
     */
    public $js = [
        'js/compat/date/i18n/datepicker-ru.js',
    ];

    public $depends = [
        'yii\jui\JuiAsset',
    ];

}
