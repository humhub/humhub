<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\assets;

use humhub\components\assets\AssetBundle;

/**
 * jquery-ui-widget
 *
 * @author luke
 */
class JqueryWidgetAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@npm/jquery-ui';

    /**
     * @inheritdoc
     *
     * `ui/i18n/*` is included because `yii\jui\DatePickerLanguageAsset` publishes
     * the same source path; bundles sharing a source path must publish with
     * identical options as the published copy is keyed by source path only.
     */
    public $publishOptions = [
        'only' => [
            'ui/widget.js',
            'ui/i18n/*',
        ],
    ];

    /**
     * @inheritdoc
     */
    public $js = ['ui/widget.js'];

}
